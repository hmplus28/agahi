<?php

declare(strict_types=1);
namespace App\Http\Controllers\User;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Domains\Ads\Services\AdWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdRequest;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class AdController extends Controller { private function formData(): array { return ['categories'=>Category::query()->active()->orderBy('sort_order')->get(['id','title']),'cities'=>City::query()->where('is_active',true)->orderBy('name')->get(['id','name'])]; } public function create(): View { return view('user.ads.create',$this->formData()); } public function store(StoreAdRequest $request,AdSubmissionService $service): RedirectResponse { $ad=$service->create($request->user(),$request->safe()->except('images'),$request->file('images',[]),$request->ip()); return redirect()->route('user.dashboard')->with('success','آگهی با کد '.$ad->code.' برای تأیید ثبت شد.'); } public function edit(Ad $ad): View { $this->authorize('update',$ad); return view('user.ads.edit',[...$this->formData(),'ad'=>$ad->load('images')]); } public function update(StoreAdRequest $request,Ad $ad,AdSubmissionService $service): RedirectResponse { $this->authorize('update',$ad); $service->update($ad,$request->user(),$request->safe()->except('images'),$request->file('images',[])); return redirect()->route('user.dashboard')->with('success','ویرایش آگهی ثبت شد؛ در صورت فعال بودن دوباره بررسی می‌شود.'); } public function destroy(Ad $ad,AdWorkflow $workflow): RedirectResponse { $this->authorize('view',$ad); $workflow->transition($ad,AdStatus::Deleted,auth()->user(),'حذف توسط کاربر'); return redirect()->route('user.dashboard')->with('success','آگهی حذف شد.'); } }
