@extends('layouts.app',['title'=>'مدیریت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<div class="section-heading"><div><h1>داشبورد مدیریت</h1><p class="muted">نمای کلی وضعیت تعدیل، داده‌های پایه، مالی و پشتیبانی.</p></div><a class="button" href="{{ route('admin.ads.index') }}">مدیریت آگهی‌ها</a></div>

<nav class="dashboard-links" aria-label="ماژول‌های مدیریت">
    <a href="{{ route('admin.catalog.index','categories') }}">📂 دسته‌بندی‌ها</a>
    <a href="{{ route('admin.catalog.index','countries') }}">🌍 مکان‌ها</a>
    <a href="{{ route('admin.catalog.index','tariffs') }}">💰 تعرفه‌ها</a>
    <a href="{{ route('admin.catalog.index','forbidden-words') }}">🚫 لغات غیرمجاز</a>
    <a href="{{ route('admin.permits.index') }}">📋 مجوزها</a>
    <a href="{{ route('admin.reports.index') }}">⚠️ گزارش‌ها</a>
    <a href="{{ route('admin.tickets.index') }}">💬 تیکت‌ها</a>
    <a href="{{ route('admin.payments.index') }}">💳 پرداخت‌ها</a>
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
.stat-link{text-decoration:none;color:inherit;display:block;transition:transform .15s,box-shadow .15s}
.stat-link:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
</style>
@endsection
