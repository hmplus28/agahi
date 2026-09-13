<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin-configurable pricing & site settings.
 *
 * Stores the per-link surcharge, per-extra-image surcharge, and similar
 * admin-tunable values in the site_settings table so they can be changed
 * without a code deploy.
 */
class SettingsController extends Controller
{
    /** Keys we accept from the form, mapped to validation rules. */
    private const RULES = [
        'price_per_link'         => ['required', 'integer', 'min:0', 'max:1000000'],
        'price_per_extra_image'  => ['required', 'integer', 'min:0', 'max:1000000'],
        'apply_pricing_to_display' => ['nullable', 'boolean'],
        'support_phone'          => ['required', 'string', 'max:30'],
        'support_phone_href'     => ['required', 'string', 'max:30'],
        'brand_name'             => ['required', 'string', 'max:80'],
        'site_description'       => ['required', 'string', 'max:300'],
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'values' => [
                'price_per_link'         => SiteSetting::get('pricing.price_per_link', config('agahi.price_per_link', 10)),
                'price_per_extra_image'  => SiteSetting::get('pricing.price_per_extra_image', config('agahi.price_per_extra_image', 20)),
                'apply_pricing_to_display' => (bool) SiteSetting::get('pricing.apply_pricing_to_display', config('agahi.apply_pricing_to_display', true)),
                'support_phone'          => SiteSetting::get('branding.support_phone', config('agahi.support_phone', '021-00000000')),
                'support_phone_href'     => SiteSetting::get('branding.support_phone_href', config('agahi.support_phone_href', '02100000000')),
                'brand_name'             => SiteSetting::get('branding.brand_name', config('agahi.brand_name', 'آگهی')),
                'site_description'       => SiteSetting::get('branding.site_description', config('agahi.site_description', 'سامانهٔ سریع و امن ثبت آگهی در ایران')),
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

        return back()->with('success', 'تنظیمات ذخیره شد.');
    }
}
