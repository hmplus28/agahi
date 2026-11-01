@extends('layouts.app', [
    'title'    => 'آگهی‌های '.$city->name.' | '.config('app.name'),
    'description' => 'آخرین آگهی‌های خرید و فروش و خدمات در '.$city->name.'. جست‌وجوی آگهی‌ها در '.$city->name.' به تفکیک دسته‌بندی.',
    'robots'   => $robots,
    'canonical'=> route('city.show', $city->slug).($ads->currentPage() > 1 ? '?page='.$ads->currentPage() : ''),
])

@push('head')
@php
    $ldCityBreadcrumb = [
        '@'.'context' => 'https://schema.org',
        '@'.'type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@'.'type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => route('home')],
            ['@'.'type' => 'ListItem', 'position' => 2, 'name' => $city->name, 'item' => route('city.show', $city->slug)],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ldCityBreadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span><span>{{ $city->name }}</span></nav>
<div class="page-top"><div><h1>آگهی‌های {{ $city->name }}</h1><p>آگهی‌های فعال این شهر را به تفکیک دسته‌بندی ببینید.</p></div></div>

@if($rootCategories->isNotEmpty())
<section class="subcategories" aria-labelledby="city-cats-title">
    <h2 id="city-cats-title">دسته‌بندی‌ها در {{ $city->name }}</h2>
    @foreach($rootCategories as $rootCategory)
        <a href="{{ route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $rootCategory->slug]) }}">{{ $rootCategory->title }}</a>
    @endforeach
</section>
@endif

<section aria-label="آگهی‌های شهر">
    <div class="result-bar"><strong>{{ to_persian_digits(number_format($ads->total())) }}</strong> آگهی در {{ $city->name }}</div>
    <div class="ad-grid">@forelse($ads as $ad)<x-ad-card :ad="$ad" />@empty<p class="muted">در این شهر هنوز آگهی فعالی ثبت نشده است.</p>@endforelse</div>
    {{ $ads->links() }}
</section>
@endsection
