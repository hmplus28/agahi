@extends('layouts.app', ['title' => 'تعرفه | ' . config('app.name')])

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
.sa-body{max-width:800px;margin:0 auto;padding:2rem 1rem}
.sa-img{text-align:center;margin:1.5rem 0}
.sa-img img{max-width:100%;border-radius:1rem;box-shadow:0 4px 20px rgba(0,0,0,.08)}
.sa-price-card{background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:2px solid #14b8a6;border-radius:1.5rem;padding:2rem;text-align:center;margin:1.5rem 0;animation:scaleIn .4s ease-out}
.sa-price-card .sa-amount{font-size:2.5rem;font-weight:900;color:#0d9488;line-height:1.2}
.sa-price-card .sa-unit{font-size:1rem;color:#6b7280;font-weight:500}
.sa-price-card .sa-desc{color:#374151;font-size:.9rem;margin:.75rem 0;line-height:1.8}
.sa-price-cta{display:inline-flex;align-items:center;gap:.5rem;background:#0d9488;color:#fff;padding:.75rem 2.5rem;border-radius:999px;font-weight:800;font-size:1rem;text-decoration:none;margin-top:1rem;transition:all .25s;box-shadow:0 4px 15px rgba(13,148,136,.3)}
.sa-price-cta:hover{background:#0f766e;transform:translateY(-2px);box-shadow:0 8px 25px rgba(13,148,136,.4)}
.sa-section{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.5rem;margin:1.5rem 0}
.sa-section h3{font-size:1rem;font-weight:700;color:#0d9488;margin-bottom:.75rem}
.sa-section ul{padding-right:1.2rem;margin:.5rem 0}
.sa-section li{font-size:.88rem;color:#4b5563;line-height:1.9;margin-bottom:.3rem}
.sa-contact{background:#f0fdfa;border:1px solid #ccfbf1;border-radius:1rem;padding:1.25rem;text-align:center;margin:1.5rem 0}
.sa-contact p{color:#374151;font-size:.9rem;line-height:1.8}
.sa-contact a{color:#0d9488;font-weight:600;text-decoration:none}
.sa-contact a[href^="tel:"]{direction:ltr;unicode-bidi:embed}
</style>

<div class="sa-header">
    <h1>تعرفه</h1>
    <p>تعرفه ثبت آگهی در سایت شتاب و ۵۰ سایت تبلیغاتی</p>
    <a class="sa-back" href="{{ route('site-ads') }}">← بازگشت به صفحه اصلی</a>
</div>

<div class="sa-body">
    <div class="sa-img">
        <img src="/site-ads/images/tarife1405.jpg" alt="تعرفه ۱۴۰۵" loading="lazy">
    </div>

    <div class="sa-price-card">
        <div class="sa-amount">۹۵۰,۰۰۰ <span class="sa-unit">تومان</span></div>
        <div class="sa-desc">ثبت یکساله آگهی شما در سایت شتاب + ثبت در ۵۰ سایت تبلیغاتی کشور<br>ثبت دستی + گزارش آنلاین نام کاربری و کلمه عبور + فاکتور</div>
        <a class="sa-price-cta" href="{{ route('site-ads') }}#order">ثبت سفارش</a>
    </div>

    <div class="sa-section">
        <h3>📦 پکیج ویژه شامل:</h3>
        <ul>
            <li>ثبت آگهی در سایت شتاب (۱ ساله)</li>
            <li>ثبت در ۵۰ سایت تبلیغاتی کشور</li>
            <li>ثبت دستی توسط کارشناس</li>
            <li>گزارش آنلاین نام کاربری و کلمه عبور</li>
            <li>فاکتور معتبر</li>
            <li>سئو رایگان آگهی ظرف ۷۲ ساعت</li>
        </ul>
    </div>

    <div class="sa-section">
        <h3>💰 نحوه پرداخت</h3>
        <ul>
            <li>پرداخت از درگاه بانکی معتبر بهپرداخت ملت</li>
            <li>کارت به کارت به شماره حساب ارائه شده در فاکتور</li>
            <li>امکان پرداخت اقساطی برای سفارشات بالای ۲ میلیون تومان</li>
        </ul>
    </div>

    <div class="sa-contact">
        <p>برای اطلاع از تعرفه دقیق با ما تماس بگیرید: <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴</a> یا <a href="https://wa.me/989193841239">واتس‌آپ</a></p>
    </div>

    <div style="text-align:center;margin-top:2rem">
        <a href="{{ route('site-ads') }}" style="display:inline-flex;align-items:center;gap:.3rem;background:#0d9488;color:#fff;padding:.6rem 1.5rem;border-radius:999px;font-weight:700;text-decoration:none;transition:all .2s">← بازگشت به صفحه اصلی ثبت آگهی</a>
    </div>
</div>
@endsection
