@extends('layouts.app',['title'=>'پرداخت کارت به کارت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<div class="section-heading"><div><h1>پرداخت کارت به کارت</h1><p class="muted">لطفاً مبلغ فاکتور را به شماره کارت زیر واریز کنید.</p></div><a class="button button-outline back-button" href="{{ route('user.payments.index }}">بازگشت به پرداخت‌ها</a></div>
<section class="panel">
    <div class="form-grid">
        <div class="wide">
            <h2>شماره کارت</h2>
            <p class="card-to-card-number" dir="ltr" style="font-size:1.4rem;font-weight:bold;font-family:monospace;margin:12px 0">{{ $cardNumber }}</p>
            <p>به نام: <strong>{{ $cardholder }}</strong></p>
        </div>
        <div class="wide">
            <h2>اطلاعات فاکتور</h2>
            <table>
                <thead><tr><th>شرح</th><th>مبلغ (ریال)</th></tr></thead>
                <tbody>
                @foreach($invoice->items as $item)
                    <tr><td>{{ $item->title }}</td><td>{{ number_format($item->total_price) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><th>جمع کل</th><th>{{ number_format($invoice->total) }} ریال</th></tr></tfoot>
            </table>
        </div>
        <div class="wide">
            <h3>تأیید واریز</h3>
            <form method="post" action="{{ route('user.payments.cardToCard.confirm', ['invoice'=>$invoice->id]) }}" class="form-grid">
                @csrf
                <label>شناسه واریز (اختیاری)
                    <input name="ref_id" maxlength="100" placeholder="شماره پیگیری">
                </label>
                <div><button class="button">تأیید واریز</button></div>
            </form>
        </div>
    </div>
</section>
@endsection