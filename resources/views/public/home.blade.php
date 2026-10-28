@extends('layouts.app')

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

<section class="section" aria-labelledby="all-ads-title">
    <div class="section-heading"><div><h2 id="all-ads-title">همه آگهی‌ها <span class="ads-count-badge">{{ to_persian_digits(number_format($totalActive)) }} آگهی</span></h2></div></div>
    @if($ads->isNotEmpty())
        <div class="ad-grid ad-grid--context">@foreach($ads as $ad)<x-ad-card :ad="$ad" />@endforeach</div>
        @if($ads->hasPages())
            {{ $ads->links() }}
        @endif
    @else
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="{{ route('user.ads.create') }}">ثبت آگهی</a></div>
    @endif
</section>
@endsection
