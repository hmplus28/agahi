<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        // Default to showing only successful payments (per product spec).
        // Admin can switch the filter via the URL (?status=all|successful|failed|pending).
        $status = $request->string('status', 'successful')->toString();

        $query = Payment::query()->with(['user', 'invoice', 'ad']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.billing.index', [
            'payments'        => $query->latest()->paginate(50),
            'current_status'  => $status,
            'status_options'  => [
                'successful' => 'موفق',
                'failed'     => 'ناموفق',
                'pending'    => 'در انتظار',
                'all'        => 'همه',
            ],
        ]);
    }
}
