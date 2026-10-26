@extends('layouts.app',['title'=>($category->seo_title ?: 'آگهی '.$category->title).' | '.config('app.name'),'description'=>$category->seo_description ?: ($category->description ?: config('agahi.site_description')),'robots'=>$ads->total() ? 'index, follow' : 'noindex, follow'])

@section('content')
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span><span>{{ $category->title }}</span></nav>
<div class="page-top"><div><h1>آگهی‌های {{ $category->title }}</h1>@if($category->description)<p>{{ $category->description }}</p>@else<p>جدیدترین آگهی‌های این دسته را ببینید.</p>@endif</div></div>
@if($category->children->isNotEmpty())
<section class="subcategories" aria-labelledby="subcategories-title"><h2 id="subcategories-title">زیر‌دسته‌ها</h2>@foreach($category->children as $child)<a href="{{ route('categories.show',$child) }}">{{ $child->title }}</a>@endforeach</section>
@endif
<section aria-label="آگهی‌های دسته"><div class="result-bar"><strong>{{ number_format($ads->total()) }}</strong> آگهی در این دسته</div><div class="ad-grid">@forelse($ads as $ad)<x-ad-card :ad="$ad" />@empty<p class="muted">در این دسته هنوز آگهی فعالی وجود ندارد.</p>@endforelse</div>{{ $ads->links() }}</section>
@endsection
