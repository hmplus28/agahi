@extends('layouts.app',['robots'=>'noindex, follow','title'=>'جست‌وجوی آگهی‌ها | '.config('app.name')])

@section('content')
@php
    $selectedCategory = $categories->firstWhere('id', (int) request('category'));
    $selectedCity = $cities->firstWhere('id', (int) request('city'));
    $selectedProvince = $provinces->firstWhere('id', (int) request('province'));
    $selectedCountry = $countries->firstWhere('id', (int) request('country'));
    $activeFilters = collect([
        filled(request('q')) ? 'جست‌وجو: '.request('q') : null,
        $selectedCategory ? 'دسته: '.$selectedCategory->title : null,
        $selectedCountry ? 'کشور: '.$selectedCountry->name : null,
        $selectedProvince ? 'استان: '.$selectedProvince->name : null,
        $selectedCity ? 'شهر: '.$selectedCity->name : null,
        filled(request('min_price')) ? 'از '.number_format((int) request('min_price')).' تومان' : null,
        filled(request('max_price')) ? 'تا '.number_format((int) request('max_price')).' تومان' : null,
    ])->filter();
@endphp

<div class="page-top"><div><h1>جست‌وجوی آگهی‌ها</h1><p>با چند فیلتر ساده، نتیجهٔ مورد نظرتان را پیدا کنید.</p></div></div>

<div class="search-layout">
    <aside class="filter-panel" aria-label="فیلتر نتایج">
        <div class="filter-title"><h2>فیلترها</h2><div>@if($activeFilters->isNotEmpty())<a class="clear-filters" href="{{ route('search') }}">پاک‌سازی</a>@endif<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg></div></div>
        <form class="filter-form" method="get">
            <label class="field-label">عبارت جست‌وجو
                <input type="search" name="q" value="{{ request('q') }}" placeholder="مثلاً موبایل سامسونگ" autocomplete="off">
            </label>

            {{-- دسته‌بندی --}}
            <div class="field-label">دسته‌بندی
                <x-category-modal :categories="$allCategories" name="category" :selected-category-id="request('category')" :hide-label="true" />
            </div>

            {{-- کشور --}}
            <label class="field-label">کشور
                <select name="country" data-filter-select>
                    <option value="">همهٔ کشورها</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" @selected(request('country')==$country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </label>

            {{-- استان --}}
            <label class="field-label">استان
                <select name="province" data-filter-select data-cascade-province>
                    <option value="">همهٔ استان‌ها</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" data-country="{{ $province->country_id }}" @selected(request('province')==$province->id)>{{ $province->name }}</option>
                    @endforeach
                </select>
            </label>

            {{-- شهر --}}
            <label class="field-label">شهر
                <select name="city" data-filter-select data-cascade-city>
                    <option value="">همهٔ شهرها</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(request('city')==$city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </label>

            {{-- قیمت --}}
            <div class="price-fields">
                <label class="field-label">حداقل قیمت
                    <span class="input-with-unit"><input type="number" min="0" name="min_price" value="{{ request('min_price') }}" inputmode="numeric" placeholder="تومان" title="قیمت به تومان"></span>
                </label>
                <label class="field-label">حداکثر قیمت
                    <span class="input-with-unit"><input type="number" min="0" name="max_price" value="{{ request('max_price') }}" inputmode="numeric" placeholder="تومان" title="قیمت به تومان"></span>
                </label>
            </div>

            <button class="button" type="submit">نمایش نتایج</button>
        </form>
    </aside>

    <section class="search-results" aria-labelledby="search-results-heading">
        <h2 id="search-results-heading" class="sr-only">نتایج جست‌وجو</h2>
        <div class="result-bar" aria-live="polite"><strong>{{ number_format($ads->total()) }}</strong> آگهی پیدا شد @if(request('q')) برای «{{ request('q') }}» @endif</div>
        @if($activeFilters->isNotEmpty())
            <div class="active-filters" aria-label="فیلترهای فعال">
                @foreach($activeFilters as $filter)<span>{{ $filter }}</span>@endforeach
            </div>
        @endif
        @if($ads->isNotEmpty())
            <div class="ad-grid ad-grid--context">
                @foreach($ads as $ad)<x-ad-card :ad="$ad" />@endforeach
                @if($ads->count() < 3)<x-post-ad-cta title="آگهی شما هم می‌تواند اینجا باشد" subtitle="ثبت آگهی کمتر از چند دقیقه زمان می‌برد." />@endif
            </div>
        @else
            <div class="empty-state empty-state--results">
                <strong>آگهی منطبق با جست‌وجوی شما پیدا نشد.</strong>
                <span>عبارت یا فیلترها را تغییر دهید، یا آگهی مورد نظرتان را ثبت کنید.</span>
                <div class="empty-actions">
                    <a class="button button-outline" href="{{ route('search') }}">پاک‌سازی فیلترها</a>
                    <a class="button" href="{{ route('user.ads.create') }}">ثبت آگهی جدید</a>
                </div>
            </div>
        @endif
        {{ $ads->links() }}
    </section>
</div>

<style>
.empty-actions{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap}
.empty-actions .button{min-width:140px}
.price-fields{display:flex;gap:.75rem}
.price-fields .field-label{flex:1}
.price-fields .input-with-unit input{width:100%}
</style>

<script>
(function(){
    var provinceSel=document.querySelector('[data-cascade-province]');
    var citySel=document.querySelector('[data-cascade-city]');
    if(provinceSel&&citySel){
        var allOpts=Array.from(citySel.options);
        provinceSel.addEventListener('change',function(){
            var pid=this.value;
            var first=citySel.options[0];
            citySel.innerHTML='';citySel.appendChild(first);
            allOpts.forEach(function(o){
                if(!pid||o.dataset.province===pid){citySel.appendChild(o.cloneNode(true));}
            });
            citySel.value='';
        });
    }
})();
</script>

@endsection
