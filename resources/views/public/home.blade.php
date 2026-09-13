@extends('layouts.app')

@section('content')
<h1>{{ config('agahi.brand_name', 'سامانه آگهی') }} — هر چیزی که نیاز دارید، نزدیک شما پیدا کنید</h1>

<section class="home-hero" aria-label="بنر تبلیغاتی">
    @if($banner)
        @if($banner['type'] === 'html')
            {!! $banner['html'] !!}
        @else
            <img src="{{ $banner['url'] }}" alt="بنر تبلیغاتی" style="width:100%;height:auto;border-radius:.5rem;">
        @endif
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

<style>
.home-hero{border-radius:0;margin-inline-start:-.5rem;margin-inline-end:-.5rem;background:linear-gradient(135deg,#0f766e 0%,#0d9488 50%,#14b8a6 100%);text-align:center;margin-bottom:2rem;padding:4rem 2rem;position:relative;overflow:hidden;min-height:220px}
.home-hero:before{content:"";pointer-events:none;background:radial-gradient(circle at 20% 80%,rgba(255,255,255,.08) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(255,255,255,.05) 0%,transparent 50%);position:absolute;top:0;bottom:0;left:0;right:0}
.home-hero>*{position:relative;z-index:1}
.home-search-section{max-width:900px;margin:0 auto 2.5rem;text-align:center}
.home-search-form{background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.home-search-row{display:flex;gap:.625rem;align-items:stretch;flex-wrap:wrap;justify-content:center}
.home-search-input-wrap{flex:2;min-width:180px;position:relative;display:flex}
.home-search-input-wrap svg{fill:none;stroke:#9ca3af;stroke-width:2;width:1.15rem;height:1.15rem;position:absolute;top:50%;right:.75rem;translate:0 -50%;pointer-events:none}
.home-search-input-wrap input{width:100%;padding-inline:2.5rem .75rem;border:1px solid #e5e7eb;border-radius:.5rem;font-size:.9rem;font-family:inherit;background:#f9fafb;transition:border-color .15s,box-shadow .15s}
.home-search-input-wrap input:focus{outline:none;border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.12);background:#fff}
.home-cat-wrap{flex:1;min-width:160px;display:flex}
.home-cat-wrap .cat-trigger{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding-inline:.75rem;border:1px solid #e5e7eb;border-radius:.5rem;font-size:.85rem;font-family:inherit;background:#f9fafb;cursor:pointer;transition:border-color .15s}
.home-cat-wrap .cat-trigger:focus{outline:none;border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.12)}
.home-price-wrap{flex:1;min-width:130px;display:flex}
.home-price-wrap input{width:100%;padding-inline:.5rem;border:1px solid #e5e7eb;border-radius:.5rem;font-size:.85rem;font-family:inherit;background:#f9fafb;text-align:center}
.home-price-wrap input:focus{outline:none;border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,.12)}
.home-search-row .button{flex:0 0 auto;padding-inline:1.5rem;border-radius:.5rem;font-size:.9rem;font-weight:700;background:#0d9488;color:#fff;border:none;cursor:pointer;white-space:nowrap;transition:background .15s}
.home-search-row .button:hover{background:#0f766e}
@media(max-width:640px){
    .home-hero{padding:2rem 1rem}
    .home-search-row{flex-direction:column}
    .home-search-row>*{flex:1 1 100%}
}
</style>

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

@if($featured->isNotEmpty())
<section class="section" aria-labelledby="featured-title">
    <div class="section-heading"><div><h2 id="featured-title">آگهی‌های منتخب</h2><p>آگهی‌هایی که بیشتر دیده می‌شوند.</p></div></div>
    <div class="ad-grid ad-grid--context">@foreach($featured as $ad)<x-ad-card :ad="$ad" />@endforeach @if($featured->count() < 3)<x-post-ad-cta title="آگهی شما می‌تواند منتخب باشد" subtitle="با ثبت آگهی، مشتریان نزدیک شما را پیدا می‌کنند." />@endif</div>
</section>
@endif

<section class="section" aria-labelledby="latest-title">
    <div class="section-heading"><div><h2 id="latest-title">تازه‌ترین آگهی‌ها</h2></div></div>
    @if($latest->isNotEmpty())
        <div class="ad-grid ad-grid--context">@foreach($latest as $ad)<x-ad-card :ad="$ad" />@endforeach</div>
        @if($latest->hasPages())
            <div class="pagination-wrap">{{ $latest->links() }}</div>
        @endif
    @else
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="{{ route('user.ads.create') }}">ثبت آگهی</a></div>
    @endif
</section>
@endsection
