@extends('layouts.app', ['title' => 'پرداخت‌ها و سرویس‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="section-heading">
    <div><h1>پرداخت‌ها و سرویس‌ها</h1><p class="muted">تعرفهٔ موردنظر را برای آگهی خود انتخاب کنید.</p></div>
    <a href="{{ route('user.dashboard') }}">بازگشت به پنل</a>
</div>

<section class="panel">
    <h2>خرید یا تمدید سرویس</h2>
    <form method="post" action="{{ route('user.payments.purchase') }}" class="form-grid">
        @csrf
        <label>آگهی
            <select name="ad_id" required>
                <option value="">انتخاب آگهی</option>
                @foreach($ads as $ad)
                    <option value="{{ $ad->id }}">{{ $ad->title }} — {{ $ad->status->label() }}</option>
                @endforeach
            </select>
        </label>
        <label>تعرفه
            <select name="tariff_id" required>
                <option value="">انتخاب تعرفه</option>
                @foreach($tariffs as $tariff)
                    <option value="{{ $tariff->id }}">{{ $tariff->title }} — {{ number_format($tariff->price) }} ریال</option>
                @endforeach
            </select>
        </label>
        <fieldset class="pay-methods">
            <legend>روش پرداخت</legend>
            <label class="check-label"><input type="radio" name="method" value="online" checked> آنلاین (درگاه پرداخت)</label>
            <label class="check-label"><input type="radio" name="method" value="card"> کارت به کارت (تأیید دستی مدیر)</label>
        </fieldset>
        <div><button class="button">ادامهٔ پرداخت</button></div>
    </form>
</section>

@if(isset($payments) && $payments->isNotEmpty())
<section>
    <h2>پرداخت‌های موفق</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>آگهی</th>
                    <th>مبلغ (ریال)</th>
                    <th>روش</th>
                    <th>تاریخ پرداخت</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td dir="ltr">{{ $payment->authority }}</td>
                        <td>{{ $payment->ad?->title ?? '—' }}</td>
                        <td>{{ number_format($payment->amount) }}</td>
                        <td>{{ $payment->methodLabel() }}</td>
                        <td>{{ jdate($payment->paid_at ?? $payment->created_at) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</section>
@endif

<section>
    <h2>فاکتورهای من</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>شماره</th><th>آگهی</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td dir="ltr"><a href="{{ route('user.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                        <td>{{ $invoice->ad?->title ?? '—' }}</td>
                        <td>{{ number_format($invoice->total) }} ریال</td>
                        <td>{{ $invoice->statusLabel() }}</td>
                        <td>{{ jdate($invoice->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">فاکتوری وجود ندارد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $invoices->links() }}
</section>

<style>
.pay-methods{border:1px solid #e5e7eb;border-radius:.5rem;padding:.75rem 1rem;display:flex;gap:1.25rem;flex-wrap:wrap}
.pay-methods legend{font-size:.8rem;color:#374151;font-weight:600;padding:0 .35rem}
.pay-methods .check-label{display:flex;align-items:center;gap:.4rem;font-size:.85rem}
</style>
@endsection
