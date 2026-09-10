<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="<?php echo e($robots ?? 'index, follow'); ?>">
    <meta name="theme-color" content="#0f766e">
    <meta name="format-detection" content="telephone=no">

    <title><?php echo e($title ?? config('app.name')); ?></title>
    <meta name="description" content="<?php echo e($description ?? config('agahi.site_description')); ?>">

    
    <link rel="canonical" href="<?php echo e($canonical ?? url()->current()); ?>">

    
    <?php $ogImage = ($openGraph ?? [])['image'] ?? null; ?>
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($title ?? config('app.name')); ?>">
    <meta property="og:description" content="<?php echo e($description ?? config('agahi.site_description')); ?>">
    <meta property="og:url" content="<?php echo e($canonical ?? url()->current()); ?>">
    <meta property="og:locale" content="fa_IR">
    <?php if($ogImage): ?><meta property="og:image" content="<?php echo e($ogImage); ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($title ?? config('app.name')); ?>">
    <meta name="twitter:description" content="<?php echo e($description ?? config('agahi.site_description')); ?>">
    <?php if($ogImage): ?><meta name="twitter:image" content="<?php echo e($ogImage); ?>"><?php endif; ?>

    
    <link rel="dns-prefetch" href="//fonts.googleapis.com">

    
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📢</text></svg>">

    
    <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        
        <style>
            *,:after,:before{box-sizing:border-box;border:0 solid;margin:0;padding:0}
            html{font-family:sans-serif;-webkit-text-size-adjust:100%;tab-size:4;line-height:1.5}
            body{margin:0;background:#f9fafb;color:#1f2937}
            .container{width:100%;max-width:1280px;margin-inline:auto;padding-inline:1rem}
            .site-header{background:#fff;border-bottom:1px solid #f3f4f6;position:sticky;top:0;z-index:50}
            .header-top{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1rem;padding-block:.625rem}
            .header-top>.header-actions--start{justify-self:start}.header-top>.brand{justify-self:center}.header-top>.header-actions--end{justify-self:end}
            .header-bottom{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:1rem;padding-block:.375rem .625rem;border-top:1px solid #f3f4f6}
            .header-bottom>.city-picker{justify-self:start}.header-bottom>.header-phone{justify-self:center}.header-bottom>.header-dropdown{justify-self:end}
            .brand{display:flex;align-items:center;gap:.5rem;text-decoration:none;font-weight:700;font-size:1.15rem;color:#111827;flex-shrink:0}
            .brand-mark{display:flex;align-items:center;justify-content:center;width:2.125rem;height:2.125rem;border-radius:.625rem;background:#0d9488;color:#fff;font-size:1.125rem;font-weight:800}
            .post-ad-button{display:flex;align-items:center;gap:.25rem;background:#f97316;color:#fff;font-size:.875rem;font-weight:600;padding:.625rem 1rem;border-radius:.625rem;text-decoration:none;white-space:nowrap;flex-shrink:0}
            .post-ad-button:hover{background:#ea580c}
            .header-phone{display:flex;align-items:center;gap:.375rem;font-size:.875rem;color:#4b5563;text-decoration:none;flex-shrink:0}
            .header-phone:hover{color:#0d9488}
            .site-footer{background:#111827;color:#d1d5db;margin-top:4rem;padding-top:3rem;padding-bottom:2rem}
            .skip-link{position:absolute;top:-100%;left:1rem;z-index:1000;background:#0d9488;color:#fff;padding:.5rem 1rem;border-radius:.375rem;font-size:.875rem;font-weight:500}
            .alert{display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border-radius:.5rem;margin-bottom:1.5rem;font-size:.875rem}
            .alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
            .alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
            .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border-width:0}
        </style>
    <?php endif; ?>

    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo e(config('app.name')); ?>",
        "url": "<?php echo e(url('/')); ?>",
        "description": "<?php echo e(config('agahi.site_description')); ?>",
        "inLanguage": "fa-IR",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?php echo e(url('/search')); ?>?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <?php echo $__env->yieldPushContent('head'); ?>

    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
</head>
<body class="<?php echo e(request()->routeIs('admin.*') ? 'theme-admin' : ''); ?>">
<a class="skip-link" href="#main-content">پرش به محتوای اصلی</a>

<?php
    $selectedCityIds = collect(request()->query('cities', request()->query('city')))
        ->flatten()->map(fn ($v) => (int) $v)->filter();
    $selectedProvinceIds = collect(request()->query('provinces'))->flatten()->map(fn ($v) => (int) $v)->filter();
    $hasSelection = $selectedCityIds->isNotEmpty() || $selectedProvinceIds->isNotEmpty();
    $firstSelectedCity = $layoutProvinces->flatMap->cities->firstWhere('id', $selectedCityIds->first());
    $pickerLabel = ! $hasSelection
        ? 'همهٔ شهرها'
        : ($selectedProvinceIds->count() + $selectedCityIds->count() === 1
            ? ($firstSelectedCity?->name ?? ($layoutProvinces->firstWhere('id', $selectedProvinceIds->first())?->name ?? 'همهٔ شهرها'))
            : (($selectedProvinceIds->count() ? $selectedProvinceIds->count().' استان' : '').($selectedProvinceIds->count() && $selectedCityIds->count() ? ' و ' : '').($selectedCityIds->count() ? $selectedCityIds->count().' شهر' : '')));
    // داده فشرده موقعیت‌ها برای رندر سمت کلاینت
    $locationData = $layoutProvinces
        ->map(fn ($p) => ['i' => $p->id, 'n' => $p->name, 'cy' => $p->country_id, 'c' => $p->cities->map(fn ($c) => [$c->id, $c->name])->values()->all()])
        ->values()->toJson(JSON_UNESCAPED_UNICODE);
    $selectedCountryId = (int) request('country');
?>

<header class="site-header" role="banner">
    
    <div class="container header-top">
        <div class="header-actions header-actions--start">
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->is_staff): ?>
                    <nav class="role-switch" aria-label="تغییر نقش">
                        <a href="<?php echo e(route('user.dashboard')); ?>" class="<?php echo e(request()->routeIs('admin.*') ? '' : 'active'); ?>">حساب من</a>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="<?php echo e(request()->routeIs('admin.*') ? 'active' : ''); ?>">پنل مدیریت</a>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="صفحهٔ اصلی <?php echo e(config('app.name')); ?>">
            <img class="brand-logo" src="/img/logo.png" alt="<?php echo e(config('app.name')); ?>">
        </a>

        <?php if(auth()->guard()->check()): ?>
            <div class="header-actions header-actions--end header-actions--guest">
                <a class="post-ad-button" href="<?php echo e(route('user.ads.create')); ?>"><span aria-hidden="true">＋</span> ثبت آگهی</a>
                <form method="post" action="<?php echo e(route('logout')); ?>" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="login-button" style="cursor:pointer">خروج</button>
                </form>
            </div>
        <?php else: ?>
            <div class="header-actions header-actions--end header-actions--guest">
                <a class="post-ad-button" href="<?php echo e(route('guest.ad.create')); ?>"><span aria-hidden="true">＋</span> ثبت آگهی</a>
                <a class="login-button" href="<?php echo e(route('login')); ?>">ورود</a>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="container header-bottom">
        <button class="city-picker" type="button" id="city-picker-trigger" aria-haspopup="dialog" aria-controls="city-modal" aria-expanded="false">
            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="9" r="2.25"/></svg>
            <span id="city-picker-label"><?php echo e($pickerLabel); ?></span><span class="chevron">⌄</span>
        </button>

        <a class="header-phone" href="tel:02166248174" dir="ltr">
            <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>
            <span>تبلیغات: ۰۲۱۶۶۲۴۸۱۷۴</span>
        </a>

        <div class="header-dropdown" id="header-dropdown">
            <button class="header-dropdown__trigger" type="button" aria-haspopup="true" aria-expanded="false">
                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                <span class="chevron">⌄</span>
            </button>
            <div class="header-dropdown__menu">
                <a href="<?php echo e(route('about')); ?>">درباره ما</a>
                <a href="<?php echo e(route('contact')); ?>">تماس با ما</a>
            </div>
        </div>
    </div>
</header>

<?php echo $__env->make('public.partner_buttons', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
(function(){
    var dd=document.getElementById('header-dropdown');
    if(!dd)return;
    var trigger=dd.querySelector('.header-dropdown__trigger');
    var menu=dd.querySelector('.header-dropdown__menu');
    trigger.addEventListener('click',function(e){
        e.stopPropagation();
        var open=menu.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded',open?'true':'false');
    });
    document.addEventListener('click',function(){
        menu.classList.remove('is-open');
        trigger.setAttribute('aria-expanded','false');
    });
    menu.addEventListener('click',function(e){e.stopPropagation();});
})();
</script>

<main id="main-content" class="page-shell container" role="main">
    <?php if(session('success')): ?>
        <div class="alert alert-success" role="status">
            <span aria-hidden="true">✓</span>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="alert alert-error" role="alert">
            <span aria-hidden="true">!</span>
            <div>
                <strong>لطفاً موارد زیر را اصلاح کنید.</strong>
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>

<footer class="site-footer" role="contentinfo">
    <div class="container footer-inner">
        <div>
            <a class="brand brand-footer" href="<?php echo e(route('home')); ?>">
                <img class="brand-logo" src="/img/logo.png" alt="<?php echo e(config('app.name')); ?>">
            </a>
            <p>بازار ساده و امن برای پیدا کردن و ثبت آگهی در شهر شما. با چند کلیک آگهی بدهید یا نیازتان را پیدا کنید.</p>
        </div>
        <nav aria-label="پیوندهای پایی">
            <a href="<?php echo e(route('search')); ?>">جست‌وجوی آگهی‌ها</a>
            <a href="<?php echo e(route('user.ads.create')); ?>">ثبت آگهی رایگان</a>
            <a href="<?php echo e(route('terms')); ?>">قوانین و مقررات</a>
            <a href="<?php echo e(route('about')); ?>">درباره ما</a>
            <a href="<?php echo e(route('contact')); ?>">تماس با ما</a>
            <a href="<?php echo e(route('login')); ?>">حساب کاربری</a>
        </nav>
        <div class="footer-trust" aria-label="نماد اعتماد الکترونیکی">
            <a referrerpolicy='origin' target='_blank' rel='noopener noreferrer' href='https://trustseal.enamad.ir/?id=253106&Code=raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb'>
                <img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=253106&Code=raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb' alt='نماد اعتماد الکترونیکی' style='cursor:pointer' onerror="this.closest('.footer-trust').style.display='none'">
            </a>
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQwIiBoZWlnaHQ9IjM2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJtMTIwIDI0M2w5NC01NCAwLTEwOSAtOTQgNTQgMCAxMDkgMCAweCIgZmlsbD0iIzgwODI4NSIvPjxwYXRoIGQ9Im0xMjAgMjU0bC0xMDMtNjAgMC0xMTkgMTAzLTYwIDEwMyA2MCAwIDExOSAtMTAzIDYweiIgc3R5bGU9ImZpbGw6bm9uZTtzdHJva2UtbGluZWpvaW46cm91bmQ7c3Ryb2tlLXdpZHRoOjU7c3Ryb2tlOiMwMGFlZWYiLz48cGF0aCBkPSJtMjE0IDgwbC05NC01NCAtOTQgNTQgOTQgNTQgOTQtNTR6IiBmaWxsPSIjMDBhZWVmIi8+PHBhdGggZD0ibTI2IDgwbDAgMTA5IDk0IDU0IDAtMTA5IC05NC01NCAwIDB6IiBmaWxsPSIjNTg1OTViIi8+PHBhdGggZD0ibTEyMCAxNTdsNDctMjcgMC0yMyAtNDctMjcgLTQ3IDI3IDAgNTQgNDcgMjcgNDctMjciIHN0eWxlPSJmaWxsOm5vbmU7c3Ryb2tlLWxpbmVjYXA6cm91bmQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS13aWR0aDoxNTtzdHJva2U6I2ZmZiIvPjx0ZXh0IHg9IjE1IiB5PSIzMDAiIGZvbnQtc2l6ZT0iMjVweCIgZm9udC1mYW1pbHk9IidCIFlla2FuJyIgc3R5bGU9ImZpbGw6IzI5Mjk1Mjtmb250LXdlaWdodDpib2xkIj7Yudi22Ygg2KfYqtit2KfYr9uM2Ycg2qnYtNmI2LHbjDwvdGV4dD48dGV4dCB4PSI4IiB5PSIzNDMiIGZvbnQtc2l6ZT0iMjVweCIgZm9udC1mYW1pbHk9IidCIFlla2FuJyIgc3R5bGU9ImZpbGw6IzI5Mjk1Mjtmb250LXdlaWdodDpib2xkIj7YqdiizKqAg2Yg2qnYp9iy2YfYp9uMINGF2KzYp9iy24w8L3RleHQ+PC9zdmc+" alt="نماد اتحادیه" onclick="window.open('https://ecunion.ir/verify/shetabe.ir?token=159123862273e76a61a5', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=580, height=600, top=30')" style="cursor:pointer; width: 96px;height: 144px;">
        </div>
        <p class="copyright">© <?php echo e(jalali_year()); ?> <?php echo e(config('app.name')); ?> — تمام حقوق محفوظ است. | توسعه توسط <a href="https://daynacode.ir" target="_blank" rel="noopener" style="color:#5eead4;text-decoration:none;font-weight:600;">تیم دایناکد</a></p>
    </div>
</footer>


<script>
(function(){var d=document;d.documentElement.classList.remove('no-js');d.addEventListener('click',function(e){var t=e.target.closest('[data-toggle-class]');if(t){e.preventDefault();var el=d.querySelector(t.dataset.target);if(el)el.classList.toggle(t.dataset.toggleClass)}})})();
</script>



<div class="city-modal" id="city-modal" role="dialog" aria-modal="true" aria-label="انتخاب موقعیت" hidden
     data-locations="<?php echo e($locationData); ?>"
     data-selected-cities="<?php echo e($selectedCityIds->implode(',')); ?>"
     data-selected-provinces="<?php echo e($selectedProvinceIds->implode(',')); ?>">
    <div class="city-modal__backdrop" data-city-close aria-hidden="true"></div>
    <div class="city-modal__panel">
        <div class="city-modal__header">
            <h2>انتخاب موقعیت</h2>
            <button class="city-modal__close" type="button" data-city-close aria-label="بستن">✕</button>
        </div>
        <div class="city-modal__search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
            <input type="search" id="city-search-input" placeholder="جست‌وجوی استان یا شهر…" autocomplete="off">
        </div>
        <div class="city-modal__quick">
            <label class="city-chip <?php echo e($hasSelection ? '' : 'is-selected'); ?>">
                <input type="checkbox" data-city-all <?php echo e($hasSelection ? '' : 'checked'); ?>>
                🇮🇷 کل ایران
            </label>
        </div>
        <form id="city-select-form" method="get" action="<?php echo e(route('search')); ?>">
            <?php $__currentLoopData = request()->query(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!in_array($k, ['cities', 'city', 'provinces', 'page', 'country'])): ?>
                    <input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>">
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div class="city-modal__country">
                <label for="city-country-select">کشور</label>
                <select id="city-country-select" name="country">
                    <option value="">همهٔ کشورها</option>
                    <?php $__currentLoopData = $layoutCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>" <?php if($selectedCountryId === (int) $country->id): echo 'selected'; endif; ?>><?php echo e($country->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="city-modal__list" id="city-list" aria-live="polite"></div>
            <p class="city-empty" id="city-empty" hidden>موردی یافت نشد.</p>
            <div class="city-modal__footer">
                <span class="city-modal__hint" id="city-count-hint"></span>
                <div class="city-modal__actions">
                    <button type="button" class="city-modal__clear" id="city-clear">پاک‌سازی</button>
                    <button type="submit" class="city-modal__apply">نمایش آگهی‌ها</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .city-modal { position: fixed; inset: 0; z-index: 200; display: flex; align-items: flex-end; justify-content: center; }
    .city-modal[hidden] { display: none; }
    .city-modal__backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(2px); }
    .city-modal__panel { position: relative; display: flex; flex-direction: column; width: 100%; max-width: 560px; max-height: 88vh; min-height: 0; overflow: hidden; background: #fff; border-radius: 1rem 1rem 0 0; box-shadow: 0 -8px 40px rgba(0,0,0,.25); animation: cityModalUp .22s ease-out; }
    .city-modal__panel form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
    @keyframes cityModalUp { from { transform: translateY(24px); opacity: 0; } to { transform: none; opacity: 1; } }
    .city-modal__header { display: flex; align-items: center; justify-content: space-between; padding: .875rem 1rem .5rem; flex-shrink: 0; }
    .city-modal__header h2 { margin: 0; font-size: 1rem; font-weight: 700; color: #111827; }
    .city-modal__close { width: 2rem; height: 2rem; border: 0; border-radius: .5rem; background: #f3f4f6; color: #374151; font-size: .9rem; cursor: pointer; }
    .city-modal__close:hover { background: #e5e7eb; }
    .city-modal__search { position: relative; padding: .25rem 1rem .5rem; flex-shrink: 0; }
    .city-modal__search svg { position: absolute; right: calc(1rem + .75rem); top: 50%; translate: 0 -50%; width: 1.05rem; height: 1.05rem; fill: none; stroke: #9ca3af; stroke-width: 2; pointer-events: none; }
    .city-modal__search input { width: 100%; height: 2.5rem; padding-inline: 2.4rem .75rem; border: 1px solid #e5e7eb; border-radius: .625rem; font-size: .875rem; font-family: inherit; background: #f9fafb; }
    .city-modal__search input:focus { outline: none; border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.12); background: #fff; }
    .city-modal__quick { display: flex; gap: .5rem; padding: .25rem 1rem .5rem; flex-shrink: 0; }
    .city-modal__country { display: flex; align-items: center; gap: .5rem; padding: .25rem 1rem .5rem; flex-shrink: 0; }
    .city-modal__country label { font-size: .75rem; font-weight: 600; color: #6b7280; white-space: nowrap; }
    .city-modal__country select { flex: 1; height: 2.25rem; padding-inline: .75rem; border: 1px solid #e5e7eb; border-radius: .625rem; font-size: .85rem; font-family: inherit; background: #f9fafb; cursor: pointer; }
    .city-modal__country select:focus { outline: none; border-color: #0d9488; box-shadow: 0 0 0 3px rgba(13,148,136,.12); background: #fff; }
    .city-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .45rem .85rem; border: 1px solid #e5e7eb; border-radius: 999px; font-size: .8rem; cursor: pointer; user-select: none; transition: all .15s; }
    .city-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .city-chip.is-selected { background: #ccfbf1; border-color: #14b8a6; color: #0f766e; font-weight: 600; }
    .city-empty { text-align: center; color: #9ca3af; font-size: .85rem; padding: 1.5rem 0; margin: 0; }
    .city-modal__list { flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: .25rem 1rem 1rem; -webkit-overflow-scrolling: touch; }
    .city-loading { text-align: center; color: #9ca3af; font-size: .85rem; padding: 2rem 0; }
    .city-province { border: 1px solid #f3f4f6; border-radius: .625rem; margin-top: .5rem; overflow: hidden; transition: border-color .15s; }
    .city-province.is-selected { border-color: #99f6e4; background: #f0fdfa; }
    .city-province__head { display: flex; align-items: stretch; }
    .city-province__row { display: flex; align-items: center; gap: .5rem; flex: 1; min-width: 0; padding: .65rem .75rem; font-size: .88rem; font-weight: 600; color: #111827; cursor: pointer; user-select: none; }
    .city-province__row input { accent-color: #0d9488; width: 1rem; height: 1rem; flex-shrink: 0; }
    .city-province__row em { font-style: normal; font-size: .72rem; font-weight: 400; color: #9ca3af; margin-inline-start: auto; white-space: nowrap; }
    .city-province.is-selected .city-province__row span { color: #0f766e; }
    .city-province__toggle { width: 2.5rem; border: 0; border-inline-start: 1px solid #f3f4f6; background: transparent; color: #6b7280; font-size: 1rem; cursor: pointer; transition: transform .18s; }
    .city-province__toggle[aria-expanded="true"] { transform: rotate(180deg); }
    .city-province__toggle:hover { color: #0d9488; background: #f9fafb; }
    .city-province__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: .2rem .5rem; padding: .35rem .75rem .75rem; border-top: 1px dashed #f3f4f6; }
    .city-option { display: flex; align-items: center; gap: .4rem; padding: .38rem .45rem; border-radius: .5rem; font-size: .82rem; color: #374151; cursor: pointer; user-select: none; transition: background .12s; }
    .city-option:hover { background: #f3f4f6; }
    .city-option input { accent-color: #0d9488; width: .95rem; height: .95rem; flex-shrink: 0; }
    .city-option.is-selected span { color: #0f766e; font-weight: 600; }
    .city-modal__footer { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .75rem 1rem; border-top: 1px solid #f3f4f6; background: #fff; flex-shrink: 0; }
    .city-modal__hint { font-size: .78rem; color: #6b7280; }
    .city-modal__actions { display: flex; gap: .5rem; }
    .city-modal__clear { border: 0; background: transparent; color: #6b7280; font-size: .82rem; font-family: inherit; cursor: pointer; padding: .55rem .75rem; }
    .city-modal__clear:hover { color: #ef4444; }
    .city-modal__apply { border: 0; background: #0d9488; color: #fff; font-size: .85rem; font-weight: 600; font-family: inherit; padding: .6rem 1.25rem; border-radius: .625rem; cursor: pointer; transition: background .15s; }
    .city-modal__apply:hover { background: #0f766e; }
    @media (min-width: 640px) {
        .city-modal { align-items: center; }
        .city-modal__panel { border-radius: 1rem; animation: cityModalPop .18s ease-out; }
        @keyframes cityModalPop { from { transform: scale(.96); opacity: 0; } to { transform: none; opacity: 1; } }
    }
    body.theme-admin .city-modal__panel { background: #1e293b; }
    body.theme-admin .city-modal__header h2, body.theme-admin .city-option,
    body.theme-admin .city-province__row span { color: #e2e8f0; }
    body.theme-admin .city-option:hover { background: #334155; }
    body.theme-admin .city-province { border-color: #334155; }
    body.theme-admin .city-province.is-selected { border-color: #14b8a6; background: rgba(20,184,166,.08); }
    body.theme-admin .city-modal__close { background: #334155; color: #cbd5e1; }
    body.theme-admin .city-modal__search input { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    body.theme-admin .city-modal__country label { color: #94a3b8; }
    body.theme-admin .city-modal__country select { background: #0f172a; border-color: #334155; color: #e2e8f0; }
    body.theme-admin .city-province__grid { border-top-color: #334155; }
    body.theme-admin .city-province__toggle { border-inline-start-color: #334155; }
    body.theme-admin .city-modal__footer { border-top-color: #334155; background: #1e293b; }
</style>

<script>
(function () {
    var d = document;
    var modal = d.getElementById('city-modal');
    if (!modal) return;
    var trigger = d.getElementById('city-picker-trigger');
    var searchInput = d.getElementById('city-search-input');
    var listEl = d.getElementById('city-list');
    var emptyMsg = d.getElementById('city-empty');
    var form = d.getElementById('city-select-form');
    var allChip = modal.querySelector('[data-city-all]');
    var clearBtn = d.getElementById('city-clear');
    var hint = d.getElementById('city-count-hint');

    var LOCATIONS = [];
    try { LOCATIONS = JSON.parse(modal.dataset.locations); } catch (e) {}
    var selCities = {};
    modal.dataset.selectedCities.split(',').forEach(function (v) { if (v) selCities[v] = true; });
    var selProvinces = {};
    modal.dataset.selectedProvinces.split(',').forEach(function (v) { if (v) selProvinces[v] = true; });

    function fa(v) { return String(v == null ? '' : v).replace(/\u064A/g, '\u06CC').replace(/\u0643/g, '\u06A9'); }

    function esc(s) {
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function provinceHtml(p) {
        var checked = selProvinces[p.i] ? ' checked' : '';
        var open = !!selProvinces[p.i];
        var html = '<div class="city-province' + (open ? ' is-selected' : '') + '" data-pid="' + p.i + '" data-country="' + (p.cy || '') + '" data-name="' + esc(p.n) + '">'
            + '<div class="city-province__head">'
            + '<label class="city-province__row">'
            + '<input type="checkbox" name="provinces[]" value="' + p.i + '" data-province-checkbox' + checked + '>'
            + '<span>' + esc(p.n) + '</span><em>' + p.c.length + ' شهر</em>'
            + '</label>'
            + '<button class="city-province__toggle" type="button" data-city-toggle aria-expanded="' + (open ? 'true' : 'false') + '" aria-label="نمایش شهرهای ' + esc(p.n) + '">⌄</button>'
            + '</div>'
            + '<div class="city-province__grid" data-city-grid' + (open ? '' : ' hidden') + '>';
        p.c.forEach(function (pair) {
            var cid = pair[0], cname = pair[1];
            var cchecked = selCities[cid] ? ' checked' : '';
            html += '<label class="city-option' + (cchecked ? ' is-selected' : '') + '" data-name="' + esc(cname) + '">'
                + '<input type="checkbox" name="cities[]" value="' + cid + '" data-city-checkbox data-parent="' + p.i + '"' + cchecked + '>'
                + '<span>' + esc(cname) + '</span></label>';
        });
        return html + '</div></div>';
    }

    var countrySelect = d.getElementById('city-country-select');
    var activeCountry = countrySelect ? countrySelect.value : '';

    function applyCountryFilter() {
        listEl.querySelectorAll('.city-province[data-country]').forEach(function (block) {
            var hidden = activeCountry !== '' && block.dataset.country !== activeCountry;
            block.hidden = hidden;
            if (hidden) {
                block.querySelectorAll('[data-province-checkbox]').forEach(function (b) { b.checked = false; });
                block.querySelectorAll('[data-city-checkbox]').forEach(function (b) { b.checked = false; b.disabled = false; });
            }
        });
        refresh();
    }

    var rendered = false;
    function ensureRender() {
        if (rendered) return;
        rendered = true;
        if (!LOCATIONS.length) {
            listEl.innerHTML = '<p class="city-loading">فهرست شهرها بارگذاری نشد.</p>';
            return;
        }
        listEl.innerHTML = LOCATIONS.map(provinceHtml).join('');
        applyCountryFilter();
        refresh();
    }

    function provinces() { return Array.prototype.slice.call(listEl.querySelectorAll('[data-province-checkbox]')); }
    function allCities() { return Array.prototype.slice.call(listEl.querySelectorAll('[data-city-checkbox]')); }
    function citiesOf(pid) { return Array.prototype.slice.call(listEl.querySelectorAll('[data-city-checkbox][data-parent="' + pid + '"]')); }

    function refresh() {
        if (!rendered) return;
        var selP = provinces().filter(function (b) { return b.checked; });
        var selC = allCities().filter(function (b) { return b.checked; });

        provinces().forEach(function (pb) {
            var kids = citiesOf(pb.value);
            var n = kids.filter(function (b) { return b.checked || b.disabled; }).length;
            pb.indeterminate = n > 0 && n < kids.length && !pb.checked;
            pb.closest('.city-province').classList.toggle('is-selected', pb.checked);
        });
        allCities().forEach(function (b) {
            var opt = b.closest('.city-option');
            if (opt) opt.classList.toggle('is-selected', b.checked);
        });

        var none = selP.length === 0 && selC.length === 0;
        allChip.checked = none;
        allChip.closest('.city-chip').classList.toggle('is-selected', none);

        var countryName = '';
        if (countrySelect && countrySelect.value !== '') {
            var opt = countrySelect.options[countrySelect.selectedIndex];
            countryName = opt ? opt.text : '';
        }
        hint.textContent = none ? (countryName ? 'نمایش آگهی‌های کل کشور ' + countryName : 'نمایش آگهی‌های کل کشور')
            : (selP.length ? selP.length + ' استان' : '')
              + (selP.length && selC.length ? ' و ' : '')
              + (selC.length ? selC.length + ' شهر' : '')
              + (countryName ? ' — ' + countryName : '');
    }

    function open() {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        trigger.setAttribute('aria-expanded', 'true');
        ensureRender();
        setTimeout(function () { searchInput.focus(); }, 60);
    }

    function close() {
        modal.hidden = true;
        document.body.style.overflow = '';
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', open);
    d.querySelectorAll('[data-city-picker-open]').forEach(function (el) {
        el.addEventListener('click', function (e) { e.preventDefault(); open(); });
    });
    modal.addEventListener('click', function (e) {
        if (e.target.closest('[data-city-close]')) close();
        var btn = e.target.closest('[data-city-toggle]');
        if (btn) {
            var grid = btn.closest('.city-province').querySelector('[data-city-grid]');
            var expanded = grid.hidden;
            grid.hidden = !expanded;
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }
    });
    d.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || modal.hidden) return;
        close();
    });

    modal.addEventListener('change', function (e) {
        var t = e.target;
        if (t.matches('[data-province-checkbox]')) {
            citiesOf(t.value).forEach(function (b) { b.checked = false; b.disabled = t.checked; });
            refresh();
        } else if (t.matches('[data-city-checkbox]')) {
            refresh();
        }
    });

    allChip.addEventListener('change', function () {
        if (allChip.checked) {
            provinces().forEach(function (pb) { pb.checked = false; });
            allCities().forEach(function (b) { b.checked = false; b.disabled = false; });
            if (countrySelect && countrySelect.value !== '') countrySelect.value = '';
        }
        refresh();
    });

    searchInput.addEventListener('input', function () {
        ensureRender();
        var q = fa(searchInput.value.trim()).toLowerCase();
        var any = false;
        listEl.querySelectorAll('.city-province[data-name]').forEach(function (block) {
            if (activeCountry !== '' && block.dataset.country !== activeCountry) { block.hidden = true; return; }
            var provMatch = q !== '' && fa(block.dataset.name).toLowerCase().indexOf(q) !== -1;
            var blockAny = provMatch;
            block.querySelectorAll('.city-option').forEach(function (opt) {
                var show = q === '' || provMatch || fa(opt.dataset.name).toLowerCase().indexOf(q) !== -1;
                opt.hidden = !show;
                if (show) blockAny = true;
            });
            block.hidden = !blockAny;
            if (provMatch) {
                var grid = block.querySelector('[data-city-grid]');
                if (grid.hidden) {
                    grid.hidden = false;
                    block.querySelector('[data-city-toggle]').setAttribute('aria-expanded', 'true');
                }
            }
            if (blockAny) any = true;
        });
        emptyMsg.hidden = any;
    });

    clearBtn.addEventListener('click', function () {
        ensureRender();
        provinces().forEach(function (pb) { pb.checked = false; });
        allCities().forEach(function (b) { b.checked = false; b.disabled = false; });
        allChip.checked = true;
        if (countrySelect && countrySelect.value !== '') countrySelect.value = '';
        applyCountryFilter();
        refresh();
    });

    if (countrySelect) {
        countrySelect.addEventListener('change', function () {
            activeCountry = countrySelect.value;
            applyCountryFilter();
        });
    }

    form.addEventListener('submit', function () {
        ensureRender();
        provinces().concat(allCities()).forEach(function (b) { if (!b.checked) b.disabled = true; });
    });

    refresh();
})();
</script>


<script>
$(function () {
    var el = document.getElementById('permit-issued-display');
    if (!el || typeof $ === 'undefined' || !$.fn.persianDatepicker) return;
    var hidden = document.getElementById('permit-issued');
    function iso(ts) {
        var g = new persianDate(ts).toDate();
        return g.getFullYear() + '-' + ('0' + (g.getMonth() + 1)).slice(-2) + '-' + ('0' + g.getDate()).slice(-2);
    }
    $(el).persianDatepicker({
        format: 'YYYY/MM/DD',
        initialValueType: 'persian',
        autoClose: true,
        onSelect: function (unix) { hidden.value = iso(unix); }
    });
});
</script>
</body>
</html><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/layouts/app.blade.php ENDPATH**/ ?>