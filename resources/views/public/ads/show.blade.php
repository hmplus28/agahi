@extends('layouts.app',['title'=>$ad->title.' | '.config('app.name'),'description'=>\Illuminate\Support\Str::limit($ad->description,160),'canonical'=>$ad->publicUrl(),'openGraph'=>['image'=>optional($ad->images->firstWhere('is_primary',true) ?? $ad->images->first())->displayUrl()]])

@push('head')
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>collect($seo->breadcrumbForAd($ad))->values()->map(fn($item,$index)=>['@type'=>'ListItem','position'=>$index+1,'name'=>$item['name'],'item'=>$item['url']])->all()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@if($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired || ($ad->expires_at && $ad->expires_at->isPast()))
<div class="expired-banner" role="alert">
    <strong>این آگهی منقضی شده است.</strong>
    <span>برای اطلاع از شرایط جدید با آگهی‌دهنده تماس بگیرید.</span>
</div>
@endif
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span>@if($ad->category)<a href="{{ route('categories.show',$ad->category) }}">{{ $ad->category->title }}</a><span>/</span>@endif<span>{{ $ad->title }}</span></nav>
<article class="ad-detail">
    <section class="gallery" aria-label="تصاویر آگهی">
        @php($first=true)
        @forelse($ad->images as $image)
            <img src="{{ $image->displayUrl() }}" width="{{ $image->width }}" height="{{ $image->height }}" @if($first) fetchpriority="high" @else loading="lazy" @endif alt="{{ $ad->title }}">
            @php($first=false)
        @empty
            <div class="image-placeholder gallery-placeholder"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg><span>تصویری برای این آگهی ثبت نشده است.</span></div>
        @endforelse
    </section>
    <aside class="ad-sidecard">
        <div class="ad-detail-heading"><h1>{{ $ad->title }}</h1>@if($ad->is_featured)<span class="badge">ویژه</span>@endif</div>
        <p class="detail-price" dir="auto">{{ $ad->priceLabel() }}</p>
        <p class="ad-date">{{ $ad->published_at?->diffForHumans() }} در {{ $ad->city?->name ?? '—' }}</p>
        <dl class="details"><dt>کد آگهی</dt><dd dir="ltr">{{ $ad->code }}</dd><dt>بازدید</dt><dd>{{ number_format($ad->views_count) }}</dd><dt>تاریخ انتشار</dt><dd>{{ $ad->published_at?->format('Y/m/d') }}</dd></dl>
        @if($ad->mobile_1)<a class="button contact-action" href="tel:{{ $ad->mobile_1 }}">تماس با آگهی‌دهنده</a>@endif
    </aside>
    <section class="description"><h2>توضیحات آگهی</h2><p>{!! nl2br(e($ad->description)) !!}</p>@if($ad->links->isNotEmpty())<h2 style="margin-top:22px">لینک‌های مرتبط</h2><ul>@foreach($ad->links as $link)<li><a rel="ugc" target="_blank" href="{{ $link->url }}">{{ $link->url }}</a></li>@endforeach</ul>@endif</section>
    <section class="report-box"><h2>گزارش تخلف</h2><form method="post" action="{{ route('ads.reports.store',$ad->code) }}">@csrf<label class="field-label">دلیل<select name="reason" required><option value="">انتخاب کنید</option><option value="no_response">عدم پاسخگویی</option><option value="fraud">کلاهبرداری</option><option value="false_information">خلاف واقع</option><option value="illegal">محتوای غیرمجاز</option><option value="inappropriate">محتوای نامناسب</option><option value="overpriced">گران‌فروشی</option><option value="other">سایر</option></select></label><label class="field-label">توضیح (اختیاری)<textarea name="description" rows="3" maxlength="2000"></textarea></label><button class="button button-small" type="submit">ثبت گزارش</button></form></section>
</article>
<section class="section related-section" aria-labelledby="related-title"><div class="section-heading"><div><h2 id="related-title">آگهی‌های مرتبط</h2><p>گزینه‌های مشابهی که ممکن است برای شما مناسب باشند.</p></div></div><div class="ad-grid">@forelse($related as $relatedAd)<x-ad-card :ad="$relatedAd" />@empty<p class="muted">آگهی مرتبطی یافت نشد.</p>@endforelse</div></section>
@endsection
