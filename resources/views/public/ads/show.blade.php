@extends('layouts.app', ['title' => $ad->title . ' | ' . config('app.name'), 'description' => \Illuminate\Support\Str::limit($ad->description, 160), 'canonical' => $ad->publicUrl(), 'openGraph' => ['image' => optional($ad->images->firstWhere('is_primary', true) ?? $ad->images->first())->displayUrl()]])

@push('head')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => collect($seo->breadcrumbForAd($ad))->values()->map(fn($item, $index) => ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']])->all()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@if($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired || ($ad->expires_at && $ad->expires_at->isPast()))
<div class="expired-banner" role="alert">
    <strong>⚠️ این آگهی منقضی شده است.</strong>
    <span>برای تمدید و دوباره فعال شدن این آگهی روی دکمهٔ زیر بزنید.</span>
    <a class="button button-small" href="{{ route('user.payments.index') }}">تمدید آگهی</a>
</div>
@endif

<nav class="breadcrumb" aria-label="مسیر صفحه">
    <a href="{{ route('home') }}">خانه</a><span>/</span>
    @if($ad->category)<a href="{{ route('categories.show', $ad->category) }}">{{ $ad->category->title }}</a><span>/</span>@endif
    <span>{{ $ad->title }}</span>
</nav>

<article class="ad-detail {{ $ad->is_featured ? 'ad-detail--featured' : '' }} {{ $ad->is_urgent ? 'ad-detail--urgent' : '' }}">
    @if($ad->is_urgent)
        <div class="ad-detail__urgent-ribbon" aria-label="آگهی فوری">فوری</div>
    @endif

    <section class="gallery" aria-label="تصاویر آگهی">
        @php($first = true)
        @forelse($ad->images as $image)
            <img src="{{ $image->displayUrl() }}" width="{{ $image->width }}" height="{{ $image->height }}" @if($first) fetchpriority="high" @else loading="lazy" @endif alt="{{ $ad->title }}">
            @php($first = false)
        @empty
            <div class="image-placeholder gallery-placeholder">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg>
                <span>تصویری برای این آگهی ثبت نشده است.</span>
            </div>
        @endforelse
    </section>

    <aside class="ad-sidecard">
        <div class="ad-detail-heading">
            <h1>{{ $ad->title }}</h1>
            @if($ad->is_featured)<span class="badge badge-featured">ویژه</span>@endif
            @if($ad->is_urgent)<span class="badge badge-danger">فوری</span>@endif
        </div>

        <p class="detail-price" dir="auto">{{ $ad->priceLabel() }}</p>
        <p class="ad-date">{{ $ad->published_at?->diffForHumans() }} در {{ $ad->city?->name ?? '—' }}</p>

        <dl class="details">
            <dt>کد آگهی</dt><dd dir="ltr">{{ $ad->code }}</dd>
            <dt>بازدید</dt><dd>{{ number_format($ad->views_count) }}</dd>
            <dt>تاریخ انتشار</dt><dd>{{ jdate($ad->published_at) }}</dd>
            @if($ad->expires_at)
                <dt>تاریخ انقضا</dt><dd>{{ jdate($ad->expires_at) }}</dd>
            @endif
        </dl>

        @if($ad->mobile_1)
            <a class="button contact-action" href="tel:{{ $ad->mobile_1 }}">تماس با آگهی‌دهنده</a>
        @endif
    </aside>

    <section class="description">
        <h2>توضیحات آگهی</h2>
        <p>{!! nl2br(e($ad->description)) !!}</p>

        @if($ad->links->isNotEmpty())
            <h2 style="margin-top:22px">لینک‌های مرتبط</h2>
            <ul class="ad-links">
                @foreach($ad->links as $link)
                    <li><a rel="ugc" target="_blank" href="{{ $link->url }}">{{ $link->url }}</a></li>
                @endforeach
            </ul>
        @endif

        @if(!empty($ad->keywords))
            <h2 style="margin-top:22px">برچسب‌ها</h2>
            <div class="ad-keywords">
                @foreach($ad->keywords as $kw)
                    <a class="keyword-tag" href="{{ route('tag.show', ['keyword' => $kw]) }}">{{ $kw }}</a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="report-box">
        <h2>گزارش تخلف</h2>
        <form method="post" action="{{ route('ads.reports.store', $ad->code) }}">
            @csrf
            <label class="field-label">دلیل
                <select name="reason" required>
                    <option value="">انتخاب کنید</option>
                    <option value="no_response">عدم پاسخگویی</option>
                    <option value="fraud">کلاهبرداری</option>
                    <option value="false_information">خلاف واقع</option>
                    <option value="illegal">محتوای غیرمجاز</option>
                    <option value="inappropriate">محتوای نامناسب</option>
                    <option value="overpriced">گران‌فروشی</option>
                    <option value="other">سایر</option>
                </select>
            </label>
            <label class="field-label">توضیح (اختیاری)
                <textarea name="description" rows="3" maxlength="2000"></textarea>
            </label>
            <button class="button button-small" type="submit">ثبت گزارش</button>
        </form>
    </section>
</article>

<section class="section related-section" aria-labelledby="related-title">
    <div class="section-heading">
        <div><h2 id="related-title">آگهی‌های مرتبط</h2><p>گزینه‌های مشابهی که ممکن است برای شما مناسب باشند.</p></div>
    </div>
    <div class="ad-grid">
        @forelse($related as $relatedAd)
            <x-ad-card :ad="$relatedAd" />
        @empty
            <p class="muted">آگهی مرتبطی یافت نشد.</p>
        @endforelse
    </div>
</section>

<style>
/* Featured ad page — softer background + teal border. */
.ad-detail--featured {
    background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
    border: 2px solid #14b8a6;
    border-radius: 14px;
    padding: 1.5rem;
}
/* Urgent ad page — red border + ribbon. */
.ad-detail--urgent {
    border: 2px solid #ef4444;
    border-radius: 14px;
    position: relative;
}
.ad-detail--featured.ad-detail--urgent {
    border: 2px solid #ef4444;
    background: linear-gradient(180deg, #fef2f2 0%, #ffffff 50%, #f0fdfa 100%);
}
.ad-detail__urgent-ribbon {
    position: absolute;
    top: 0;
    inset-inline-start: 1rem;
    transform: translateY(-50%);
    background: #dc2626;
    color: #fff;
    padding: .3rem 1rem;
    border-radius: 6px;
    font-weight: 700;
    font-size: .85rem;
    box-shadow: 0 2px 8px rgba(220, 38, 38, .35);
}
.badge-featured { background: #14b8a6; color: #fff; }
.badge-danger { background: #dc2626; color: #fff; }

.expired-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    background: linear-gradient(90deg, #fef3c7 0%, #fee2e2 100%);
    border: 1px solid #fbbf24;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
}
.expired-banner strong { color: #92400e; }
.expired-banner span { color: #991b1b; flex: 1; min-width: 200px; }

.ad-keywords {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    margin-top: .5rem;
}
.keyword-tag {
    background: #f0fdfa;
    color: #0f766e;
    border: 1px solid #99f6e4;
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .8rem;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.keyword-tag:hover {
    background: #ccfbf1;
    color: #115e59;
}

.ad-links {
    list-style: none;
    padding: 0;
    margin: .5rem 0 0;
}
.ad-links li {
    padding: .3rem 0;
}
.ad-links a {
    color: #0d9488;
    text-decoration: none;
    word-break: break-all;
}
.ad-links a:hover { text-decoration: underline; }
</style>
@endsection
