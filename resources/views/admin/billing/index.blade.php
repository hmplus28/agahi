@extends('layouts.app', ['title' => 'پرداخت‌های مدیریت | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
@include('admin._toolbar')
<div class="page-top">
    <div><h1>پرداخت‌ها</h1><p class="muted">به‌طور پیش‌فرض فقط پرداخت‌های موفق نمایش داده می‌شوند.</p></div>
    <a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<form class="filter-form" method="get" aria-label="فیلتر پرداخت‌ها">
    <label class="field-label">
        <span>وضعیت پرداخت</span>
        <select name="status" data-filter-select>
            @foreach($status_options as $value => $label)
                <option value="{{ $value }}" @selected($current_status === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <button class="button" type="submit">اعمال فیلتر</button>
    @if($current_status !== 'successful')
        <a class="link-button" href="{{ route('admin.payments.index') }}">بازگشت به موفق‌ها</a>
    @endif
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>کاربر</th>
                <th>آگهی</th>
                <th>فاکتور</th>
                <th>مبلغ (ریال)</th>
                <th>درگاه</th>
                <th>شناسه مرجع</th>
                <th>وضعیت</th>
                <th>تاریخ پرداخت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->user?->mobile ?? '—' }}</td>
                    <td>
                        @if($payment->ad)
                            <a href="{{ $payment->ad->publicUrl() }}" target="_blank" rel="noopener">{{ $payment->ad->title }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td dir="ltr">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                    <td>{{ number_format($payment->amount) }}</td>
                    <td>{{ $payment->gateway }}</td>
                    <td dir="ltr">{{ $payment->reference_id ?? '—' }}</td>
                    <td>
                        <span class="badge payment-status-{{ $payment->status }}">
                            @switch($payment->status)
                                @case('successful') موفق  @break
                                @case('failed')      ناموفق @break
                                @case('pending')     در انتظار @break
                                @default             {{ $payment->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>{{ $payment->paid_at ? jdate($payment->paid_at) : jdate($payment->created_at) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;">پرداختی با این فیلتر وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $payments->links() }}

<style>
.filter-form{display:flex;gap:.75rem;align-items:end;flex-wrap:wrap;margin-bottom:1.5rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:.5rem;padding:1rem;}
.filter-form .field-label span{display:block;font-size:.75rem;color:#374151;margin-bottom:.25rem;font-weight:600;}
.filter-form select{padding:.4rem .6rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.85rem;}
.payment-status-successful{background:#d1fae5;color:#065f46;}
.payment-status-failed{background:#fee2e2;color:#991b1b;}
.payment-status-pending{background:#fef3c7;color:#92400e;}
</style>

<script>
document.querySelector('[data-filter-select]')?.addEventListener('change', function(){
    this.form.submit();
});
</script>
@endsection
