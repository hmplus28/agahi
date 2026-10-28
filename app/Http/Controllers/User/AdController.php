<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdServiceActivator;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Domains\Ads\Services\AdWorkflow;
use App\Domains\Billing\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class AdController extends Controller {
    private function formData(): array { return ['categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','parent_id','title','slug','sort_order']),'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]; }
    private function tariffs() { return Tariff::query()->where('is_active',true)->orderBy('price')->orderBy('sort_order')->get(['id','code','title','description','price','service_type','duration_days']); }

    public function create(): View { return view('user.ads.create',[...$this->formData(),'tariffs'=>$this->tariffs()]); }

    public function store(StoreAdRequest $request, AdSubmissionService $service, AdServiceActivator $activator, PaymentService $payments): RedirectResponse
    {
        $ad = $service->create($request->user(), $request->safe()->except(['images','services']), $request->file('images', []), $request->ip());

        // Selected options: free tariffs apply instantly, paid tariffs are
        // collected into ONE invoice and go through the payment gateway;
        // PaymentService settles them (AdService rows + ad flags).
        $selected = Tariff::query()
            ->whereIn('id', (array) $request->input('services', []))
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        foreach ($selected->where('price', 0) as $freeTariff) {
            $activator->attach($ad, $freeTariff, 'active', ['context' => 'user_submission']);
        }

        $paidTariffs = $selected->filter(fn (Tariff $tariff) => $tariff->price > 0);
        if ($paidTariffs->isNotEmpty()) {
            $invoice = $payments->createInvoiceForAd($request->user(), $ad, $paidTariffs);
            $result = $payments->begin($invoice, $request->user());

            return redirect()->to($result['redirect_url']);
        }

        return redirect()->route('user.dashboard')->with('success', 'آگهی با کد '.$ad->code.' برای تأیید ثبت شد.');
    }

    public function edit(Ad $ad): View { $this->authorize('update',$ad); return view('user.ads.edit',[...$this->formData(),'tariffs'=>$this->tariffs(),'ad'=>$ad->load('images')]); }
    public function update(StoreAdRequest $request,Ad $ad,AdSubmissionService $service): RedirectResponse { $this->authorize('update',$ad); $service->update($ad,$request->user(),$request->safe()->except(['images','services']),$request->file('images',[])); return redirect()->route('user.dashboard')->with('success','ویرایش آگهی ثبت شد؛ در صورت فعال بودن دوباره بررسی می‌شود.'); }
    public function destroy(Ad $ad,AdWorkflow $workflow): RedirectResponse { $this->authorize('view',$ad); $workflow->transition($ad,AdStatus::Deleted,auth()->user(),'حذف توسط کاربر'); return redirect()->route('user.dashboard')->with('success','آگهی حذف شد.'); }
}
