@extends('layouts.app',['title'=>'نهایی‌سازی سفارش | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<div class="section-heading"><div><h1>نهایی‌سازی سفارش</h1><p class="muted">خدمات موردنظر خود را برای آگهی «{{ $ad->title ?? '—' }}» انتخاب کنید.</p></div><a class="button button-outline back-button" href="{{ route('user.dashboard') }}">بازگشت به پنل</a></div>
@error('ad_id')<div class="alert alert-error">{{ $message }}</div>@enderror
@if(isset($selectedIds) && $selectedIds->isEmpty() && request()->has('services'))
    <div class="alert alert-warning">خدمتی انتخاب نشده است. حداقل یک سرویس برگزینید.</div>
@endif
<section class="panel">
    <form method="post" action="{{ route('user.payments.checkout.store') }}" class="form-grid">
        @csrf
        <input type="hidden" name="ad_id" value="{{ $ad?->id }}">
        <div class="wide">
            <h2>انتخاب خدمات (بسته آگهی)</h2>
            <div class="choice-box">
            @foreach($allTariffs as $tariff)
                <label class="check service-option">
                    <input type="checkbox" name="services[]" value="{{ $tariff->id }}"
                        @checked(request()->has('services') ? in_array($tariff->id, $selectedIds->all()) : $preexisting->contains($tariff->id))>
                    <span>
                        <strong>{{ $tariff->title }}</strong>
                        <span class="muted">{{ $tariff->description }}</span>
                        <span class="service-price">{{ $tariff->price === 0 ? 'رایگان' : number_format($tariff->price).' ریال' }}</span>
                    </span>
                </label>
            @endforeach
            </div>
        </div>
        <div class="wide form-section-title">انتخاب روش پرداخت</div>
        <div class="choice-box wide">
            <label class="check payment-option"><input type="radio" name="payment_type" value="online" checked> <span>پرداخت آنلاین (درگاه اینترنتی)</span></label>
            <label class="check payment-option"><input type="radio" name="payment_type" value="card_to_card"> <span>کارت به کارت</span></label>
            <label class="check payment-option"><input type="radio" name="payment_type" value="later"> <span>پرداخت بعداً (آگهی تا پرداخت کامل منتشر نمی‌شود)</span></label>
        </div>
        <div class="wide"><button class="button">ادامه و پرداخت</button></div>
    </form>
</section>
@endsection