@extends('layouts.app', ['title' => 'تغییر رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<section class="form-page">
    <h1>تغییر رمز عبور</h1>

    <div class="auth-info-note" role="note">
        <strong>رمز جدید تولید و پیامک می‌شود.</strong>
        <span>با تأیید این فرم، یک رمز جدید تصادفی برای شما ساخته می‌شود و به شمارهٔ موبایلتان پیامک خواهد شد. رمز قبلی بلافاصله غیرفعال می‌شود و در سایر دستگاه‌ها از حساب خارج می‌شوید.</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">
            <span aria-hidden="true">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" role="alert">
            <span aria-hidden="true">!</span>
            <div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="post" action="{{ route('user.password.update') }}">
        @csrf
        @method('PUT')

        <label class="check-label">
            <input type="checkbox" name="confirm" value="1" required>
            <span>می‌دانم که رمز قبلی غیرفعال می‌شود و رمز جدید پیامک می‌شود. تأیید می‌کنم.</span>
        </label>

        <button class="button" type="submit">تولید رمز جدید و ارسال پیامک</button>
    </form>

    <p><a href="{{ route('user.profile.edit') }}">بازگشت به پروفایل</a></p>
</section>

<style>
.auth-info-note{background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#0f766e;}
.auth-info-note strong{display:block;margin-bottom:.25rem;}
.auth-info-note span{display:block;line-height:1.5;}
.check-label{display:flex;gap:.5rem;align-items:flex-start;margin-bottom:1rem;font-size:.875rem;color:#374151;}
.check-label input{margin-top:.2rem;}
.check-label span{flex:1;}
</style>
@endsection
