<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <meta name="theme-color" content="#0f766e">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? config('agahi.site_description') }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="stylesheet" href="/assets/app.css">
    @isset($openGraph)
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $title ?? config('app.name') }}">
        <meta property="og:description" content="{{ $description ?? config('agahi.site_description') }}">
        <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
        @if (!empty($openGraph['image']))<meta property="og:image" content="{{ $openGraph['image'] }}">@endif
        <meta name="twitter:card" content="summary_large_image">
    @endisset
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main-content">پرش به محتوای اصلی</a>
<header class="site-header">
    <div class="container header-row">
        <a class="brand" href="{{ route('home') }}" aria-label="صفحهٔ اصلی {{ config('app.name') }}">
            <span class="brand-mark" aria-hidden="true">آ</span><span>{{ config('app.name') }}</span>
        </a>
        <a class="city-picker" href="{{ route('search') }}" aria-label="انتخاب شهر">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="9" r="2.25"/></svg>
            <span>همهٔ شهرها</span><span class="chevron">⌄</span>
        </a>
        <form class="header-search" method="get" action="{{ route('search') }}" role="search">
            <label class="sr-only" for="header-query">جست‌وجوی آگهی</label>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
            <input id="header-query" name="q" value="{{ request('q') }}" type="search" placeholder="جست‌وجو در آگهی‌ها" autocomplete="off">
            <button type="submit">جست‌وجو</button>
        </form>
        <nav class="header-actions" aria-label="ناوبری اصلی">
            @auth
                <a class="account-link" href="{{ route('user.dashboard') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.55-4.15 3-6.25 7-6.25s6.45 2.1 7 6.25"/></svg><span>حساب من</span></a>
                @if (auth()->user()->is_staff)<a class="account-link staff-link" href="{{ route('admin.dashboard') }}">مدیریت</a>@endif
                <form class="inline-form" method="post" action="{{ route('logout') }}">@csrf<button class="text-action" type="submit">خروج</button></form>
            @else
                <a class="account-link" href="{{ route('login') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.55-4.15 3-6.25 7-6.25s6.45 2.1 7 6.25"/></svg><span>ورود | ثبت‌نام</span></a>
            @endauth
            <a class="post-ad-button" href="{{ route('user.ads.create') }}"><span>＋</span> ثبت آگهی</a>
        </nav>
    </div>
</header>
<main id="main-content" class="page-shell container">
    @if (session('success'))<div class="alert alert-success" role="status"><span>✓</span>{{ session('success') }}</div>@endif
    @if ($errors->any())
        <div class="alert alert-error" role="alert"><span>!</span><div><strong>لطفاً موارد زیر را اصلاح کنید.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif
    @yield('content')
</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div><a class="brand brand-footer" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">آ</span><span>{{ config('app.name') }}</span></a><p>بازار ساده و امن برای پیدا کردن و ثبت آگهی در شهر شما.</p></div>
        <nav aria-label="پیوندهای پایی"><a href="{{ route('search') }}">جست‌وجوی آگهی‌ها</a><a href="{{ route('user.ads.create') }}">ثبت آگهی رایگان</a><a href="{{ route('login') }}">حساب کاربری</a></nav>
        <p class="copyright">© {{ now()->year }} {{ config('app.name') }}</p>
    </div>
</footer>
</body>
</html>
