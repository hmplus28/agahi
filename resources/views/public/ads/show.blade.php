@extends('layouts.app', ['title' => $ad->title . ' | ' . config('app.name'), 'description' => \Illuminate\Support\Str::limit($ad->description, 160), 'canonical' => $ad->publicUrl(), 'openGraph' => ['image' => optional($ad->images->firstWhere('is_primary', true) ?? $ad->images->first())->displayUrl()]])

@push('head')
@php
    // NB: '@' is concatenated so the Blade compiler never sees the
    // framework's own @context/@type directives inside this JSON-LD.
    $ldBreadcrumb = [
        '@'.'context' => 'https://schema.org',
        '@'.'type' => 'BreadcrumbList',
        'itemListElement' => collect($seo->breadcrumbForAd($ad))->values()->map(fn($item, $index) => ['@'.'type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']])->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ldBreadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')

@if(auth()->check() && $ad->user_id === auth()->user()->id && ($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired || ($ad->expires_at && $ad->expires_at->isPast())))
<div class="expired-banner" role="alert">
    <strong>⚠️ این آگهی منقضی شده است.</strong>
    <span>برای پرداخت مجدد و تمدید خودکار، آگهی را از لیست آگهی‌های منقضی خود انتخاب و پرداخت کنید.</span>
    <a class="button button-small" href="{{ route('user.expired-ads.index') }}">تمدید آگهی</a>
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
        @forelse($ad->images as $image)
            <img src="{{ $image->displayUrl() }}" width="{{ $image->width }}" height="{{ $image->height }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif alt="{{ $ad->title }}">
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

        <p class="detail-price" dir="rtl">{{ $ad->price === null ? 'توافقی' : 'ریال '.to_persian_digits(number_format($ad->price)) }}</p>
        <p class="ad-date" title="{{ $ad->published_at ? jdate_human($ad->published_at) : '' }}">{{ $ad->published_at ? to_persian_digits(jdate($ad->published_at)) : '—' }} در {{ $ad->city?->name ?? '—' }}</p>

        <dl class="details">
            <dt>کد آگهی</dt><dd dir="ltr">{{ to_persian_digits((string) $ad->code) }}</dd>
            <dt>بازدید</dt><dd>{{ to_persian_digits(number_format($ad->views_count)) }}</dd>
            <dt>تاریخ انتشار</dt><dd>{{ to_persian_digits(jdate($ad->published_at)) }}</dd>
            @if($ad->expires_at)
                <dt>تاریخ انقضا</dt><dd>{{ to_persian_digits(jdate($ad->expires_at)) }}</dd>
            @endif
        </dl>

        @php
            $telClean = fn (?string $v) => $v ? preg_replace('/[^0-9+]/', '', $v) : '';
            $contactIcons = [
                'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92Z"/></svg>',
                'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
                'pin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.33 7-12a7 7 0 1 0-14 0c0 6.67 7 12 7 12Z"/><circle cx="12" cy="9" r="2.25"/></svg>',
            ];
            $contactItems = collect();
            if ($ad->mobile_1 && $ad->show_mobile_1) $contactItems->push(['icon' => 'phone', 'label' => 'موبایل', 'value' => to_persian_digits($ad->mobile_1), 'href' => 'tel:'.$ad->mobile_1]);
            if ($ad->mobile_2) $contactItems->push(['icon' => 'phone', 'label' => 'موبایل ۲', 'value' => to_persian_digits($ad->mobile_2), 'href' => 'tel:'.$ad->mobile_2]);
            if ($ad->phone_1) $contactItems->push(['icon' => 'phone', 'label' => 'تلفن', 'value' => to_persian_digits($ad->phone_1), 'href' => 'tel:'.$telClean($ad->phone_1)]);
            if ($ad->phone_2) $contactItems->push(['icon' => 'phone', 'label' => 'تلفن ۲', 'value' => to_persian_digits($ad->phone_2), 'href' => 'tel:'.$telClean($ad->phone_2)]);
            if ($ad->email) $contactItems->push(['icon' => 'mail', 'label' => 'ایمیل', 'value' => $ad->email, 'href' => 'mailto:'.$ad->email]);
            if (filled($ad->address)) $contactItems->push(['icon' => 'pin', 'label' => 'آدرس', 'value' => $ad->address, 'href' => null]);
        @endphp

        @if($contactItems->isNotEmpty())
            <button type="button" class="button contact-action" data-contact-open>تماس با آگهی‌دهنده</button>
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

    <div class="scam-warning" role="note">
        <strong>⚠️ هشدار</strong>
        <span>قبل از دریافت کالا یا خدمات به‌صورت صحیح و کامل، از پرداخت هرگونه مبلغ به حساب افراد خودداری کنید.</span>
    </div>

    <details class="report-box">
        <summary>گزارش تخلف</summary>
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
    </details>
</article>

@if($contactItems->isNotEmpty())
<div class="contact-modal" id="contact-modal" role="dialog" aria-modal="true" aria-label="اطلاعات تماس آگهی‌دهنده">
    <div class="contact-modal__backdrop" data-contact-close aria-hidden="true"></div>
    <div class="contact-modal__box">
        <div class="contact-modal__head">
            <div>
                <h3>تماس با آگهی‌دهنده</h3>
                @if($ad->business_name || $ad->full_name)
                    <p class="contact-modal__name">{{ $ad->business_name ?: $ad->full_name }}</p>
                @endif
            </div>
            <button type="button" class="contact-modal__close" data-contact-close aria-label="بستن">✕</button>
        </div>
        <div class="contact-list">
            @foreach($contactItems as $item)
                @if($item['href'])
                    <a class="contact-item" href="{{ $item['href'] }}" @if(str_starts_with($item['href'], 'mailto:')) rel="nofollow" @endif>
                        {!! $contactIcons[$item['icon']] !!}
                        <span class="contact-item__text"><small>{{ $item['label'] }}</small><b dir="ltr">{{ $item['value'] }}</b></span>
                    </a>
                @else
                    <div class="contact-item">
                        {!! $contactIcons[$item['icon']] !!}
                        <span class="contact-item__text"><small>{{ $item['label'] }}</small><b>{{ $item['value'] }}</b></span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
<script>
(function(){
    var m=document.getElementById('contact-modal');if(!m)return;
    function open(){m.classList.add('open');document.body.style.overflow='hidden';}
    function close(){m.classList.remove('open');document.body.style.overflow='';}
    document.querySelectorAll('[data-contact-open]').forEach(function(b){b.addEventListener('click',open)});
    m.querySelectorAll('[data-contact-close]').forEach(function(b){b.addEventListener('click',close)});
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close()});
})();
</script>
@endif

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
.scam-warning{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;background:#fff6cc;border:1px solid #eed88a;border-radius:10px;padding:.9rem 1.25rem;margin-top:1.5rem}
.scam-warning strong{color:#8a6d1a;white-space:nowrap}
.scam-warning span{color:#6b5a20;flex:1;min-width:220px}

/* Featured ad page — softer background + teal border. */
.ad-detail--featured {
    background: linear-gradient(180deg, #f0f1fa 0%, #ffffff 100%);
    border: 2px solid #5557b8;
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
    background: linear-gradient(180deg, #fef2f2 0%, #ffffff 50%, #f0f1fa 100%);
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
.badge-featured { background: #5557b8; color: #fff; }
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
    background: #f0f1fa;
    color: #3e4095;
    border: 1px solid #c6c7ee;
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .8rem;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.keyword-tag:hover {
    background: #e0e1f6;
    color: #2c2e72;
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
    color: #4b4dbe;
    text-decoration: none;
    word-break: break-all;
}
.ad-links a:hover { text-decoration: underline; }
</style>
@endsection
