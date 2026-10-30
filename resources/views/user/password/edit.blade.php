@extends('layouts.app', ['title' => 'تغییر رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<x-auth-shell
    title="تغییر رمز عبور"
    subtitle="رمز جدید ساخته شده و به موبایل شما پیامک می‌شود."
    :features="[
        'رمز جدید به‌طور خودکار و امن ساخته می‌شود',
        'رمز قبلی بلافاصله غیرفعال می‌شود',
        'در سایر دستگاه‌ها از حساب خارج می‌شوید',
    ]"
    footnote="اگر به شمارهٔ موبایل حساب دسترسی ندارید، این کار را انجام ندهید.">

    <div class="auth-note" role="note">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.5 2.9.7a2 2 0 0 1 1.6 2Z"/></svg>
        <div>
            <strong>رمز جدید تولید و پیامک می‌شود.</strong>
            با تأیید این فرم، یک رمز جدید تصادفی برای شما ساخته می‌شود و به شمارهٔ موبایلتان پیامک خواهد شد. رمز قبلی بلافاصله غیرفعال می‌شود.
        </div>
    </div>

    <form method="post" action="{{ route('user.password.update') }}">
        @csrf
        @method('PUT')

        <label class="auth-check">
            <input type="checkbox" name="confirm" value="1" required>
            <span>می‌دانم رمز قبلی غیرفعال می‌شود و رمز جدید پیامک می‌شود؛ تأیید می‌کنم.</span>
        </label>
        @error('confirm')<p class="auth-error">{{ $message }}</p>@enderror

        <button class="button auth-submit" type="submit">تولید رمز جدید و ارسال پیامک</button>
    </form>

    <x-slot:links>
        <p><a href="{{ route('user.dashboard') }}">بازگشت به داشبورد کاربری</a></p>
    </x-slot:links>
</x-auth-shell>
@endsection
