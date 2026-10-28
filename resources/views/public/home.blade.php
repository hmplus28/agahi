@extends('layouts.app')

@php
    // Inline stroke-icon per root category, matched by title (fallback: tag icon).
    $categoryIcons = [
        'خدمات'                 => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        'املاک'                 => '<path d="M4 21V7l8-5 8 5v14"/><path d="M2 21h20"/><path d="M9 21v-6h6v6"/><path d="M9 11h.01"/><path d="M15 11h.01"/>',
        'وسایل نقلیه'           => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
        'لوازم خانگی و صنعتی'   => '<rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="12" r="5"/><circle cx="7.5" cy="5" r=".5"/><path d="M12 9.5V12l1.7 1"/>',
        'کیف، کفش و پوشاک'      => '<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>',
        'دیجیتال'               => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M12 18h.01"/>',
        'آموزش و کتاب'          => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'ورزش و سرگرمی'         => '<path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/>',
        'استخدام و کسب‌وکار'    => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    ];
    $defaultCategoryIcon = '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5"/>';
@endphp

@section('content')
<h1>{{ config('agahi.brand_name', 'سامانه آگهی') }} — هر چیزی که نیاز دارید، نزدیک شما پیدا کنید</h1>

{{-- Banner: admin-uploaded banner wins; otherwise the default 3-banner promo row. --}}
@if($banner)
    <section class="home-hero" aria-label="{{ $banner['type'] === 'html' ? 'بنر تبلیغاتی' : 'معرفی سامانه' }}">
        @if($banner['type'] === 'html')
            {!! $banner['html'] !!}
        @else
            <img src="{{ $banner['url'] }}" alt="بنر تبلیغاتی">
        @endif
    </section>
@else
    <section class="home-promos" aria-label="معرفی خدمات سامانه">
        <a class="promo-banner" href="{{ route('guest.ad.create') }}">
            <img src="{{ asset('img/banners/post-free.jpg') }}" width="1152" height="864" alt="ثبت آگهی رایگان" loading="eager">
            <span class="promo-banner__overlay">
                <strong>آگهی‌تان را رایگان ثبت کنید</strong>
                <span>در چند دقیقه منتشر می‌شود؛ بدون نیاز به ثبت‌نام.</span>
                <span class="promo-banner__cta">ثبت آگهی رایگان</span>
            </span>
        </a>
        <a class="promo-banner" href="{{ route('site-ads.pricing') }}">
            <img src="{{ asset('img/banners/promote.jpg') }}" width="1152" height="864" alt="خدمات ویژه آگهی" loading="eager">
            <span class="promo-banner__overlay">
                <strong>دیده‌شدن بیشتر با خدمات ویژه</strong>
                <span>ویژه، فوری، رنگی و نردبان در یک نگاه.</span>
                <span class="promo-banner__cta">مشاهدهٔ تعرفه‌ها</span>
            </span>
        </a>
        <a class="promo-banner" href="#home-categories">
            <img src="{{ asset('img/banners/categories.jpg') }}" width="1152" height="864" alt="دسته‌بندی‌های متنوع آگهی" loading="eager">
            <span class="promo-banner__overlay">
                <strong>هر چه نیاز دارید، در یک سامانه</strong>
                <span>از املاک و خودرو تا خدمات و استخدام.</span>
                <span class="promo-banner__cta">مشاهدهٔ دسته‌بندی‌ها</span>
            </span>
        </a>
    </section>
@endif

<section class="home-search-section">
    <form class="home-search-form" method="get" action="{{ route('search') }}" role="search">
        <div class="home-search-grid">
            <div class="home-search-input-wrap home-search-grid__wide">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
                <label class="sr-only" for="home-query">جست‌وجوی آگهی</label>
                <input id="home-query" type="search" name="q" placeholder="مثلاً: تعمیرات کولر، استخدام، لوازم خانه" autocomplete="off">
            </div>
            <div class="home-cat-wrap">
                <x-category-modal :categories="$allCategories" name="category" :selected-category-id="old('category')" :hide-label="true" />
            </div>
            <button class="button" type="submit">جست‌وجو</button>

            <div class="home-field">
                <label class="sr-only" for="home-province">استان</label>
                <select id="home-province" name="province">
                    <option value="">همهٔ استان‌ها</option>
                </select>
            </div>
            <div class="home-field">
                <label class="sr-only" for="home-city">شهر</label>
                <select id="home-city" name="city">
                    <option value="">همهٔ شهرها</option>
                </select>
            </div>
            <div class="home-price-wrap">
                <label class="sr-only" for="home-min-price">حداقل قیمت</label>
                <input id="home-min-price" type="text" name="min_price" min="0" placeholder="حداقل قیمت (تومان)" inputmode="numeric" autocomplete="off">
            </div>
            <div class="home-price-wrap">
                <label class="sr-only" for="home-max-price">حداکثر قیمت</label>
                <input id="home-max-price" type="text" name="max_price" min="0" placeholder="حداکثر قیمت (تومان)" inputmode="numeric" autocomplete="off">
            </div>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Province → city cascading selects, populated from the header city-modal
       location data that is already embedded in the page (no extra payload).
       NOTE: the city-modal markup lives after <main> in the layout, so this
       must wait for DOMContentLoaded before querying it. */
    var modal = document.getElementById('city-modal');
    var pSel = document.getElementById('home-province');
    var cSel = document.getElementById('home-city');
    if (modal && pSel && cSel) {
        var locs = [];
        try { locs = JSON.parse(modal.dataset.locations); } catch (e) {}
        locs.forEach(function (p) {
            var o = document.createElement('option');
            o.value = p.i; o.textContent = p.n;
            pSel.appendChild(o);
        });
        function fillCities(pid) {
            cSel.innerHTML = '';
            var all = document.createElement('option');
            all.value = ''; all.textContent = 'همهٔ شهرها';
            cSel.appendChild(all);
            var p = null;
            for (var i = 0; i < locs.length; i++) {
                if (String(locs[i].i) === String(pid)) { p = locs[i]; break; }
            }
            if (!p) { cSel.disabled = true; return; }
            p.c.forEach(function (pair) {
                var o = document.createElement('option');
                o.value = pair[0]; o.textContent = pair[1];
                cSel.appendChild(o);
            });
            cSel.disabled = false;
        }
        cSel.disabled = true;
        pSel.addEventListener('change', function () { fillCities(this.value); });

        // Prefill when arriving with ?province=&city= in the query string.
        var qp = new URLSearchParams(window.location.search);
        var qProvince = qp.get('province'), qCity = qp.get('city');
        if (qProvince) {
            pSel.value = qProvince;
            if (pSel.value === qProvince) {
                fillCities(qProvince);
                if (qCity) { cSel.value = qCity; }
            }
        }
    }

    function formatPrice(el){
        var raw=el.value.replace(/[^\d]/g,'');
        if(!raw){el.value='';return;}
        el.value=raw.replace(/\B(?=(\d{3})+(?!\d))/g,',')+' تومان';
    }
    function cleanPrice(el){
        var v=el.value.replace(/[^۰-۹0-9]/g,'');
        // convert persian digits
        v=v.replace(/[۰-۹]/g,function(d){return String.fromCharCode(d.charCodeAt(0)-1776);});
        return v;
    }
    document.querySelectorAll('.home-price-wrap input').forEach(function(el){
        el.addEventListener('blur',function(){ formatPrice(this); });
        el.addEventListener('focus',function(){ this.value=cleanPrice(this); this.select(); });
        el.addEventListener('input',function(){
            var v=cleanPrice(this);
            if(v){this.value=v.replace(/\B(?=(\d{3})+(?!\d))/g,',');}else{this.value='';}
        });
        if(el.value) formatPrice(el);
    });
});
</script>

<section class="section home-categories" id="home-categories" aria-labelledby="home-categories-title">
    <div class="section-heading">
        <div>
            <h2 id="home-categories-title">دسته‌بندی‌های خدمات</h2>
            <p>هر چه نیاز دارید، در دستهٔ مربوط به خودش پیدا کنید.</p>
        </div>
        <a class="section-link" href="{{ route('search') }}">همهٔ آگهی‌ها</a>
    </div>
    <div class="category-grid">
        @foreach($categories as $category)
            <article class="category-card">
                <h3>
                    <span class="category-icon" aria-hidden="true"><svg viewBox="0 0 24 24">{!! $categoryIcons[$category['title']] ?? $defaultCategoryIcon !!}</svg></span>
                    <a href="{{ route('categories.show', $category['slug']) }}">{{ $category['title'] }}</a>
                </h3>
                @if(!empty($category['children']))
                    <ul>
                        @foreach(array_slice($category['children'], 0, 4) as $child)
                            <li><a href="{{ route('categories.show', $child['slug']) }}">{{ $child['title'] }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </article>
        @endforeach
    </div>
</section>

<section class="section home-stats" aria-label="آمار سامانه">
    <div class="stat-grid">
        <div class="stat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
            <strong>{{ to_persian_digits(number_format($totalActive)) }}</strong>
            <span>آگهی فعال</span>
        </div>
        <div class="stat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            <strong>{{ to_persian_digits(count($categories)) }}</strong>
            <span>دسته‌بندی خدمات</span>
        </div>
        <div class="stat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="9" r="2.25"/></svg>
            <strong>{{ to_persian_digits(number_format($stats['cities'])) }}</strong>
            <span>شهر تحت پوشش</span>
        </div>
        <div class="stat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <strong>{{ to_persian_digits(number_format($stats['users'])) }}</strong>
            <span>کاربر عضو</span>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="all-ads-title">
    <div class="section-heading"><div><h2 id="all-ads-title">همه آگهی‌ها <span class="ads-count-badge">{{ to_persian_digits(number_format($totalActive)) }} آگهی</span></h2></div></div>
    @if($ads->isNotEmpty())
        <div class="ad-grid ad-grid--context">@foreach($ads as $ad)<x-ad-card :ad="$ad" />@endforeach</div>
        @if($ads->hasPages())
            {{ $ads->links() }}
        @endif
    @else
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="{{ route('guest.ad.create') }}">ثبت آگهی</a></div>
    @endif
</section>

<section class="section home-steps" aria-labelledby="home-steps-title">
    <div class="section-heading">
        <div>
            <h2 id="home-steps-title">ثبت آگهی در سه گام</h2>
            <p>از ثبت تا تماس مشتری، کل مسیر چند دقیقه بیشتر طول نمی‌کشد.</p>
        </div>
    </div>
    <ol class="steps-grid">
        <li class="step-card">
            <span class="step-num" aria-hidden="true">۱</span>
            <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/></svg></span>
            <h3>آگهی‌تان را ثبت کنید</h3>
            <p>فرم ثبت آگهی را کامل کنید، تصاویر واقعی بگذارید و شماره تماس را وارد کنید.</p>
        </li>
        <li class="step-card">
            <span class="step-num" aria-hidden="true">۲</span>
            <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h3>تایید و انتشار</h3>
            <p>آگهی پایه بلافاصله منتشر می‌شود؛ آگهی‌های دارای خدمت پولی پس از پرداخت و بررسی فعال می‌شوند.</p>
        </li>
        <li class="step-card">
            <span class="step-num" aria-hidden="true">۳</span>
            <span class="step-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg></span>
            <h3>تماس مستقیم مشتری</h3>
            <p>مشتریان از صفحهٔ آگهی با شما تماس می‌گیرند؛ بدون واسطه و بدون هزینهٔ اضافه.</p>
        </li>
    </ol>
</section>

<section class="section home-benefits" aria-labelledby="home-benefits-title">
    <div class="section-heading">
        <div>
            <h2 id="home-benefits-title">چرا {{ config('agahi.brand_name', 'سامانه آگهی') }}؟</h2>
            <p>یک بازار ساده و امن برای خرید و فروش در شهر شما.</p>
        </div>
    </div>
    <div class="benefits-grid">
        <article class="benefit-card">
            <span class="benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 12v10H4V12"/><path d="M2 7h20v5H2z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg></span>
            <h3>ثبت آگهی رایگان</h3>
            <p>آگهی پایه بدون هیچ هزینه‌ای برای {{ to_persian_digits(config('agahi.free_ad_duration_days', 30)) }} روز کامل در سایت می‌ماند.</p>
        </article>
        <article class="benefit-card">
            <span class="benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/><path d="M8 11h6"/><path d="M11 8v6"/></svg></span>
            <h3>جست‌وجوی دقیق</h3>
            <p>با فیلتر قیمت، دسته‌بندی، استان و شهر به سرعت به نتیجهٔ دلخواه برسید.</p>
        </article>
        <article class="benefit-card">
            <span class="benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg></span>
            <h3>بدون واسطه</h3>
            <p>مستقیم با فروشنده یا خدمت‌دهنده در ارتباط باشید؛ هزینه و زمان کمتر.</p>
        </article>
        <article class="benefit-card">
            <span class="benefit-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg></span>
            <h3>خدمات ویژه</h3>
            <p>با ویژه، فوری، رنگی و نردبان، آگهی‌تان را در صدر نتایج ببینید.</p>
        </article>
    </div>
</section>

<section class="section home-faq" aria-labelledby="home-faq-title">
    <div class="section-heading">
        <div>
            <h2 id="home-faq-title">سوالات متداول</h2>
            <p>پاسخ رایج‌ترین پرسش‌ها دربارهٔ ثبت و نمایش آگهی.</p>
        </div>
    </div>
    <div class="faq-list">
        <details class="faq-item">
            <summary>چطور آگهی ثبت کنم؟<span class="faq-chevron" aria-hidden="true">⌄</span></summary>
            <p>روی دکمهٔ «ثبت آگهی» بالای صفحه بزنید و فرم را کامل کنید. آگهی پایه بلافاصله منتشر می‌شود و نیازی به ثبت‌نام نیست؛ با ساخت حساب کاربری اما می‌توانید آگهی‌های‌تان را هر زمان مدیریت کنید.</p>
        </details>
        <details class="faq-item">
            <summary>هزینهٔ ثبت آگهی چقدر است؟<span class="faq-chevron" aria-hidden="true">⌄</span></summary>
            <p>ثبت آگهی پایه رایگان است. خدمات اختیاری مثل ویژه، فوری، رنگی و نردبان با قیمت مشخص در صفحهٔ «تعرفهٔ آگهی» فهرست شده‌اند و پیش از پرداخت، مبلغ نهایی فاکتور را می‌بینید.</p>
        </details>
        <details class="faq-item">
            <summary>آگهی من چه زمانی تایید می‌شود؟<span class="faq-chevron" aria-hidden="true">⌄</span></summary>
            <p>آگهی‌های رایگان همان لحظه فعال می‌شوند. آگهی‌هایی که خدمت پولی دارند پس از تسویهٔ فاکتور و بررسی کارشناسان (معمولاً کمتر از ۲۴ ساعت) منتشر می‌شوند و وضعیت‌شان در پنل کاربری قابل پیگیری است.</p>
        </details>
        <details class="faq-item">
            <summary>چطور آگهی‌ام را در صدر نتایج ببینم؟<span class="faq-chevron" aria-hidden="true">⌄</span></summary>
            <p>با خرید خدمات «ویژه» یا «فوری» آگهی شما بالاتر از بقیه نمایش داده می‌شود؛ خدمت «نردبان» هم آگهی را به‌صورت دوره‌ای به بالای فهرست برمی‌گرداند. همهٔ این خدمات در صفحهٔ تعرفهٔ آگهی قابل خریداری است.</p>
        </details>
        <details class="faq-item">
            <summary>تخلف در یک آگهی را به چه کسی گزارش بدهم؟<span class="faq-chevron" aria-hidden="true">⌄</span></summary>
            <p>در صفحهٔ هر آگهی دکمهٔ «گزارش تخلف» وجود دارد؛ گزارش شما مستقیماً برای کارشناسان ما ارسال می‌شود و پس از بررسی، در صورت تخلف آگهی حذف خواهد شد.</p>
        </details>
    </div>
</section>
@endsection
