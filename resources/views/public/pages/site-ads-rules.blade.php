@extends('layouts.app', ['title' => 'قوانین درج آگهی | ' . config('app.name')])

@php $foundedYear = 1390; $currentJalaliYear = jalali_year(); $yearsOfExperience = $currentJalaliYear - $foundedYear; @endphp

@section('content')
<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.sa-header{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;padding:3rem 1.5rem 2rem;text-align:center;animation:fadeUp .5s ease-out}
.sa-header h1{font-size:1.8rem;font-weight:900;margin-bottom:.5rem}
.sa-header p{opacity:.85;font-size:.95rem}
.sa-header .sa-back{display:inline-flex;align-items:center;gap:.3rem;color:#fff;text-decoration:none;margin-top:1rem;background:rgba(255,255,255,.15);padding:.4rem 1rem;border-radius:999px;font-size:.85rem;transition:all .2s}
.sa-header .sa-back:hover{background:rgba(255,255,255,.25)}
.sa-body{max-width:800px;margin:0 auto;padding:2rem 1rem}
.sa-rule{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:1rem;transition:all .3s}
.sa-rule:hover{border-color:#14b8a6;box-shadow:0 4px 12px rgba(0,0,0,.05)}
.sa-rule h3{font-size:.95rem;font-weight:700;color:#0d9488;margin-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
.sa-rule p{font-size:.88rem;color:#4b5563;line-height:1.9}
.sa-rule ul{padding-right:1.2rem;margin:.5rem 0}
.sa-rule li{font-size:.88rem;color:#4b5563;line-height:1.9;margin-bottom:.3rem}
.sa-warn{background:#fef3c7;border:1px solid #f59e0b;border-radius:1rem;padding:1.25rem;margin:1.5rem 0}
.sa-warn h3{font-size:.95rem;font-weight:700;color:#92400e;margin-bottom:.5rem}
.sa-warn p{font-size:.88rem;color:#78350f;line-height:1.8}
.sa-contact{background:#f0fdfa;border:1px solid #ccfbf1;border-radius:1rem;padding:1.25rem;text-align:center;margin:1.5rem 0}
.sa-contact p{color:#374151;font-size:.9rem;line-height:1.8}
.sa-contact a{color:#0d9488;font-weight:600;text-decoration:none}
.sa-contact a[href^="tel:"]{direction:ltr;unicode-bidi:embed}
</style>

<div class="sa-header">
    <h1>قوانین درج آگهی</h1>
    <p>شرایط و قوانین ثبت آگهی در سایت شتاب و سایت‌های تبلیغاتی</p>
    <a class="sa-back" href="{{ route('site-ads') }}">← بازگشت به صفحه اصلی</a>
</div>

<div class="sa-body">
    <div class="sa-rule">
        <h3>📋 شرایط عمومی</h3>
        <p>ثبت آگهی در سایت شتاب و سایت‌های تبلیغاتی کشور تابع قوانین جمهوری اسلامی ایران می‌باشد. کلیه آگهی‌ها باید مطابق با قوانین و مقررات کشور باشند.</p>
    </div>

    <div class="sa-rule">
        <h3>🚫 محتوای ممنوعه</h3>
        <p>درج آگهی در موارد زیر مجاز نیست و بلافاصله حذف خواهد شد:</p>
        <ul>
            <li>تبلیغات ماهواره‌ای و لوازم آن</li>
            <li>سایت‌های همسریابی و دوست‌یابی</li>
            <li>فروش اسلحه و مواد مخدر</li>
            <li>هرگونه فعالیت غیرقانونی</li>
            <li>محتوای نامناسب و خلاف عرف</li>
            <li>تبلیغات فریبکارانه و گمراه‌کننده</li>
        </ul>
    </div>

    <div class="sa-rule">
        <h3>📝 شرایط متن آگهی</h3>
        <p>متن آگهی باید شامل موارد زیر باشد:</p>
        <ul>
            <li>عنوان واضح و مرتبط با موضوع آگهی</li>
            <li>توضیحات کامل و جامع درباره خدمات یا محصولات</li>
            <li>شماره تماس ثابت و همراه معتبر</li>
            <li>آدرس دقیق در صورت وجود</li>
            <li>کلمات کلیدی مرتبط</li>
        </ul>
    </div>

    <div class="sa-rule">
        <h3>⏰ مدت زمان نمایش</h3>
        <p>مدت زمان نمایش آگهی در سایت‌های تبلیغاتی تابع قانون همان سایت می‌باشد که از ۷ روز تا یک سال متغیر است. لازم به ذکر است در تعدادی از سایت‌ها آگهی امکان دارد برای بیشتر از یکسال هم باقی بماند.</p>
    </div>

    <div class="sa-rule">
        <h3>🔄 بروزرسانی</h3>
        <p>با استفاده از گزارشکار و فیلم آموزشی به صورت رایگان با روزی ۱۵ دقیقه آنها را بروزرسانی نمایید. لازم به ذکر ۵۰ تا ۷۰ درصد سایت‌ها گزینه بروزرسانی دارند.</p>
    </div>

    <div class="sa-warn">
        <h3>⚠️ تذکر مهم</h3>
        <p>ثبت آگهی توسط نیروی انسانی انجام می‌گردد. در نهایت تائید آگهی توسط مدیر سایت مربوطه بنا به متن و موضوع آگهی معمولاً ۵۵٪ تا ۹۰٪ می‌باشد. در صورت عدم تائید آگهی، هزینه برگشت داده نمی‌شود.</p>
    </div>

    <div class="sa-contact">
        <p>در صورت داشتن سوال با ما تماس بگیرید: <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴</a> یا <a href="https://wa.me/989193841239">واتس‌آپ</a></p>
    </div>

    <div style="text-align:center;margin-top:2rem">
        <a href="{{ route('site-ads') }}" style="display:inline-flex;align-items:center;gap:.3rem;background:#0d9488;color:#fff;padding:.6rem 1.5rem;border-radius:999px;font-weight:700;text-decoration:none;transition:all .2s">← بازگشت به صفحه اصلی ثبت آگهی</a>
    </div>
</div>
@endsection
