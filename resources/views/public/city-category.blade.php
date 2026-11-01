@extends('layouts.app', [
    'title'    => 'آگهی '.$category->title.' در '.$city->name.' | '.config('app.name'),
    'description' => $category->seo_description ?: ('آگهی‌های '.$category->title.' در '.$city->name.' — جدیدترین آگهی‌های خرید، فروش و خدمات این دسته در '.$city->name.'.'),
    'robots'   => $robots,
    'canonical'=> route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $category->slug]).($ads->currentPage() > 1 ? '?page='.$ads->currentPage() : ''),
])

@push('head')
@php
    $canonicalUrl = route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $category->slug]).($ads->currentPage() > 1 ? '?page='.$ads->currentPage() : '');
    $items = [['@'.'type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => route('home')]];
    $items[] = ['@'.'type' => 'ListItem', 'position' => count($items) + 1, 'name' => $city->name, 'item' => route('city.show', $city->slug)];
    foreach ($chain as $node) {
        if ($node->id === $category->id) { continue; }
        $items[] = ['@'.'type' => 'ListItem', 'position' => count($items) + 1, 'name' => $node->title, 'item' => route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $node->slug])];
    }
    $items[] = ['@'.'type' => 'ListItem', 'position' => count($items) + 1, 'name' => $category->title.' در '.$city->name, 'item' => $canonicalUrl];
    $ldCityCatBreadcrumb = [
        '@'.'context' => 'https://schema.org',
        '@'.'type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ldCityCatBreadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<nav class="breadcrumb" aria-label="مسیر صفحه">
    <a href="{{ route('home') }}">خانه</a><span>/</span>
    <a href="{{ route('city.show', $city->slug) }}">{{ $city->name }}</a><span>/</span>
    @foreach($chain as $node)
        @if($node->id === $category->id)
            <span>{{ $node->title }}</span>
        @else
            <a href="{{ route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $node->slug]) }}">{{ $node->title }}</a><span>/</span>
        @endif
    @endforeach
</nav>

<div class="page-top"><div><h1>آگهی {{ $category->title }} در {{ $city->name }}</h1>@if($category->description)<p>{{ $category->description }}</p>@else<p>جدیدترین آگهی‌های این دسته در {{ $city->name }} را ببینید.</p>@endif</div></div>

@if($category->children->isNotEmpty())
<section class="subcategories" aria-labelledby="city-subcats-title">
    <h2 id="city-subcats-title">زیر‌دسته‌ها در {{ $city->name }}</h2>
    @foreach($category->children as $child)
        <a href="{{ route('city.sub-category', ['citySlug' => $city->slug, 'categorySlug' => $category->slug, 'subSlug' => $child->slug]) }}">{{ $child->title }}</a>
    @endforeach
</section>
@endif

@if($siblings->isNotEmpty())
<section class="subcategories" aria-labelledby="city-siblings-title">
    <h2 id="city-siblings-title">دسته‌های دیگر در {{ $city->name }}</h2>
    @foreach($siblings as $sibling)
        @if($sibling->id === $category->id)
            <a class="is-current" href="{{ route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $sibling->slug]) }}">{{ $sibling->title }}</a>
        @else
            <a href="{{ route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $sibling->slug]) }}">{{ $sibling->title }}</a>
        @endif
    @endforeach
</section>
@endif

<section aria-label="آگهی‌های دسته در شهر">
    <div class="result-bar"><strong>{{ to_persian_digits(number_format($ads->total())) }}</strong> آگهی</div>
    <div class="ad-grid">@forelse($ads as $ad)<x-ad-card :ad="$ad" />@empty<p class="muted">در این دسته و شهر هنوز آگهی فعالی ثبت نشده است.</p>@endforelse</div>
    {{ $ads->links() }}
</section>

<style>
.subcategories a.is-current{background:#4b4dbe;color:#fff;border-color:#4b4dbe}
</style>
@endsection
