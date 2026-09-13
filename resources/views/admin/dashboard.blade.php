@extends('layouts.app', ['title' => 'مدیریت | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="section-heading"><div><h1>داشبورد مدیریت</h1><p class="muted">نمای کلی وضعیت تعدیل، داده‌های پایه، مالی و پشتیبانی.</p></div><a class="button" href="{{ route('admin.ads.index') }}">مدیریت آگهی‌ها</a></div>

<nav class="admin-toolbar" aria-label="ماژول‌های مدیریت">
    <div class="admin-toolbar__inner">
        <a href="{{ route('admin.ads.index') }}" class="admin-toolbar__link">📦 آگهی‌ها</a>
        <a href="{{ route('admin.catalog.index', 'categories') }}" class="admin-toolbar__link">📂 دسته‌بندی‌ها</a>
        <a href="{{ route('admin.catalog.index', 'countries') }}" class="admin-toolbar__link">🌍 مکان‌ها</a>
        <a href="{{ route('admin.catalog.index', 'tariffs') }}" class="admin-toolbar__link">💰 تعرفه‌ها</a>
        <a href="{{ route('admin.catalog.index', 'forbidden-words') }}" class="admin-toolbar__link">🚫 لغات غیرمجاز</a>
        <a href="{{ route('admin.permits.index') }}" class="admin-toolbar__link">📋 مجوزها</a>
        <a href="{{ route('admin.reports.index') }}" class="admin-toolbar__link">⚠️ گزارش‌ها</a>
        <a href="{{ route('admin.tickets.index') }}" class="admin-toolbar__link">💬 تیکت‌ها</a>
        <a href="{{ route('admin.payments.index') }}" class="admin-toolbar__link">💳 پرداخت‌ها</a>
        @if($pendingReminders > 0)
            <a href="{{ route('admin.expiry-reminders.index') }}" class="admin-toolbar__link admin-toolbar__link--alert">⏰ یادآوری انقضا <span class="badge badge-warning">{{ number_format($pendingReminders) }}</span></a>
        @else
            <a href="{{ route('admin.expiry-reminders.index') }}" class="admin-toolbar__link">⏰ یادآوری انقضا</a>
        @endif
        <a href="{{ route('admin.settings.edit') }}" class="admin-toolbar__link">⚙️ تنظیمات</a>
        <form method="post" action="{{ route('admin.ads.ladder.refresh') }}" class="admin-toolbar__form">
            @csrf
            <button type="submit" class="admin-toolbar__link admin-toolbar__link--button" title="نردبان همه آگهی‌ها را به‌روزرسانی کن و سایت‌مپ را بازسازی کن">
                🔄 بروزرسانی نردبان
            </button>
        </form>
    </div>
</nav>

@php
    $statusEmojis = [
        'draft' => '📝',
        'pending_approval' => '⏳',
        'active' => '✅',
        'needs_permit' => '📋',
        'inactive' => '⏸️',
        'expired' => '⏰',
        'deleted' => '🗑️',
        'pending_payment' => '💳',
    ];
@endphp

<div class="stat-grid">
    @foreach($counts as $status=>$count)
        <a class="stat stat-link" href="{{ route('admin.ads.index', ['status' => $status]) }}">
            <span>{{ $statusEmojis[$status] ?? '📌' }} {{ \App\Domains\Ads\Enums\AdStatus::from($status)->label() }}</span>
            <strong>{{ number_format($count) }}</strong>
        </a>
    @endforeach
    <a class="stat stat-link" href="{{ route('admin.tickets.index') }}">
        <span>💬 تیکت باز</span>
        <strong>{{ number_format($openTickets) }}</strong>
    </a>
</div>

<style>
/* ───── Sticky admin toolbar ─────
   The toolbar sticks to the top of the viewport below the site header
   so the operator can jump between modules without scrolling back up.
*/
.admin-toolbar {
    position: sticky;
    top: 0;
    z-index: 50;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: .5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
    margin-bottom: 1.5rem;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.admin-toolbar__inner {
    display: flex;
    gap: .25rem;
    align-items: center;
    padding: .5rem;
    flex-wrap: nowrap;
    min-width: max-content;
}
.admin-toolbar__link {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .5rem .875rem;
    border-radius: .375rem;
    font-size: .85rem;
    font-weight: 600;
    color: #374151;
    text-decoration: none;
    white-space: nowrap;
    transition: background .15s, color .15s;
    cursor: pointer;
    background: transparent;
    border: 0;
}
.admin-toolbar__link:hover {
    background: #f0fdfa;
    color: #0d9488;
}
.admin-toolbar__link--alert {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fbbf24;
}
.admin-toolbar__link--button {
    background: #ecfdf5;
    color: #0f766e;
    border: 1px solid #99f6e4;
}
.admin-toolbar__link--button:hover {
    background: #ccfbf1;
}
.admin-toolbar__form {
    margin: 0;
    display: inline-flex;
}

/* Admin theme tweaks to keep the toolbar readable on the light bg. */
.theme-admin .admin-toolbar {
    background: #ffffff;
    border-color: #e5e7eb;
}

.stat-link {text-decoration:none;color:inherit;display:block;transition:transform .15s,box-shadow .15s}
.stat-link:hover {transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.stat-link-alert {background:#fef3c7;border:1px solid #fbbf24;border-radius:.5rem;padding:.5rem .75rem}
.badge {display:inline-block;padding:.1rem .4rem;border-radius:999px;font-size:.7rem;font-weight:600;margin-inline-start:.25rem;}
.badge-warning {background:#fef3c7;color:#92400e;}
</style>
@endsection
