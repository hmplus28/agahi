@extends('layouts.app', ['title' => 'آگهی‌های منقضی شده | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="section-heading">
    <div>
        <h1>آگهی‌های منقضی شده</h1>
        <p class="muted">آگهی‌های شما که تاریخ انقضای آن‌ها گذشته است. می‌توانید همه را با هم تمدید کنید.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" role="status">
        <span aria-hidden="true">✓</span>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if($ads->isNotEmpty())
    <div class="expired-summary">
        <div>
            <strong>{{ $ads->count() }}</strong> آگهی منقضی شده.
            @if($tariff)
                <span>تعرفه تمدید هر آگهی: <strong>{{ number_format($tariff->price) }} ریال</strong>.</span>
            @endif
        </div>
        <div>
            <strong>مجموع برای تمدید همه: {{ number_format($total) }} ریال</strong>
        </div>
    </div>

    <form method="post" action="{{ route('user.expired-ads.renewAll') }}">
        @csrf
        <input type="hidden" name="tariff_id" value="{{ $tariff?->id }}">

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" checked></th>
                        <th>عنوان</th>
                        <th>کد</th>
                        <th>شهر</th>
                        <th>تاریخ انقضا</th>
                        <th>قیمت تمدید</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ads as $ad)
                        <tr>
                            <td><input type="checkbox" name="ad_ids[]" value="{{ $ad->id }}" class="ad-checkbox" checked></td>
                            <td><a href="{{ $ad->publicUrl() }}" target="_blank">{{ $ad->title }}</a></td>
                            <td dir="ltr">{{ $ad->code }}</td>
                            <td>{{ $ad->city?->name ?? '—' }}</td>
                            <td>{{ jdate($ad->expires_at) }}</td>
                            <td>{{ number_format($tariff?->price ?? 0) }} ریال</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="renew-actions">
            <button type="submit" class="button">تمدید همهٔ آگهی‌های انتخابی</button>
            <a href="{{ route('user.payments.index') }}" class="link-button">بازگشت به پرداخت‌ها</a>
        </div>
    </form>
@else
    <div class="empty-state">
        <strong>آگهی منقضی شده‌ای ندارید.</strong>
        <span>همه آگهی‌های شما فعال هستند.</span>
        <a class="button" href="{{ route('user.dashboard') }}">بازگشت به داشبورد</a>
    </div>
@endif

<style>
.expired-summary {
    background: #fef3c7;
    border: 1px solid #fbbf24;
    border-radius: .5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: .5rem;
}
.expired-summary strong { color: #92400e; }
.renew-actions {
    display: flex;
    gap: .75rem;
    align-items: center;
    margin-top: 1.25rem;
    flex-wrap: wrap;
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    padding: 3rem 1rem;
    text-align: center;
}
.empty-state strong { font-size: 1.05rem; color: #374151; }
.empty-state span { color: #6b7280; font-size: .875rem; margin-bottom: .5rem; }
</style>

<script>
document.getElementById('select-all')?.addEventListener('change', function(){
    document.querySelectorAll('.ad-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
