@extends('layouts.app',['title'=>'ورود | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<x-auth-shell
    title="ورود به حساب کاربری"
    subtitle="شمارهٔ موبایل و گذرواژهٔ خود را وارد کنید."
    :features="[
        'ورود سریع فقط با شمارهٔ موبایل',
        'مدیریت آگهی‌ها، پرداخت‌ها و تیکت‌های پشتیبانی',
        'اطلاعات حساب شما با گذرواژه محافظت می‌شود',
    ]"
    footnote="گذرواژهٔ شما پس از ثبت‌نام با پیامک برایتان ارسال شده است.">

    <form method="post">
        @csrf
        <div class="auth-field">
            <label for="mobile">شمارهٔ موبایل</label>
            <input id="mobile" name="mobile" value="{{ old('mobile') }}" inputmode="numeric" dir="ltr" placeholder="09123456789" required autocomplete="tel">
            @error('mobile')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-field">
            <label for="password">گذرواژه</label>
            <div class="pass-wrap">
                <input id="password" name="password" type="password" required autocomplete="current-password">
                <button type="button" class="pass-toggle" data-pass-toggle="password" aria-pressed="false">نمایش</button>
            </div>
            @error('password')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <label class="auth-check">
            <input type="checkbox" name="remember" value="1">
            <span>مرا به خاطر بسپار</span>
        </label>
        <button class="button auth-submit" type="submit">ورود به حساب</button>
    </form>

    <x-slot:links>
        <p>حساب کاربری ندارید؟ <a href="{{ route('register') }}">ثبت‌نام کنید</a></p>
        <p>رمز عبورتان را فراموش کرده‌اید؟ <a href="{{ route('password.request') }}">بازیابی رمز با پیامک</a></p>
    </x-slot:links>
</x-auth-shell>

<script>
document.querySelectorAll('[data-pass-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.getAttribute('data-pass-toggle'));
        if (!input) return;
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        btn.textContent = showing ? 'نمایش' : 'پنهان';
        btn.setAttribute('aria-pressed', showing ? 'false' : 'true');
    });
});
</script>
@endsection
