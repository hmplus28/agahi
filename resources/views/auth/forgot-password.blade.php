@extends('layouts.app', ['title' => 'بازیابی رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<section class="auth-card">
    <h1>بازیابی رمز عبور</h1>

    <div class="auth-info-note" role="note">
        <strong>رمز عبور ثابت شما دوباره پیامک می‌شود.</strong>
        <span>رمز عبور سامانه ثابت است و در زمان ثبت‌نام به شما پیامک شده است. در صورت فراموشی، همان رمز دوباره برایتان پیامک می‌شود و نیازی به تغییر آن نیست.</span>
    </div>

    <form method="post">
        @csrf
        <label>شمارهٔ موبایل
            <input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>
        <button class="button">دریافت رمز پیامک</button>
    </form>
    <p>رمز عبور دارید؟ <a href="{{ route('login') }}">وارد شوید.</a></p>
</section>

<style>
.auth-info-note{background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#0f766e;}
.auth-info-note strong{display:block;margin-bottom:.25rem;}
.auth-info-note span{display:block;line-height:1.5;}
</style>
@endsection
