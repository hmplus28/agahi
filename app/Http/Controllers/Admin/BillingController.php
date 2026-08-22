<?php

declare(strict_types=1);
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\View\View;
class BillingController extends Controller { public function index(): View { return view('admin.billing.index',['payments'=>Payment::query()->with(['user','invoice','ad'])->latest()->paginate(50)]); } }
