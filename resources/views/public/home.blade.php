@extends('layouts.app', ['canonical' => route('home').($ads->currentPage() > 1 ? '?page='.$ads->currentPage() : '')])

@push('head')
@php
    // PRD §51 - Organization structured data on the homepage.
    $ldOrganization = [
        '@'.'context' => 'https://schema.org',
        '@'.'type' => 'Organization',
        'name' => config('app.name'),
        'url' => url('/'),
        'contactPoint' => [[
            '@'.'type' => 'ContactPoint',
            'telephone' => '+98-'.ltrim(preg_replace('/[^0-9]/', '', (string) \App\Models\SiteSetting::get('branding.support_phone', config('agahi.support_phone', ''))), '0'),
            'contactType' => 'customer service',
        ]],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ldOrganization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<h1>{{ config('agahi.brand_name', 'سامانه آگهی') }} — هر چیزی که نیاز دارید، نزدیک شما پیدا کنید</h1>

<section class="home-hero" aria-label="{{ $banner ? 'بنر تبلیغاتی' : 'معرفی سامانه' }}">
    @if($banner)
        @if($banner['type'] === 'html')
            {!! $banner['html'] !!}
        @else
            <img src="{{ $banner['url'] }}" alt="بنر تبلیغاتی">
        @endif
    @else
        <div class="hero-content">
            <p class="eyebrow">{{ config('agahi.brand_name', 'سامانه آگهی') }} — بازار آگهی‌های محلی، ساده و مطمئن</p>
            <h2 class="hero-headline">هر چیزی که نیاز دارید، نزدیک شما پیدا کنید</h2>
            <p class="hero-copy">آگهی خود را در چند دقیقه ثبت کنید تا مشتریان نزدیک شما را پیدا کنند.</p>
            <div class="quick-links">
                <a href="{{ route('guest.ad.create') }}">ثبت آگهی رایگان</a>
                <a href="{{ route('search') }}">جست‌وجوی آگهی‌ها</a>
            </div>
        </div>
    @endif
</section>

<section class="home-search-section">
    <form class="home-search-form" method="get" action="{{ route('search') }}" role="search">
        <div class="home-search-row">
            <div class="home-search-input-wrap">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
                <label class="sr-only" for="home-query">جست‌وجوی آگهی</label>
                <input id="home-query" type="search" name="q" placeholder="مثلاً: تعمیرات کولر، استخدام، لوازم خانه" autocomplete="off">
            </div>
            <div class="home-cat-wrap">
                <x-category-modal :categories="$allCategories" name="category" :selected-category-id="old('category')" :hide-label="true" />
            </div>
            <div class="home-geo-wrap">
                <x-geo-modal :provinces="$provinces" :cities="$cities" name-province="province" name-city="city" :selected-province-id="old('province')" :selected-city-id="old('city')" :hide-label="true" placeholder="استان / شهر" />
            </div>
            <div class="home-price-wrap">
                <label class="sr-only" for="home-min-price">حداقل قیمت</label>
                <input id="home-min-price" type="text" name="min_price" min="0" placeholder="حداقل قیمت (تومان)" inputmode="numeric" autocomplete="off">
            </div>
            <div class="home-price-wrap">
                <label class="sr-only" for="home-max-price">حداکثر قیمت</label>
                <input id="home-max-price" type="text" name="max_price" min="0" placeholder="حداکثر قیمت (تومان)" inputmode="numeric" autocomplete="off">
            </div>
            <button class="button" type="submit">جست‌وجو</button>
        </div>
    </form>
</section>

<script>
(function(){
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
})();
</script>

<section class="section" aria-labelledby="home-cats-title">
    <div class="section-heading"><div><h2 id="home-cats-title">دسته‌بندی‌های اصلی</h2><p>دستهٔ موردنظر خود را انتخاب کنید.</p></div></div>
    <div class="home-cats">
        @foreach($categories as $rootCategory)
            <div class="home-cat">
                <a class="home-cat__title" href="{{ route('categories.show', ['category' => $rootCategory['slug']]) }}">{{ $rootCategory['title'] }}</a>
                @if(!empty($rootCategory['children']))
                    <div class="home-cat__children">
                        @foreach(array_slice($rootCategory['children'], 0, 5) as $child)
                            <a href="{{ route('categories.show', ['category' => $child['slug']]) }}">{{ $child['title'] }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</section>

@if($featuredAds->isNotEmpty() && $ads->currentPage() === 1)
<section class="section" aria-labelledby="featured-title">
    <div class="section-heading"><div><h2 id="featured-title">آگهی‌های ویژه</h2><p>آگهی‌هایی که با سرویس ویژه در ابتدا نمایش داده می‌شوند.</p></div></div>
    <div class="ad-grid ad-grid--context">@foreach($featuredAds as $ad)<x-ad-card :ad="$ad" />@endforeach</div>
</section>
@endif

@if($topCities->isNotEmpty())
<section class="section" aria-labelledby="top-cities-title">
    <div class="section-heading"><div><h2 id="top-cities-title">جست‌وجو در شهرها</h2><p>آگهی‌های شهر خود را مستقیم ببینید.</p></div></div>
    <div class="subcategories">
        @foreach($topCities as $topCity)
            <a href="{{ route('city.show', $topCity->slug) }}">{{ $topCity->name }}</a>
        @endforeach
    </div>
</section>
@endif

<section class="section" aria-labelledby="all-ads-title">
    <div class="section-heading"><div><h2 id="all-ads-title">{{ $tehranOnly ? 'آگهی‌های تهران' : 'همه آگهی‌ها' }} <span class="ads-count-badge">{{ to_persian_digits(number_format($totalActive)) }} آگهی</span></h2></div><a class="button button-small" href="{{ $tehranOnly ? route('home', ['all' => 1]) : route('home') }}">{{ $tehranOnly ? 'نمایش همهٔ شهرها' : 'فقط آگهی‌های تهران' }}</a></div>
    @if($ads->isNotEmpty())
        <div class="ad-grid ad-grid--context">@foreach($ads as $ad)<x-ad-card :ad="$ad" />@endforeach</div>
        @if($ads->hasPages())
            {{ $ads->links() }}
        @endif
    @else
        <div class="empty-state empty-state--wide"><strong>{{ $tehranOnly ? 'هنوز آگهی فعالی در تهران ثبت نشده است.' : 'هنوز آگهی فعالی ثبت نشده است.' }}</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="{{ route('user.ads.create') }}">ثبت آگهی</a></div>
    @endif
</section>

@once
<style>
/* ─────────── PRD §32 - main categories grid on the homepage ─────────── */
.home-cats{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:.875rem}
.home-cat{background:#fff;border:1px solid #e8eaee;border-radius:12px;padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.5rem}
.home-cat__title{font-weight:700;color:#3e4095;text-decoration:none;font-size:1rem}
.home-cat__title:hover{text-decoration:underline}
.home-cat__children{display:flex;flex-wrap:wrap;gap:.25rem .6rem}
.home-cat__children a{font-size:.78rem;color:#6b7280;text-decoration:none}
.home-cat__children a:hover{color:#4b4dbe}
</style>
@endonce
@endsection
