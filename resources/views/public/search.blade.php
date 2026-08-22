@extends('layouts.app',['robots'=>'noindex, follow','title'=>'جست‌وجوی آگهی‌ها | '.config('app.name')])

@section('content')
@php
    $selectedCategory = $categories->firstWhere('id', (int) request('category'));
    $selectedCity = $cities->firstWhere('id', (int) request('city'));
    $activeFilters = collect([
        filled(request('q')) ? 'جست‌وجو: '.request('q') : null,
        $selectedCategory ? 'دسته: '.$selectedCategory->title : null,
        $selectedCity ? 'شهر: '.$selectedCity->name : null,
        filled(request('min_price')) ? 'از '.number_format((int) request('min_price')) : null,
        filled(request('max_price')) ? 'تا '.number_format((int) request('max_price')) : null,
    ])->filter();
@endphp
<div class="page-top"><div><h1>جست‌وجوی آگهی‌ها</h1><p>با چند فیلتر ساده، نتیجهٔ مورد نظرتان را پیدا کنید.</p></div></div>
<div class="search-layout">
    <aside class="filter-panel" aria-label="فیلتر نتایج">
        <div class="filter-title"><h2>فیلترها</h2><div>@if($activeFilters->isNotEmpty())<a class="clear-filters" href="{{ route('search') }}">پاک‌سازی</a>@endif<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg></div></div>
        <form class="filter-form" method="get">
            <label class="field-label">عبارت جست‌وجو<input type="search" name="q" value="{{ request('q') }}" placeholder="مثلاً موبایل سامسونگ" autocomplete="off"></label>
            <label class="field-label">دسته<select name="category"><option value="">همهٔ دسته‌ها</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category')==$category->id)>{{ $category->title }}</option>@endforeach</select></label>
            <label class="field-label">شهر<select name="city"><option value="">همهٔ شهرها</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(request('city')==$city->id)>{{ $city->name }}</option>@endforeach</select></label>
            <div class="price-fields"><label class="field-label">حداقل قیمت<span class="input-with-unit"><input type="number" min="0" name="min_price" value="{{ request('min_price') }}" inputmode="numeric" placeholder="۰"><span>تومان</span></span></label><label class="field-label">حداکثر قیمت<span class="input-with-unit"><input type="number" min="0" name="max_price" value="{{ request('max_price') }}" inputmode="numeric" placeholder="بدون سقف"><span>تومان</span></span></label></div>
            <button class="button" type="submit">نمایش نتایج</button>
        </form>
    </aside>
    <section class="search-results" aria-labelledby="search-results-heading"><h2 id="search-results-heading" class="sr-only">نتایج جست‌وجو</h2>
        <div class="result-bar" aria-live="polite"><strong>{{ number_format($ads->total()) }}</strong> آگهی پیدا شد @if(request('q')) برای «{{ request('q') }}» @endif</div>
        @if($activeFilters->isNotEmpty())<div class="active-filters" aria-label="فیلترهای فعال">@foreach($activeFilters as $filter)<span>{{ $filter }}</span>@endforeach</div>@endif
        @if($ads->isNotEmpty())
            <div class="ad-grid ad-grid--context">@foreach($ads as $ad)<x-ad-card :ad="$ad" />@endforeach @if($ads->count() < 3)<x-post-ad-cta title="آگهی شما هم می‌تواند اینجا باشد" subtitle="ثبت آگهی کمتر از چند دقیقه زمان می‌برد." />@endif</div>
        @else
            <div class="empty-state empty-state--results"><strong>آگهی منطبق با جست‌وجوی شما پیدا نشد.</strong><span>عبارت یا فیلترها را تغییر دهید، یا آگهی مورد نظرتان را ثبت کنید.</span><div><a class="button button-small" href="{{ route('search') }}">پاک‌سازی فیلترها</a><a class="secondary-action" href="{{ route('user.ads.create') }}">ثبت آگهی جدید</a></div></div>
        @endif
        {{ $ads->links() }}
    </section>
</div>
@endsection
