<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdServiceActivator;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Tariff;
use App\Support\PersianNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GuestAdController extends Controller
{
    public function create(): View
    {
        return view('guest.ad-create', [
            'categories' => Category::query()->active()->orderBy('sort_order')->get(['id', 'parent_id', 'title', 'slug', 'sort_order']),
            'countries'  => Country::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']),
            'provinces'  => Province::query()->where('is_active', true)->orderBy('name')->get(['id', 'country_id', 'name']),
            'cities'     => City::query()->where('is_active', true)->orderBy('name')->get(['id', 'province_id', 'name']),
            'tariffs'    => Tariff::query()->where('is_active', true)->orderBy('price')->get(['id', 'code', 'title', 'description', 'price', 'service_type', 'duration_days']),
        ]);
    }

    public function store(Request $request, AdSubmissionService $service, AdServiceActivator $activator): RedirectResponse
    {


        if ($request->filled('mobile_1')) {
            $request->merge(['mobile_1' => PersianNormalizer::mobile((string) $request->input('mobile_1'))]);
        }
        if ($request->filled('mobile_2')) {
            $request->merge(['mobile_2' => PersianNormalizer::mobile((string) $request->input('mobile_2'))]);
        }

        // Drop untouched link slots so an empty row never counts toward the
        // per-link surcharge, then keep well-formed rows only.
        $linkRows = [];
        foreach ((array) $request->input('links', []) as $link) {
            if (! is_array($link)) continue;
            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '') continue;
            $linkRows[] = ['type' => (string) ($link['type'] ?? 'site'), 'url' => $url];
        }
        $request->merge(['links' => $linkRows]);

        $data = $this->validateAd($request);



        $data['title']       = PersianNormalizer::text($data['title']);
        $data['description'] = PersianNormalizer::text($data['description']);
        if (!empty($data['business_name'])) {
            $data['business_name'] = PersianNormalizer::text($data['business_name']);
        }
        if (!empty($data['full_name'])) {
            $data['full_name'] = PersianNormalizer::text($data['full_name']);
        }

        $uploads = $request->file('images', []);

        // Selected paid/free options (tariffs). Free ones apply instantly;
        // paid ones are parked as "pending" rows so the moderation team can
        // activate them from the admin panel after payment coordination.
        $selectedTariffs = Tariff::query()
            ->whereIn('id', (array) $request->input('services', []))
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        // Persisted separately (Ad::create ignores non-fillable keys, but keep
        // the payload explicit).
        $linkRows = array_values((array) ($data['links'] ?? []));
        unset($data['links']);

        try {
            DB::transaction(function () use ($data, $uploads, $request, $service, $activator, $selectedTariffs, $linkRows): void {
                $fingerprints = app(\App\Domains\Ads\Services\DuplicateDetector::class)
                    ->fingerprints($data['title'], $data['description']);

                $ad = Ad::query()->create([
                    ...$data,
                    'slug'                       => Str::slug($data['title'], '-', 'fa') ?: Str::random(8),
                    'normalized_title'           => $fingerprints['title'],
                    'normalized_description'     => $fingerprints['description'],
                    'normalized_title_hash'      => $fingerprints['title_hash'],
                    'normalized_description_hash'=> $fingerprints['description_hash'],
                    'status'                     => AdStatus::PendingApproval,
                    'source'                     => 'guest',
                    'submit_ip'                  => $request->ip(),
                    'sort_at'                    => now(),
                ]);

                foreach ($linkRows as $index => $link) {
                    $ad->allLinks()->create([
                        'type'       => (string) ($link['type'] ?? 'site'),
                        'url'        => (string) $link['url'],
                        'sort_order' => $index,
                        'is_active'  => true,
                    ]);
                }

                foreach ($uploads as $index => $upload) {
                    app(\App\Domains\Ads\Services\AdImageProcessor::class)->store($ad, $upload, $index);
                }

                foreach ($selectedTariffs as $tariff) {
                    $activator->attach(
                        $ad,
                        $tariff,
                        $tariff->price > 0 ? 'pending' : 'active',
                        ['context' => 'guest_submission'],
                    );
                }
            });
        } catch (ValidationException $e) {
            throw $e;
        }

        $hasPaidSelection = $selectedTariffs->contains(fn (Tariff $tariff) => $tariff->price > 0);
        return redirect()->route('home')->with('success', 'آگهی شما با موفقیت ثبت شد و پس از تأیید مدیریت منتشر خواهد شد.'
            . ($hasPaidSelection ? ' خدمات پولی انتخاب‌شده پس از هماهنگی پرداخت و تأیید مدیریت فعال می‌شود.' : ''));
    }

    public function validateAd(Request $request): array
    {
        return $request->validate([
            'title'         => ['required', 'string', 'max:300'],
            'description'   => ['required', 'string', 'max:6000'],
            'category_id'   => ['required', 'exists:categories,id'],
            'country_id'    => ['nullable', 'exists:countries,id'],
            'province_id'   => ['nullable', 'exists:provinces,id'],
            'city_id'       => ['required', 'exists:cities,id'],
            'price'         => ['nullable', 'integer', 'min:0'],
            'full_name'     => ['nullable', 'string', 'max:160'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'mobile_1'      => ['required', 'regex:/^09\d{9}$/'],
            'mobile_2'      => ['nullable', 'regex:/^09\d{9}$/'],
            'phone_1'       => ['nullable', 'string', 'max:30'],
            'phone_2'       => ['nullable', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'address'       => ['nullable', 'string', 'max:500'],
            'images'        => ['nullable', 'array', 'max:5'],
            'images.*'      => ['file', 'image', 'max:5120'],
            'links'         => ['nullable', 'array', 'max:5'],
            'links.*.type'  => ['required_with:links.*.url', 'in:site,aparat,eitaa,bale,rubika,whatsapp,telegram,instagram'],
            'links.*.url'   => ['required', 'string', 'max:500', 'url'],
            'services'      => ['nullable', 'array', 'max:11'],
            'services.*'    => ['integer', 'exists:tariffs,id'],
        ], [
            'city_id.required'    => 'انتخاب شهر الزامی است.',
            'category_id.required'=> 'انتخاب دسته‌بندی الزامی است.',
            'images.max'          => 'حداکثر پنج تصویر قابل بارگذاری است.',
        ]);
    }
}
