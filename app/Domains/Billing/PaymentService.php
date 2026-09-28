<?php

declare(strict_types=1);

namespace App\Domains\Billing;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Ad;
use App\Models\AdService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

final class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly AdWorkflow $workflow,
    ) {}

    public function createInvoice(User $user, Ad $ad, Tariff $tariff): Invoice
    {
        if ($ad->user_id !== $user->id) {
            abort(403, 'این آگهی متعلق به شما نیست؛ امکان صدور فاکتور برای آگهی دیگران وجود ندارد.');
        }
        if (!$tariff->is_active) throw new LogicException('تعرفهٔ انتخابی فعال نیست.');

        return DB::transaction(function () use ($user, $ad, $tariff): Invoice {
            $invoice = Invoice::query()->create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id'        => $user->id,
                'ad_id'          => $ad->id,
                'status'         => 'pending',
                'subtotal'       => $tariff->price,
                'discount'       => 0,
                'total'          => $tariff->price,
            ]);
            InvoiceItem::query()->create([
                'invoice_id'  => $invoice->id,
                'title'       => $tariff->title,
                'quantity'    => 1,
                'unit_price'  => $tariff->price,
                'total_price' => $tariff->price,
                'metadata'    => ['tariff_id' => $tariff->id, 'service_type' => $tariff->service_type],
            ]);
            return $invoice->load('items');
        });
    }


    public function begin(Invoice $invoice, User $user): array
    {
        return DB::transaction(function () use ($invoice, $user): array {
            $locked = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
            if ($locked->user_id !== $user->id || $locked->status === 'paid') {
                throw new LogicException('فاکتور قابل پرداخت نیست.');
            }
            if ($locked->total === 0) {
                $payment = Payment::query()->create([
                    'user_id'    => $user->id,
                    'ad_id'      => $locked->ad_id,
                    'invoice_id' => $locked->id,
                    'amount'     => 0,
                    'method'     => 'free',
                    'status'     => 'pending',
                    'gateway'    => 'free',
                    'authority'  => 'FREE-'.Str::upper(Str::random(20)),
                ]);
                return [
                    'payment'      => $this->settle($payment, 'FREE-'.Str::upper(Str::random(10))),
                    'redirect_url' => route('user.payments.index'),
                ];
            }

            $gateway = config('payment.gateway', 'fake');
            $payment = Payment::query()->create([
                'user_id'    => $user->id,
                'ad_id'      => $locked->ad_id,
                'invoice_id' => $locked->id,
                'amount'     => $locked->total,
                'method'     => 'online',
                'status'     => 'pending',
                'gateway'    => $gateway,
            ]);
            $intent = $this->gateway->create($payment);
            $payment->update(['authority' => $intent['authority']]);





            $redirectUrl = $intent['redirect_url']
                ?? route('user.payments.callback', [
                    'authority' => $intent['authority'],
                    'signature' => $this->computeSignature($intent['authority']),
                ]);

            return [
                'payment'      => $payment->refresh(),
                'redirect_url' => $redirectUrl,
            ];
        });
    }



    public function verify(string $authority, mixed $second, mixed $third = null): Payment
    {

        if ($second instanceof User) {
            $user = $second;
            $callback = is_array($third) ? $third : null;
            $explicitSignature = null;
        } else {

            $explicitSignature = is_string($second) ? $second : null;
            $user = $third instanceof User ? $third : null;
            $callback = null;
            if (!$user) abort(403);
        }





        $signature = $explicitSignature ?? request()?->query('signature');
        if (!$signature || !hash_equals($this->computeSignature($authority), (string) $signature)) {
            abort(403);
        }

        return DB::transaction(function () use ($authority, $user, $callback): Payment {
            $payment = Payment::query()->where('authority', $authority)->lockForUpdate()->firstOrFail();

            if ($payment->user_id !== $user->id) {
                abort(403, 'این پرداخت متعلق به شما نیست؛ امکان تأیید آن وجود ندارد.');
            }
            if ($payment->status === 'successful') return $payment;

            $callback ??= request()?->post() ?: [];
            $result = $this->gateway->verify($payment, (array) $callback);

            if (!$result['successful']) {
                $payment->update(['status' => 'failed']);
                return $payment->refresh();
            }

            return $this->settle($payment, $result['reference_id'] ?? null);
        });
    }



    public function computeSignature(string $authority): string
    {
        $key = config('app.key');
        return hash_hmac('sha256', $authority, $key);
    }



    public function validateCallbackSignature(string $authority, string $signature): bool
    {
        if ($signature === '') return false;
        return hash_equals($this->computeSignature($authority), $signature);
    }



    public function signCallbackUrl(string $template, string $authority): string
    {
        $signature = $this->computeSignature($authority);

        return str_replace(
            ['__AUTHORITY__', '__SIGNATURE__'],
            [urlencode($authority), $signature],
            $template,
        );
    }

    private function settle(Payment $payment, ?string $reference): Payment
    {
        if ($payment->status === 'successful') return $payment;

        $payment->update([
            'status'       => 'successful',
            'reference_id' => $reference,
            'paid_at'      => now(),
            'verified_at'  => now(),
        ]);

        $invoice = $payment->invoice()->lockForUpdate()->firstOrFail();
        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        $ad = Ad::query()->lockForUpdate()->findOrFail($invoice->ad_id);

        foreach ($invoice->items as $item) {
            $tariffId = $item->metadata['tariff_id'] ?? null;
            $tariff = $tariffId ? Tariff::query()->find($tariffId) : null;
            if (!$tariff) continue;

            $start = now();
            $expires = $tariff->duration_days ? now()->addDays($tariff->duration_days) : null;

            AdService::query()->create([
                'ad_id'      => $ad->id,
                'tariff_id'  => $tariff->id,
                'starts_at'  => $start,
                'expires_at' => $expires,
                'status'     => 'active',
                'metadata'   => ['invoice_id' => $invoice->id],
            ]);

            match ($tariff->service_type) {
                'renewal', 'ad' => $this->activateOrExtend($ad, $expires),
                'featured'      => $ad->update(['is_featured' => true]),
                'urgent'        => $ad->update(['is_urgent' => true]),
                'colored'       => $ad->update(['is_colored' => true]),
                'ladder'        => $ad->update(['last_ladder_at' => now(), 'sort_at' => now()]),
                default         => null,
            };
        }




        if ($ad->status === AdStatus::PendingPayment) {
            $this->workflow->transition($ad, AdStatus::PendingApproval, null, 'تسویه پرداخت بعداً');
        }

        return $payment->refresh();
    }

    private function activateOrExtend(Ad $ad, ?\DateTimeInterface $expires): void
    {
        if ($expires) $ad->update(['expires_at' => $expires]);
        if ($ad->status === AdStatus::Expired) {
            $this->workflow->transition($ad, AdStatus::Active, null, 'تمدید پس از پرداخت');
        }
    }
}
