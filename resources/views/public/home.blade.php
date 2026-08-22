@extends('layouts.app')

@section('content')
<section class="home-hero">
    <div class="hero-content">
        <p class="eyebrow">بازار آگهی‌های محلی، ساده و مطمئن</p>
        <h1>هر چیزی که نیاز دارید، نزدیک شما پیدا کنید.</h1>
        <p class="hero-copy">میان آگهی‌ها جست‌وجو کنید یا در چند دقیقه آگهی خودتان را ثبت کنید.</p>
        <form class="home-search" method="get" action="{{ route('search') }}" role="search">
            <label class="sr-only" for="home-query">جست‌وجوی آگهی</label>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
            <input id="home-query" type="search" name="q" placeholder="مثلاً: تعمیرات کولر، استخدام، لوازم خانه" autocomplete="off">
            <button class="button" type="submit">جست‌وجو</button>
        </form>
        <div class="quick-links" aria-label="دسته‌های پرمراجعه">@forelse(array_slice($categories,0,4) as $category)<a href="{{ route('categories.show',['category'=>$category['slug']]) }}">{{ $category['title'] }}</a>@empty<a href="{{ route('search') }}">همهٔ آگهی‌ها</a>@endforelse</div>
    </div>
</section>

<section class="section" aria-labelledby="categories-title">
    <div class="section-heading"><div><h2 id="categories-title">دسته‌بندی‌های آگهی</h2><p>با انتخاب دسته، سریع‌تر به نتیجه برسید.</p></div><a class="section-link" href="{{ route('search') }}">همهٔ دسته‌ها</a></div>
    <div class="category-grid">
        @forelse($categories as $category)
            <article class="category-card">
                <h3><span class="category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg></span><a href="{{ route('categories.show',['category'=>$category['slug']]) }}">{{ $category['title'] }}</a></h3>
                @if($category['children'] !== [])<ul>@foreach(array_slice($category['children'],0,4) as $child)<li><a href="{{ route('categories.show',['category'=>$child['slug']]) }}">{{ $child['title'] }}</a></li>@endforeach</ul>@else<ul><li><a href="{{ route('categories.show',['category'=>$category['slug']]) }}">مشاهدهٔ آگهی‌ها</a></li></ul>@endif
            </article>
        @empty
            <div class="empty-state"><strong>دسته‌ای برای نمایش نداریم.</strong><span>به‌زودی دسته‌بندی‌های جدید در دسترس قرار می‌گیرند.</span></div>
        @endforelse
    </div>
</section>

@if($featured->isNotEmpty())
<section class="section" aria-labelledby="featured-title">
    <div class="section-heading"><div><h2 id="featured-title">آگهی‌های منتخب</h2><p>آگهی‌هایی که بیشتر دیده می‌شوند.</p></div></div>
    <div class="ad-grid ad-grid--context">@foreach($featured as $ad)<x-ad-card :ad="$ad" />@endforeach @if($featured->count() < 3)<x-post-ad-cta title="آگهی شما می‌تواند منتخب باشد" subtitle="با ثبت آگهی، مشتریان نزدیک شما را پیدا می‌کنند." />@endif</div>
</section>
@endif

<section class="section" aria-labelledby="latest-title">
    <div class="section-heading"><div><h2 id="latest-title">تازه‌ترین آگهی‌ها</h2><p>آخرین آگهی‌های منتشرشده در بازار.</p></div><a class="section-link" href="{{ route('search') }}">مشاهدهٔ همه</a></div>
    @if($latest->isNotEmpty())
        <div class="ad-grid ad-grid--context">@foreach($latest as $ad)<x-ad-card :ad="$ad" />@endforeach @if($latest->count() < 3)<x-post-ad-cta />@endif</div>
    @else
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="{{ route('user.ads.create') }}">ثبت آگهی</a></div>
    @endif
</section>
@endsection
