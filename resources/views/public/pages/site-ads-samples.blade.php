@extends('layouts.app', ['title' => 'نمونه کار | ' . config('app.name')])

@php $foundedYear = 1390; $currentJalaliYear = jalali_year(); $yearsOfExperience = $currentJalaliYear - $foundedYear; @endphp

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
.sa-img{text-align:center;margin:1.5rem 0}
.sa-img img{max-width:100%;border-radius:1rem;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.sa-note{background:#f0fdfa;border:1px solid #ccfbf1;border-radius:1rem;padding:1.25rem;text-align:center;margin:1.5rem 0}
.sa-note p{color:#374151;font-size:.9rem;line-height:1.8}
.sa-contact{background:#f0fdfa;border:1px solid #ccfbf1;border-radius:1rem;padding:1.25rem;text-align:center;margin:1.5rem 0}
.sa-contact p{color:#374151;font-size:.9rem;line-height:1.8}
.sa-contact a{color:#0d9488;font-weight:600;text-decoration:none}
.sa-contact a[href^="tel:"]{direction:ltr;unicode-bidi:embed}
</style>

<div class="sa-header">
    <h1>نمونه کار</h1>
    <p>بیش از ۹۰ هزار نمونه کار و آگهی ثبت شده</p>
    <a class="sa-back" href="{{ route('site-ads') }}">← بازگشت به صفحه اصلی</a>
</div>

<div class="sa-body">
    <div class="sa-img">
        <img src="/site-ads/images/report.jpg" alt="نمونه گزارش ثبت آگهی" loading="lazy">
    </div>

    <div class="sa-note">
        <p>بیش از ۹۰,۰۰۰ آگهی ثبت شده در سایت شتاب، گواهی بر اعتماد مشتریان به خدمات ما می‌باشد. هر آگهی با دقت و کیفیت بالا در سایت‌های تبلیغاتی کشور ثبت می‌گردد.</p>
    </div>

    <div class="sa-img">
        <img src="/site-ads/images/list-sitha.jpg" alt="لیست سایت‌های تبلیغاتی" loading="lazy">
    </div>

    <div class="sa-contact">
        <p>برای مشاهده نمونه آگهی‌های بیشتر با ما تماس بگیرید: <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴</a> یا <a href="https://wa.me/989193841239">واتس‌آپ</a></p>
    </div>

    <div style="text-align:center;margin-top:2rem">
        <a href="{{ route('site-ads') }}" style="display:inline-flex;align-items:center;gap:.3rem;background:#0d9488;color:#fff;padding:.6rem 1.5rem;border-radius:999px;font-weight:700;text-decoration:none;transition:all .2s">← بازگشت به صفحه اصلی ثبت آگهی</a>
    </div>
</div>
@endsection
