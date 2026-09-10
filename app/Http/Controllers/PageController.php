<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Renders lightweight static public pages (about / contact / terms).
 * These pages are mostly server-rendered Blade with no dynamic data so they
 * stay fast on shared hosting and easy to maintain.
 */
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
}
