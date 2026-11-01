<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /** PRD §28 - full invoice page (seller, buyer, items, total, payment ref). */
    public function show(Invoice $invoice): View
    {
        abort_unless($invoice->user_id === auth()->user()->id, 404);

        $invoice->load(['items', 'payments', 'ad']);

        return view('user.invoices.show', [
            'invoice'      => $invoice,
            'sellerName'   => (string) \App\Models\SiteSetting::get('branding.brand_name', config('agahi.brand_name', 'سامانه آگهی')),
            'sellerPhone'  => (string) \App\Models\SiteSetting::get('branding.support_phone', config('agahi.support_phone', '')),
            'cardNumber'   => (string) \App\Models\SiteSetting::get('billing.card_number', ''),
            'cardHolder'   => (string) \App\Models\SiteSetting::get('billing.card_number_holder', ''),
        ]);
    }
}
