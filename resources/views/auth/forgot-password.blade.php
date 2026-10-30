@extends('layouts.app', ['title' => 'بازیابی رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<x-auth-shell
    title="بازیابی رمز عبور"
    subtitle="رمز عبور با پیامک برای شما ارسال می‌شود."
    :features="[
        'ارسال فوری رمز با پیامک',
        'امکان ساخت رمز جدید در صورت نیاز',
        'بدون نیاز به تماس با پشتیبانی',
    ]"
    footnote="پیامک‌های سامانه ممکن است چند دقیقه طول بکشد؛ پوشهٔ پیام‌های ناشناس را هم بررسی کنید.">

    <form method="post">
        @csrf
        <div class="auth-field">
            <label for="mobile">شمارهٔ موبایل</label>
            <input id="mobile" name="mobile" value="{{ old('mobile') }}" inputmode="numeric" dir="ltr" placeholder="09123456789" required autocomplete="tel">
            @error('mobile')<p class="auth-error">{{ $message }}</p>@enderror
        </div>

        <fieldset class="radio-cards">
            <legend>چه درخواستی دارید؟</legend>
            <label class="radio-card">
                <input type="radio" name="action" value="remind" checked>
                <strong>یادآوری رمز فعلی</strong>
                <span>رمز فعلی حساب شما دوباره پیامک می‌شود و تغییری نمی‌کند.</span>
            </label>
            <label class="radio-card">
                <input type="radio" name="action" value="rotate">
                <strong>ساخت رمز جدید</strong>
                <span>رمز قبلی غیرفعال می‌شود و رمز جدیدی برایتان پیامک می‌گردد.</span>
            </label>
        </fieldset>

        <button class="button auth-submit" type="submit">ارسال درخواست</button>
    </form>

    <x-slot:links>
        <p>رمز عبورتان را به یاد آوردید؟ <a href="{{ route('login') }}">وارد شوید</a></p>
        <p>حساب کاربری ندارید؟ <a href="{{ route('register') }}">ثبت‌نام کنید</a></p>
    </x-slot:links>
</x-auth-shell>
@endsection
