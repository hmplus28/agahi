@extends('layouts.app',['title'=>'بازیابی رمز عبور | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<section class="auth-card">
    <h1>بازیابی رمز عبور</h1>
    <p class="auth-lead">شماره موبایل خود را وارد کنید. رمز عبور جدیدی برای شما پیامک خواهد شد.</p>
    <form method="post">
        @csrf
        <label>شمارهٔ موبایل
            <input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>
        <button class="button">ارسال رمز عبور جدید</button>
    </form>
    <p>رمز عبور دارید؟ <a href="{{ route('login') }}">وارد شوید.</a></p>
</section>
@endsection
