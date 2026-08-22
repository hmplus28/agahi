<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseTariffRequest;
use App\Models\Ad;
use App\Models\Invoice;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class BillingController extends Controller { public function index(): View { return view('user.payments.index',['tariffs'=>Tariff::query()->where('is_active',true)->orderBy('sort_order')->get(),'ads'=>Ad::query()->where('user_id',auth()->id())->whereNotIn('status',['deleted'])->get(['id','title','code','status']),'invoices'=>Invoice::query()->where('user_id',auth()->id())->latest()->paginate(20)]); } public function purchase(PurchaseTariffRequest $request,PaymentService $service): RedirectResponse { $invoice=$service->createInvoice($request->user(),Ad::query()->findOrFail($request->integer('ad_id')),Tariff::query()->findOrFail($request->integer('tariff_id'))); $result=$service->begin($invoice,$request->user()); return redirect()->to($result['redirect_url']); } public function callback(string $authority,PaymentService $service): RedirectResponse { $payment=$service->verify($authority,request()->user()); return redirect()->route('user.payments.index')->with('success',$payment->status==='successful'?'پرداخت و فعال‌سازی سرویس با موفقیت انجام شد.':'تأیید پرداخت ناموفق بود.'); } }
