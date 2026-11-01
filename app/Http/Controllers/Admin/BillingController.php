<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'successful')->toString();

        $query = Payment::query()->with(['user', 'invoice', 'ad']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.billing.index', [
            'payments'        => $query->latest()->paginate(50),
            'current_status'  => $status,
            'status_options'  => [
                'needs_review' => 'نیازمند بررسی',
                'successful'   => 'موفق',
                'failed'       => 'ناموفق',
                'canceled'     => 'لغو شده',
                'pending'      => 'در انتظار',
                'all'          => 'همه',
            ],
        ]);
    }

    /**
     * PRD §27 - manual confirmation (card-to-card). Activates the purchased
     * services and stores the admin note.
     */
    public function confirm(Request $request, Payment $payment, PaymentService $service): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1500']]);

        try {
            $service->confirmManually($payment, $data['admin_note'] ?? null);
        } catch (\LogicException $e) {
            return back()->withErrors(['admin_note' => $e->getMessage()]);
        }

        return back()->with('success', 'پرداخت تأیید و سرویس فعال شد.');
    }

    /** PRD §27 - manual rejection (status: لغو شده) + admin note. */
    public function reject(Request $request, Payment $payment, PaymentService $service): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1500']]);

        try {
            $service->rejectManually($payment, $data['admin_note'] ?? null);
        } catch (\LogicException $e) {
            return back()->withErrors(['admin_note' => $e->getMessage()]);
        }

        return back()->with('success', 'پرداخت لغو شد.');
    }
}
