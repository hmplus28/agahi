@extends('layouts.app', ['title' => 'تنظیمات سامانه | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
@include('admin._toolbar')
<div class="page-top">
    <div><h1>تنظیمات سامانه</h1><p class="muted">قیمت‌های جانبی، برندینگ و اطلاعات تماس.</p></div>
    <a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<form method="post" action="{{ route('admin.settings.update') }}" class="settings-form">
    @csrf
    @method('PUT')

    @if(session('success'))
        <div class="alert alert-success" role="status">
            <span aria-hidden="true">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <fieldset class="settings-section">
        <legend>قیمت‌گذاری آگهی</legend>

        <label class="field-label">
            <span>قیمت هر لینک اضافه (تومان)</span>
            <input type="number" name="price_per_link" min="0" value="{{ $values['price_per_link'] }}" required>
            <span class="field-hint">برای هر لینک پر شده در آگهی، این مبلغ به قیمت نهایی اضافه می‌شود. پیش‌فرض: ۱۰ تومان.</span>
        </label>

        <label class="field-label">
            <span>قیمت هر تصویر اضافه (تومان)</span>
            <input type="number" name="price_per_extra_image" min="0" value="{{ $values['price_per_extra_image'] }}" required>
            <span class="field-hint">برای هر تصویر فراتر از تصویر اول، این مبلغ اضافه می‌شود. پیش‌فرض: ۲۰ تومان.</span>
        </label>

        <label class="check-label">
            <input type="checkbox" name="apply_pricing_to_display" value="1" @checked($values['apply_pricing_to_display'])>
            <span>اعمال قیمت‌گذاری روی قیمت نمایشی آگهی (در کارت آگهی و صفحه آگهی هم دیده شود)</span>
        </label>
    </fieldset>

    <fieldset class="settings-section">
        <legend>برندینگ و تماس</legend>

        <label class="field-label">
            <span>نام برند</span>
            <input name="brand_name" value="{{ $values['brand_name'] }}" required>
        </label>

        <label class="field-label">
            <span>شماره تلفن پشتیبانی (نمایشی، با خط تیره)</span>
            <input name="support_phone" value="{{ $values['support_phone'] }}" required>
            <span class="field-hint">مثال: 021-00000000</span>
        </label>

        <label class="field-label">
            <span>شماره تلفن پشتیبانی (تماس، بدون خط تیره)</span>
            <input name="support_phone_href" value="{{ $values['support_phone_href'] }}" required>
            <span class="field-hint">مثال: 02100000000</span>
        </label>

        <label class="field-label">
            <span>توضیحات کوتاه سامانه (SEO meta description)</span>
            <input name="site_description" value="{{ $values['site_description'] }}" required>
        </label>
    </fieldset>

    <button class="button" type="submit">ذخیره تنظیمات</button>
</form>

<style>
.settings-form{max-width:720px;display:flex;flex-direction:column;gap:1.5rem;}
.settings-section{border:1px solid #e5e7eb;border-radius:.625rem;padding:1rem 1.25rem 1.5rem;margin:0;}
.settings-section legend{padding:0 .5rem;font-weight:700;color:#0d9488;}
.settings-section .field-label{display:block;margin-top:1rem;}
.settings-section .field-label span{display:block;font-size:.8rem;color:#374151;margin-bottom:.25rem;font-weight:600;}
.settings-section .field-label input,
.settings-section .field-label select,
.settings-section .field-label textarea{width:100%;padding:.5rem .7rem;border:1px solid #d1d5db;border-radius:.375rem;font-size:.9rem;}
.field-hint{display:block;font-size:.7rem;color:#9ca3af;margin-top:.25rem;}

</style>
@endsection
