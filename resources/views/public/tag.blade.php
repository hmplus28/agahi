@extends('layouts.app', ['title' => 'برچسب: ' . $keyword . ' | ' . config('app.name'), 'robots' => 'index, follow'])

@section('content')
<section class="tag-page">
    <header class="tag-header">
        <h1>آگهی‌های برچسب «{{ $keyword }}»</h1>
        <p class="muted">{{ number_format($ads->total()) }} آگهی با این برچسب یافت شد.</p>
    </header>

    @if($ads->isNotEmpty())
        <div class="ad-grid ad-grid--context">
            @foreach($ads as $ad)
                <x-ad-card :ad="$ad" />
            @endforeach
        </div>
        @if($ads->hasPages())
            <div class="pagination-wrap">{{ $ads->links() }}</div>
        @endif
    @else
        <div class="empty-state">
            <strong>آگهی‌ای با این برچسب پیدا نشد.</strong>
            <span>می‌توانید کمی بعد دوباره بررسی کنید یا از جست‌وجوی کلی استفاده کنید.</span>
            <a class="button" href="{{ route('search') }}">جست‌وجوی همهٔ آگهی‌ها</a>
        </div>
    @endif
</section>

<style>
.tag-page{padding:0;}
.tag-header{margin-bottom:1.5rem;}
.tag-header h1{font-size:1.5rem;font-weight:700;color:#0f766e;}
.tag-header .muted{color:#6b7280;font-size:.875rem;margin-top:.25rem;}
.empty-state{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:3rem 1rem;text-align:center;}
.empty-state strong{font-size:1.05rem;color:#374151;}
.empty-state span{color:#6b7280;font-size:.875rem;margin-bottom:.5rem;}
.pagination-wrap{margin-top:1.5rem;display:flex;justify-content:center;}
</style>
@endsection
