@php
    // Featured ads are visually pinned: different background + border.
    // Urgent (فوری) ads get a red ribbon on top of the card.
    $classes = 'ad-card';
    if ($ad->is_featured) $classes .= ' ad-card--featured';
    if ($ad->is_urgent)   $classes .= ' ad-card--urgent';
@endphp

<article class="{{ $classes }}">
    @if($ad->is_urgent)
        <div class="ad-card__urgent-ribbon" aria-label="آگهی فوری">فوری</div>
    @endif

    <a class="card-image" href="{{ $ad->publicUrl() }}" aria-label="مشاهدهٔ آگهی {{ $ad->title }}">
        @php($image = $ad->images->firstWhere('is_primary', true) ?? $ad->images->first())
        @if($image)
            <img src="{{ $image->thumbUrl() }}" width="480" height="{{ max(1, (int) round(480 * $image->height / max(1, $image->width))) }}" loading="lazy" alt="{{ $ad->title }}">
        @else
            <span class="image-placeholder card-placeholder">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg>
                <span>بدون تصویر</span>
            </span>
        @endif
        @if($ad->images->count() > 1)
            <span class="card-image-count" aria-label="تعداد تصاویر">{{ $ad->images->count() }} تصویر</span>
        @endif
    </a>

    @if($ad->is_featured || $ad->is_urgent)
    <div class="badge-row">
        @if($ad->is_urgent)   <span class="badge badge-danger">فوری</span>   @endif
        @if($ad->is_featured) <span class="badge badge-featured">ویژه</span> @endif
    </div>
    @endif

    <div class="card-body">
        <h3><a href="{{ $ad->publicUrl() }}">{{ $ad->title }}</a></h3>
        <p class="price" dir="auto">{{ $ad->priceLabel() }}</p>
        <div class="card-meta">
            <span class="muted">{{ $ad->city?->name ?? '—' }}</span>
            <span class="muted">{{ $ad->published_at?->diffForHumans() }}</span>
        </div>
    </div>
</article>

<style>
/* ─────────── Featured / Urgent ad-card variants ─────────── */

/* Featured ads get a soft teal background + thicker border so they
   visually stand out from the regular cards in the grid. */
.ad-card--featured {
    background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
    border: 2px solid #14b8a6;
    box-shadow: 0 4px 16px rgba(20, 184, 166, 0.18);
    border-radius: 14px;
}
.ad-card--featured:hover {
    box-shadow: 0 6px 20px rgba(20, 184, 166, 0.28);
    transform: translateY(-2px);
}
.ad-card--featured .card-body h3 a {
    color: #0f766e;
}

/* Urgent ads get a red ribbon that says "فوری" at the top-left corner. */
.ad-card--urgent {
    position: relative;
    border: 2px solid #ef4444;
    border-radius: 14px;
}
.ad-card__urgent-ribbon {
    position: absolute;
    top: 8px;
    inset-inline-start: 8px;
    background: #dc2626;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
    z-index: 2;
    letter-spacing: 0.5px;
}
.ad-card--urgent .badge-danger {
    background: #dc2626;
    color: #fff;
}
.ad-card--featured.ad-card--urgent {
    border: 2px solid #dc2626;
    background: linear-gradient(180deg, #fef2f2 0%, #ffffff 50%, #f0fdfa 100%);
}

/* Image count badge on cards with multiple photos. */
.card-image-count {
    position: absolute;
    bottom: 8px;
    inset-inline-end: 8px;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    font-size: 0.7rem;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.badge-featured {
    background: #14b8a6;
    color: #ffffff;
}
</style>
