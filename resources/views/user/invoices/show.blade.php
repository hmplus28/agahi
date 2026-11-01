@extends('layouts.app', ['title' => 'فاکتور '.$invoice->invoice_number.' | '.config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="section-heading">
    <div><h1>فاکتور <span dir="ltr">{{ $invoice->invoice_number }}</span></h1><p class="muted">{{ $invoice->statusLabel() }}</p></div>
    <div class="invoice-actions">
        <button type="button" class="button button-outline" onclick="window.print()">چاپ فاکتور</button>
        <a href="{{ route('user.payments.index') }}">بازگشت به پرداخت‌ها</a>
    </div>
</div>

<section class="panel invoice-sheet">
    <div class="invoice-parties">
        <div>
            <h2>فروشنده</h2>
            <p><strong>{{ $sellerName }}</strong></p>
            @if($sellerPhone)<p class="muted">تلفن: <span dir="ltr">{{ to_persian_digits($sellerPhone) }}</span></p>@endif
            <p class="muted">{{ url('/') }}</p>
        </div>
        <div>
            <h2>خریدار</h2>
            <p><strong>{{ $invoice->user?->name ?? $invoice->user?->mobile ?? '—' }}</strong></p>
            @if($invoice->user?->mobile)<p class="muted">موبایل: <span dir="ltr">{{ to_persian_digits($invoice->user->mobile) }}</span></p>@endif
        </div>
        <div>
            <h2>اطلاعات فاکتور</h2>
            <p>تاریخ صدور: {{ to_persian_digits(jdate($invoice->created_at)) }}</p>
            @if($invoice->paid_at)<p>تاریخ پرداخت: {{ to_persian_digits(jdate($invoice->paid_at)) }}</p>@endif
            @if($invoice->ad)
                <p>آگهی مرتبط: <a href="{{ $invoice->ad->publicUrl() }}">{{ $invoice->ad->title }}</a></p>
            @endif
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>خدمت</th><th>تعداد</th><th>قیمت واحد (ریال)</th><th>جمع (ریال)</th></tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ to_persian_digits((string) $loop->iteration) }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ to_persian_digits((string) $item->quantity) }}</td>
                        <td>{{ to_persian_digits(number_format($item->unit_price)) }}</td>
                        <td>{{ to_persian_digits(number_format($item->total_price)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">ردیفی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr><th colspan="4">جمع کل</th><th>{{ to_persian_digits(number_format($invoice->subtotal)) }}</th></tr>
                @if($invoice->discount > 0)
                    <tr><th colspan="4">تخفیف</th><th>{{ to_persian_digits(number_format($invoice->discount)) }}</th></tr>
                @endif
                <tr class="invoice-total"><th colspan="4">مبلغ قابل پرداخت</th><th>{{ to_persian_digits(number_format($invoice->total)) }} ریال</th></tr>
            </tfoot>
        </table>
    </div>

    <div class="invoice-payments">
        <h2>وضعیت پرداخت</h2>
        @forelse($invoice->payments as $payment)
            <p>
                پرداخت با روش «{{ $payment->methodLabel() }}» — {{ $payment->statusLabel() }}
                @if($payment->reference_id) — شناسه پیگیری: <span dir="ltr">{{ $payment->reference_id }}</span>@endif
                @if($payment->admin_note) — توضیح مدیر: {{ $payment->admin_note }}@endif
            </p>
        @empty
            <p class="muted">پرداختی برای این فاکتور ثبت نشده است.</p>
        @endforelse
        @if($invoice->status !== 'paid' && $cardNumber !== '')
            <p class="invoice-card">برای پرداخت کارت‌به‌کارت: <b dir="ltr">{{ $cardNumber }}</b>@if($cardHolder) به نام {{ $cardHolder }}@endif</p>
        @endif
    </div>
</section>

<style>
.invoice-actions{display:flex;gap:.75rem;align-items:center}
.invoice-parties{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem;margin-bottom:1.5rem}
.invoice-parties h2{font-size:.9rem;color:#4b4dbe;margin-bottom:.35rem}
.invoice-parties p{font-size:.85rem;line-height:1.8}
.invoice-sheet tfoot th{text-align:left;font-size:.85rem}
.invoice-total th{color:#4b4dbe;font-size:1rem}
.invoice-payments{margin-top:1.5rem}
.invoice-payments h2{font-size:.95rem;margin-bottom:.5rem}
.invoice-payments p{font-size:.85rem;color:#374151;line-height:1.9}
.invoice-card{background:#f0f1fa;border:1px solid #c6c7ee;border-radius:.5rem;padding:.6rem .9rem;margin-top:.5rem}
@media print{
    header,footer,.breadcrumb,.section-heading .button,.site-footer{display:none!important}
    body{background:#fff}
    .panel{border:none}
}
</style>
@endsection
