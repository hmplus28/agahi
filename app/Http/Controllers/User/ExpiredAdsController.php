<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpiredAdsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $ads = Ad::query()
            ->where('user_id', $user->id)
            ->where('status', AdStatus::Expired)
            ->with(['city', 'category', 'images'])
            ->latest('expires_at')
            ->get();


        $tariff = Tariff::query()
            ->where('is_active', true)
            ->where('service_type', 'renewal')
            ->orderBy('price')
            ->first();

        $total = $ads->count() * ($tariff?->price ?? 0);

        return view('user.expired-ads.index', [
            'ads'    => $ads,
            'tariff' => $tariff,
            'total'  => $total,
        ]);
    }



    public function renewAll(Request $request, PaymentService $service): RedirectResponse
    {
        $data = $request->validate([
            'ad_ids'   => ['required', 'array', 'min:1'],
            'ad_ids.*' => ['integer', 'exists:ads,id'],
            'tariff_id' => ['required', 'exists:tariffs,id'],
        ]);

        $user = $request->user();
        $tariff = Tariff::query()->findOrFail($data['tariff_id']);


        $ads = Ad::query()
            ->where('user_id', $user->id)
            ->where('status', AdStatus::Expired)
            ->whereIn('id', $data['ad_ids'])
            ->get();

        if ($ads->isEmpty()) {
            return back()->withErrors(['ad_ids' => 'هیچ آگهی منقضی شده‌ای برای تمدید انتخاب نشده است.']);
        }





        $primaryAd = $ads->first();
        $invoice = \App\Models\Invoice::query()->create([
            'invoice_number' => 'INV-RENEW-' . now()->format('Ymd') . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6)),
            'user_id'        => $user->id,
            'ad_id'          => $primaryAd->id,
            'status'         => 'pending',
            'subtotal'       => $tariff->price * $ads->count(),
            'discount'       => 0,
            'total'          => $tariff->price * $ads->count(),
        ]);

        foreach ($ads as $ad) {
            \App\Models\InvoiceItem::query()->create([
                'invoice_id'  => $invoice->id,
                'title'        => 'تمدید آگهی: ' . $ad->title,
                'quantity'     => 1,
                'unit_price'   => $tariff->price,
                'total_price'  => $tariff->price,
                'metadata'     => ['tariff_id' => $tariff->id, 'service_type' => 'renewal', 'ad_id' => $ad->id],
            ]);
        }

        $result = $service->begin($invoice->fresh(['items']), $user);
        return redirect()->to($result['redirect_url']);
    }
}
