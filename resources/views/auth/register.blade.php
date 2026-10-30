@extends('layouts.app', ['title' => 'ثبت‌نام | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<x-auth-shell
    title="ایجاد حساب کاربری"
    subtitle="در چند ثانیه ثبت‌نام کنید؛ گذرواژه برایتان پیامک می‌شود."
    :features="[
        'ثبت آگهی رایگان در چند دقیقه',
        'مدیریت همهٔ آگهی‌ها از یک داشبورد',
        'اطلاع از انقضا و وضعیت آگهی با پیامک',
    ]"
    footnote="با ثبت‌نام، قوانین و مقررات سایت را می‌پذیرید.">

    <div class="auth-note" role="note">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.5 2.9.7a2 2 0 0 1 1.6 2Z"/></svg>
        <div>
            <strong>گذرواژهٔ شما پیامک می‌شود.</strong>
            گذرواژه به‌طور خودکار ساخته می‌شود و پس از ثبت‌نام به شمارهٔ موبایلتان پیامک خواهد شد. این گذرواژه ثابت است و برای ورودهای بعدی از همان استفاده کنید.
        </div>
    </div>

    <form method="post">
        @csrf
        <div class="auth-row">
            <div class="auth-field">
                <label for="first_name">نام</label>
                <input id="first_name" name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
            </div>
            <div class="auth-field">
                <label for="last_name">نام خانوادگی</label>
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
            </div>
        </div>
        <div class="auth-field">
            <label for="mobile">شمارهٔ موبایل</label>
            <input id="mobile" name="mobile" value="{{ old('mobile') }}" inputmode="numeric" dir="ltr" placeholder="09123456789" required autocomplete="tel">
            @error('mobile')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-field">
            <label for="email">ایمیل <span class="auth-optional">(اختیاری)</span></label>
            <input id="email" name="email" value="{{ old('email') }}" type="email" dir="ltr" autocomplete="email">
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <button class="button auth-submit" type="submit">ثبت‌نام و دریافت گذرواژهٔ پیامکی</button>
    </form>

    <x-slot:links>
        <p>حساب کاربری دارید؟ <a href="{{ route('login') }}">وارد شوید</a></p>
        <p>رمزتان را فراموش کرده‌اید؟ <a href="{{ route('password.request') }}">دریافت مجدد رمز با پیامک</a></p>
    </x-slot:links>
</x-auth-shell>
@endsection
