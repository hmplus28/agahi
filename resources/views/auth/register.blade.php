@extends('layouts.app', ['title' => 'ثبت‌نام | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<section class="auth-card">
    <h1>ایجاد حساب کاربری</h1>

    <div class="auth-info-note" role="note">
        <strong>رمز عبور شما پیامک می‌شود.</strong>
        <span>برای راحتی شما، رمز عبور به‌طور خودکار توسط سامانه ساخته می‌شود و پس از ثبت‌نام به شمارهٔ موبایلتان پیامک خواهد شد. این رمز ثابت است و برای ورود‌های بعدی از همان استفاده کنید.</span>
    </div>

    <form method="post">
        @csrf
        <label>نام
            <input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name">
        </label>
        <label>نام خانوادگی
            <input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
        </label>
        <label>شمارهٔ موبایل
            <input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>
        <label>ایمیل (اختیاری)
            <input name="email" value="{{ old('email') }}" type="email" autocomplete="email">
        </label>
        <button class="button">ثبت‌نام و دریافت رمز پیامکی</button>
    </form>
    <p>حساب دارید؟ <a href="{{ route('login') }}">وارد شوید.</a></p>
    <p>رمز خود را فراموش کرده‌اید؟ <a href="{{ route('password.request') }}">دریافت مجدد رمز پیامک</a></p>
</section>

<style>
.auth-info-note{background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#0f766e;}
.auth-info-note strong{display:block;margin-bottom:.25rem;}
.auth-info-note span{display:block;line-height:1.5;}
</style>
@endsection
