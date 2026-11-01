<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{

    private const RULES = [
        'price_per_link'         => ['required', 'integer', 'min:0', 'max:1000000'],
        'price_per_extra_image'  => ['required', 'integer', 'min:0', 'max:1000000'],
        'apply_pricing_to_display' => ['nullable', 'boolean'],
        'support_phone'          => ['required', 'string', 'max:30'],
        'support_phone_href'     => ['required', 'string', 'max:30'],
        'brand_name'             => ['required', 'string', 'max:80'],
        'site_description'       => ['required', 'string', 'max:300'],
        'billing_card_number'      => ['nullable', 'string', 'max:34'],
        'billing_card_number_holder' => ['nullable', 'string', 'max:120'],
        'job_category_slugs'     => ['nullable', 'string', 'max:400'],
        'landing_whitelist'      => ['nullable', 'string', 'max:2000'],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'values' => [
                'price_per_link'         => SiteSetting::get('pricing.price_per_link', config('agahi.price_per_link', 10)),
                'price_per_extra_image'  => SiteSetting::get('pricing.price_per_extra_image', config('agahi.price_per_extra_image', 20)),
                'apply_pricing_to_display' => (bool) SiteSetting::get('pricing.apply_pricing_to_display', config('agahi.apply_pricing_to_display', true)),
                'support_phone'          => SiteSetting::get('branding.support_phone', config('agahi.support_phone')),
                'support_phone_href'     => SiteSetting::get('branding.support_phone_href', config('agahi.support_phone_href')),
                'brand_name'             => SiteSetting::get('branding.brand_name', config('agahi.brand_name', 'آگهی')),
                'site_description'       => SiteSetting::get('branding.site_description', config('agahi.site_description', 'سامانهٔ سریع و امن ثبت آگهی در ایران')),
                'billing_card_number'        => SiteSetting::get('billing.card_number', ''),
                'billing_card_number_holder' => SiteSetting::get('billing.card_number_holder', ''),
                'job_category_slugs'     => SiteSetting::get('seo.job_category_slugs', ''),
                'landing_whitelist'      => SiteSetting::get('seo.landing_whitelist', ''),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(self::RULES);

        SiteSetting::put('pricing.price_per_link', (int) $data['price_per_link']);
        SiteSetting::put('pricing.price_per_extra_image', (int) $data['price_per_extra_image']);
        SiteSetting::put('pricing.apply_pricing_to_display', $request->boolean('apply_pricing_to_display'));
        SiteSetting::put('branding.support_phone', $data['support_phone']);
        SiteSetting::put('branding.support_phone_href', $data['support_phone_href']);
        SiteSetting::put('branding.brand_name', $data['brand_name']);
        SiteSetting::put('branding.site_description', $data['site_description']);

        // کارت‌به‌کارت (PRD §27): ارقام فارسی به لاتین نرمالایز می‌شود.
        $cardNumber = trim(strtr((string) ($data['billing_card_number'] ?? ''), ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']));
        SiteSetting::put('billing.card_number', $cardNumber);
        SiteSetting::put('billing.card_number_holder', trim((string) ($data['billing_card_number_holder'] ?? '')));

        // سئو (PRD §51/§42): دسته‌های استخدام + سفیدلیست صفحات فرود شهر×دسته.
        $whitelistChanged = SiteSetting::get('seo.landing_whitelist', '') !== (string) ($data['landing_whitelist'] ?? '');
        SiteSetting::put('seo.job_category_slugs', trim((string) ($data['job_category_slugs'] ?? '')));
        SiteSetting::put('seo.landing_whitelist', trim((string) ($data['landing_whitelist'] ?? '')));
        if ($whitelistChanged) {
            \Illuminate\Support\Facades\Cache::forget('seo.sitemap.locations.v1');
        }

        return back()->with('success', 'تنظیمات ذخیره شد.');
    }
}
