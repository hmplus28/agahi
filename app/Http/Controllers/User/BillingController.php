<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseTariffRequest;
use App\Models\Ad;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        // The user-facing payments page only shows SUCCESSFUL payments.
        // Pending and failed payments are admin-only to keep the user's view
        // clean and focused on the actions that actually completed.
        $payments = Payment::query()
            ->where('user_id', $userId)
            ->where('status', 'successful')
            ->with(['invoice.items', 'ad'])
            ->latest('paid_at')
            ->paginate(20);

        return view('user.payments.index', [
            'tariffs'  => Tariff::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'ads'      => Ad::query()->where('user_id', $userId)->whereNotIn('status', ['deleted'])->get(['id', 'title', 'code', 'status']),
            'invoices' => Invoice::query()->where('user_id', $userId)->latest()->paginate(20),
            'payments' => $payments,
        ]);
    }

    public function purchase(PurchaseTariffRequest $request, PaymentService $service): RedirectResponse
    {
        $invoice = $service->createInvoice(
            $request->user(),
            Ad::query()->findOrFail($request->integer('ad_id')),
            Tariff::query()->findOrFail($request->integer('tariff_id')),
        );
        $result = $service->begin($invoice, $request->user());
        return redirect()->to($result['redirect_url']);
    }

    /**
     * Sadad auto-POSTs the user's browser to this URL after a payment
     * completes (or fails) WITHOUT a CSRF token. We exempt it from CSRF
     * protection in bootstrap/app.php and rely on the HMAC signature
     * on the ?signature= query parameter instead.
     */
    public function callback(Request $request, string $authority, PaymentService $service): RedirectResponse
    {
        $payment = $service->verify($authority, $request->user(), $request->post());

        return redirect()->route('user.payments.index')->with(
            'success',
            $payment->status === 'successful'
                ? 'پرداخت و فعال‌سازی سرویس با موفقیت انجام شد.'
                : 'تأیید پرداخت ناموفق بود.',
        );
    }
}
