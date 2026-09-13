@extends('layouts.app', ['title' => 'مجوزها | ' . config('app.name')])

@php
    $foundedYear = 1390;
    $currentJalaliYear = jalali_year();
    $yearsOfExperience = $currentJalaliYear - $foundedYear;
@endphp

@section('content')
<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes scaleIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
.sa-header{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;padding:3rem 1.5rem 2rem;text-align:center;animation:fadeUp .5s ease-out}
.sa-header h1{font-size:1.8rem;font-weight:900;margin-bottom:.5rem}
.sa-header p{opacity:.85;font-size:.95rem}
.sa-header .sa-back{display:inline-flex;align-items:center;gap:.3rem;color:#fff;text-decoration:none;margin-top:1rem;background:rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:999px;font-size:.85rem;transition:all .2s}
.sa-header .sa-back:hover{background:rgba(255,255,255,.25)}
.sa-body{max-width:900px;margin:0 auto;padding:2rem 1rem}
.sa-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem;margin:1.5rem 0}
.sa-card{background:#fff;border:2px solid #f3f4f6;border-radius:1rem;padding:1.5rem;text-align:center;transition:all .3s;animation:scaleIn .4s ease-out both}
.sa-card:nth-child(2){animation-delay:.1s}
.sa-card:nth-child(3){animation-delay:.2s}
.sa-card:nth-child(4){animation-delay:.3s}
.sa-card:hover{border-color:#14b8a6;transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.sa-card img{max-width:100%;max-height:200px;border-radius:.75rem;margin-bottom:1rem;box-shadow:0 2px 8px rgba(0,0,0,.1)}
.sa-card h3{font-size:1rem;font-weight:700;color:#111827;margin-bottom:.5rem}
.sa-card p{font-size:.85rem;color:#6b7280;line-height:1.7}
.sa-card a{display:inline-flex;align-items:center;gap:.3rem;color:#0d9488;font-weight:600;font-size:.85rem;text-decoration:none;margin-top:.5rem;transition:color .2s}
.sa-card a:hover{color:#0f766e}
.sa-note{background:#f0fdfa;border:1px solid #ccfbf1;border-radius:1rem;padding:1.25rem;margin:1.5rem 0;text-align:center}
.sa-note p{color:#374151;font-size:.9rem;line-height:1.8}
@media(max-width:640px){.sa-grid{grid-template-columns:1fr}}
</style>

<div class="sa-header">
    <h1>مجوزها</h1>
    <p>مجوزها و نمادهای اعتماد شبکه شتاب</p>
    <a class="sa-back" href="{{ route('site-ads') }}">← بازگشت به صفحه اصلی</a>
</div>

<div class="sa-body">
    <div class="sa-grid">
        <div class="sa-card">
            <img src="/site-ads/images/a01.jpg" alt="نماد اعتماد" loading="lazy">
            <h3>نماد اعتماد الکترونیک</h3>
            <p>تائید شده از وزارت صنعت معدن و تجارت</p>
            <a href="https://Trustseal.eNamad.ir/logo.aspx?id=253106&Code=4NibPmVewfykiZBv3Fpp" target="_blank" rel="noopener">مشاهده در سایت ↗</a>
        </div>
        <div class="sa-card">
            <img src="/site-ads/images/logo-shetabe.svg" alt="شتاب آگهی" loading="lazy">
            <h3>لوگوی شتاب</h3>
            <p>نماینده رسمی تبلیغات انبوه</p>
            <a href="https://shetabe.ir/" target="_blank" rel="noopener">مشاهده سایت ↗</a>
        </div>
        <div class="sa-card">
            <img src="/site-ads/images/logo-mojavez.svg" alt="مجوزها" loading="lazy">
            <h3>پروانه کسب</h3>
            <p>اتحادیه کسب و کارهای مجازی</p>
            <a href="{{ route('site-ads') }}">بازگشت ↩</a>
        </div>
        <div class="sa-card">
            <img src="/site-ads/images/javaz-low.jpg" alt="جواز کسب" loading="lazy">
            <h3>جواز کسب</h3>
            <p>جواز رسمی از اتحادیه</p>
            <a href="{{ route('site-ads') }}">بازگشت ↩</a>
        </div>
    </div>

    <div class="sa-grid">
        <div class="sa-card">
            <img src="/site-ads/images/govahi-low.jpg" alt="گواهی" loading="lazy">
            <h3>گواهی ثبت</h3>
            <p>ثبت شده در اداره ثبت شرکت‌ها</p>
            <a href="{{ route('site-ads') }}">بازگشت ↩</a>
        </div>
        <div class="sa-card">
            <img src="/site-ads/images/saramad.svg" alt="ساماد" loading="lazy">
            <h3>ساماد</h3>
            <p>ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی</p>
            <a href="/site-ads/images/saramad.svg" target="_blank" rel="noopener">مشاهده در سایت ↗</a>
        </div>
    </div>

    <div class="sa-note">
        <p>شتاب آگهی با بیش از {{ to_persian_digits($yearsOfExperience) }} سال سابقه، مجموعه‌ای ثبت‌شده در اداره ثبت شرکت‌ها و مؤسسات می‌باشد و دارای نماد اعتماد الکترونیک از وزارت صنعت معدن و تجارت است. همچنین در ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی ثبت شده و پروانه کسب از اتحادیه کسب و کارهای مجازی دارد.</p>
    </div>

    <div style="text-align:center;margin-top:2rem">
        <a href="{{ route('site-ads') }}" style="display:inline-flex;align-items:center;gap:.3rem;background:#0d9488;color:#fff;padding:.6rem 1.5rem;border-radius:999px;font-weight:700;text-decoration:none;transition:all .2s">← بازگشت به صفحه اصلی ثبت آگهی</a>
    </div>
</div>
@endsection
