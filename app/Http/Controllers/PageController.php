<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('public.pages.about');
    }

    public function contact(): View
    {
        return view('public.pages.contact');
    }

    public function terms(): View
    {
        return view('public.pages.terms');
    }

    public function siteAds(): View
    {
        return view('public.pages.site-ads');
    }

    public function siteAdsLicenses(): View
    {
        return view('public.pages.site-ads-licenses');
    }

    public function siteAdsRules(): View
    {
        return view('public.pages.site-ads-rules');
    }

    public function siteAdsSamples(): View
    {
        return view('public.pages.site-ads-samples');
    }

    public function siteAdsReport(): View
    {
        return view('public.pages.site-ads-report');
    }

    public function siteAdsPricing(): View
    {
        return view('public.pages.site-ads-pricing');
    }

    public function siteAdsSites(): View
    {
        return view('public.pages.site-ads-sites');
    }
}
