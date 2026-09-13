@extends('layouts.app', ['title' => 'تبلیغات در سایت‌های تبلیغاتی کشور | ' . config('app.name'), 'description' => 'ثبت آگهی شما در بیش از 50 سایت تبلیغاتی کشور'])

@php
    $foundedYear = 1390;
    $currentJalaliYear = jalali_year();
    $yearsOfExperience = $currentJalaliYear - $foundedYear;
@endphp

@section('content')
<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideRight{from{opacity:0;transform:translateX(-30px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideLeft{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
@keyframes scaleIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:scale(1)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(13,148,136,.3)}50%{box-shadow:0 0 0 12px rgba(13,148,136,0)}}
@keyframes borderGlow{0%,100%{border-color:#14b8a6}50%{border-color:#2dd4bf}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}

.sa-hero{position:relative;background:linear-gradient(135deg,#0d9488 0%,#0f766e 40%,#115e59 100%);color:#fff;padding:4rem 1.5rem 3.5rem;text-align:center;overflow:hidden;animation:fadeUp .6s ease-out}
.sa-hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);background-size:30px 30px;animation:float 6s ease-in-out infinite}
.sa-hero::after{content:'';position:absolute;bottom:-2px;left:0;right:0;height:60px;background:linear-gradient(transparent,#f9fafb)}
.sa-hero *{position:relative;z-index:1}
.sa-hero-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);padding:.4rem 1.2rem;border-radius:999px;font-size:.82rem;backdrop-filter:blur(8px);margin-bottom:1.25rem;animation:fadeIn .8s .2s both}
.sa-hero h1{font-size:2.2rem;font-weight:900;line-height:1.5;margin-bottom:1rem;animation:fadeUp .7s .3s both}
.sa-hero h1 span{color:#fbbf24}
.sa-hero p{font-size:1.05rem;opacity:.9;max-width:640px;margin:0 auto;line-height:1.9;animation:fadeUp .7s .4s both}
.sa-hero-cta{display:inline-flex;align-items:center;gap:.5rem;background:#f97316;color:#fff;padding:.85rem 2.5rem;border-radius:999px;font-weight:800;font-size:1.05rem;text-decoration:none;margin-top:1.5rem;transition:all .25s;animation:fadeUp .7s .5s both;box-shadow:0 4px 15px rgba(249,115,22,.35)}
.sa-hero-cta:hover{background:#ea580c;transform:translateY(-3px);box-shadow:0 8px 25px rgba(249,115,22,.45)}

.sa-section{max-width:1000px;margin:0 auto;padding:2.5rem 1rem}
.sa-stitle{text-align:center;margin-bottom:2rem}
.sa-stitle h2{font-size:1.4rem;font-weight:800;color:#111827;margin-bottom:.4rem}
.sa-stitle p{color:#6b7280;font-size:.92rem;line-height:1.7}
.sa-stitle .sa-line{width:60px;height:3px;background:linear-gradient(90deg,#0d9488,#14b8a6);border-radius:999px;margin:.6rem auto 0}

.sa-btn-row{display:flex;flex-wrap:wrap;justify-content:center;gap:.5rem;margin:1.5rem 0}
.sa-btn{display:inline-flex;align-items:center;gap:.3rem;padding:.5rem 1.2rem;border-radius:999px;font-size:.85rem;font-weight:600;text-decoration:none;color:#fff;transition:all .2s}
.sa-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.2);filter:brightness(1.1)}

.sa-audio{text-align:center;margin:1.5rem 0}
.sa-audio audio{width:100%;max-width:500px;border-radius:999px}

.sa-img{text-align:center;margin:1.5rem 0}
.sa-img img{max-width:100%;border-radius:1rem;box-shadow:0 4px 20px rgba(0,0,0,.08)}

.sa-features{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem}
.sa-feat{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.5rem;text-align:center;transition:all .3s;position:relative;overflow:hidden}
.sa-feat::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#0d9488,#14b8a6);transform:scaleX(0);transition:transform .3s;transform-origin:left}
.sa-feat:hover{transform:translateY(-6px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.sa-feat:hover::before{transform:scaleX(1)}
.sa-feat-icon{width:52px;height:52px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border-radius:1rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto .8rem;transition:transform .3s}
.sa-feat:hover .sa-feat-icon{transform:scale(1.1) rotate(-5deg)}
.sa-feat h3{font-size:.95rem;font-weight:700;color:#111827;margin-bottom:.4rem}
.sa-feat p{font-size:.83rem;color:#6b7280;line-height:1.7}

.sa-price{background:linear-gradient(135deg,#f0fdfa 0%,#ccfbf1 50%,#a7f3d0 100%);border:2px solid #14b8a6;border-radius:1.5rem;padding:2.5rem 2rem;text-align:center;margin:2rem 0;position:relative;overflow:hidden;animation:borderGlow 3s ease-in-out infinite}
.sa-price::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(13,148,136,.06) 1px,transparent 1px);background-size:20px 20px}
.sa-price *{position:relative}
.sa-price-tag{display:inline-block;background:#0d9488;color:#fff;padding:.25rem 1rem;border-radius:999px;font-size:.78rem;font-weight:600;margin-bottom:.75rem}
.sa-price .sa-amount{font-size:3rem;font-weight:900;color:#0d9488;line-height:1.2}
.sa-price .sa-unit{font-size:1.1rem;color:#6b7280;font-weight:500}
.sa-price .sa-pdesc{color:#374151;font-size:.9rem;margin:.75rem 0;line-height:1.8}
.sa-price-cta{display:inline-flex;align-items:center;gap:.5rem;background:#0d9488;color:#fff;padding:.85rem 3rem;border-radius:999px;font-weight:800;font-size:1.05rem;text-decoration:none;margin-top:1rem;transition:all .25s;box-shadow:0 4px 15px rgba(13,148,136,.3);animation:pulse 2s infinite}
.sa-price-cta:hover{background:#0f766e;transform:translateY(-2px);box-shadow:0 8px 25px rgba(13,148,136,.4);animation:none}

.sa-benefits{background:#f9fafb;border-radius:1rem;padding:2rem;margin:2rem 0}
.sa-benefits h3{font-size:1.1rem;font-weight:800;color:#0d9488;margin-bottom:1rem;text-align:center}
.sa-benefits ol{padding-right:1.2rem;color:#374151;font-size:.9rem;line-height:2}
.sa-benefits ol li{margin-bottom:.5rem}
.sa-benefits .highlight{color:#800000}

.sa-why{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
.sa-why-card{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.25rem;display:flex;gap:1rem;align-items:flex-start;transition:all .3s}
.sa-why-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.06);transform:translateY(-2px)}
.sa-why-num{width:36px;height:36px;background:linear-gradient(135deg,#0d9488,#14b8a6);color:#fff;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0}
.sa-why-card h3{font-size:.9rem;font-weight:700;color:#111827;margin-bottom:.3rem}
.sa-why-card p{font-size:.82rem;color:#6b7280;line-height:1.7}

.sa-quality{background:#f9fafb;border-radius:1rem;padding:2rem;margin:2rem 0}
.sa-quality h3{font-size:1.1rem;font-weight:800;color:#0d9488;margin-bottom:1rem;text-align:center}
.sa-quality-list{list-style:none;padding:0;counter-reset:q}
.sa-quality-list li{counter-increment:q;padding:.5rem 0;font-size:.9rem;color:#374151;line-height:1.8;border-bottom:1px solid #f3f4f6;display:flex;gap:.5rem;align-items:flex-start}
.sa-quality-list li::before{content:counter(q);background:#0d9488;color:#fff;width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0}

.sa-trust{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin:2rem 0}
.sa-trust-item{background:#fff;border:2px solid #f3f4f6;border-radius:1rem;padding:1.25rem .75rem;text-align:center;transition:all .3s}
.sa-trust-item:hover{border-color:#14b8a6;transform:translateY(-3px);box-shadow:0 8px 20px rgba(13,148,136,.1)}
.sa-trust-num{font-size:1.8rem;font-weight:900;color:#0d9488;line-height:1.2}
.sa-trust-label{font-size:.78rem;color:#6b7280;margin-top:.25rem}

.sa-faq details{border:1px solid #f3f4f6;border-radius:.75rem;margin-bottom:.6rem;overflow:hidden;transition:all .3s}
.sa-faq details[open]{border-color:#14b8a6;box-shadow:0 4px 12px rgba(13,148,136,.08)}
.sa-faq summary{padding:.9rem 1rem;font-weight:600;font-size:.9rem;cursor:pointer;color:#111827;list-style:none;display:flex;align-items:center;gap:.5rem;transition:background .2s}
.sa-faq summary:hover{background:#f9fafb}
.sa-faq summary::before{content:'▸';color:#0d9488;font-size:1.1rem;transition:transform .25s;flex-shrink:0}
.sa-faq details[open] summary::before{transform:rotate(90deg)}
.sa-faq .sa-a{padding:0 1rem 1rem;font-size:.85rem;color:#4b5563;line-height:1.9}

.sa-contact{background:linear-gradient(135deg,#115e59,#0d9488);border-radius:1.25rem;padding:2rem;text-align:center;color:#fff;margin:2rem 0}
.sa-contact h3{font-size:1.1rem;font-weight:700;margin-bottom:1rem}
.sa-contact-links{display:flex;flex-wrap:wrap;justify-content:center;gap:.75rem}
.sa-contact-links a{display:inline-flex;align-items:center;gap:.35rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:#fff;padding:.5rem 1.2rem;border-radius:999px;font-size:.85rem;font-weight:600;text-decoration:none;transition:all .2s;backdrop-filter:blur(4px)}
.sa-contact-links a:hover{background:rgba(255,255,255,.25);transform:translateY(-2px)}
.sa-contact a[href^="tel:"]{direction:ltr;unicode-bidi:embed}

@media(max-width:768px){
  .sa-hero h1{font-size:1.5rem}
  .sa-features,.sa-why{grid-template-columns:1fr}
  .sa-trust{grid-template-columns:repeat(2,1fr)}
  .sa-price .sa-amount{font-size:2.2rem}
  .sa-btn-row{gap:.35rem}
  .sa-btn{font-size:.75rem;padding:.4rem .8rem}
}
</style>

<div class="sa-hero">
    <span class="sa-hero-badge">🔥 تبلیغات انبوه در سراسر ایران</span>
    <h1>ثبت آگهی در بیش از <span>۵۰ سایت</span> تبلیغاتی کشور</h1>
    <p>یکی از راهکارهای تبلیغاتی فوق‌العاده‌ای می‌باشد که آگهی واحد صنفی شما را در کوتاه‌ترین زمان ممکن با توضیحات کامل و جامع در «۵۰ سایت تبلیغاتی کشور» ثبت می‌گردد و بعد از تائید آگهی شما توسط مدیران سایت‌ها، شمار بازدیدکنندگان از محصولات یا خدمات شما افزوده خواهد شد.</p>
    <a class="sa-hero-cta" href="https://shetabe.ir/create-listing/">🚀 ثبت سفارش</a>
</div>

<div class="sa-section">
    <div class="sa-btn-row" style="animation:fadeUp .5s .2s both">
        <a class="sa-btn" href="https://shetabe.ir/create-listing/" style="background:#9e336e">سفارش ثبت در ۵۰ سایت تبلیغاتی</a>
        <a class="sa-btn" href="{{ route('site-ads.samples') }}" style="background:#9e8e33">نمونه کار</a>
        <a class="sa-btn" href="/site-ads/images/factor.jpg" style="background:#489e33">فاکتور</a>
        <a class="sa-btn" href="{{ route('site-ads.rules') }}" style="background:#0044ff">قوانین درج آگهی</a>
        <a class="sa-btn" href="{{ route('site-ads.report') }}" style="background:#750000">گزارشکار</a>
        <a class="sa-btn" href="{{ route('site-ads.pricing') }}" style="background:#489e33">تعرفه</a>
        <a class="sa-btn" href="{{ route('site-ads.licenses') }}" style="background:#007546">مجوزها</a>
        <a class="sa-btn" href="https://shetabe.ir/%d8%aa%d9%85%d8%a7%d8%b3-%d8%a8%d8%a7%d9%85%d8%a7/" style="background:#00a8a2">تماس باما</a>
        <a class="sa-btn" href="{{ route('site-ads.sites') }}" style="background:#489e33">لیست سایت‌ها</a>
        <a class="sa-btn" href="https://shetabe.ir/sms/" style="background:#38339e">تبلیغات پیامکی</a>
        <a class="sa-btn" href="https://shetabe.ir/google-ads/" style="background:#33809e">تبلیغات گوگل</a>
        <a class="sa-btn" href="https://shetabe.ir/socialnetworks/" style="background:#750000">تبلیغات شبکه‌های اجتماعی</a>
        <a class="sa-btn" href="https://shetabe.ir/hosting-domain/" style="background:#c2a500">تبلیغات طراحی سایت ساده</a>
    </div>

    <div class="sa-img" style="animation:fadeUp .5s .3s both">
        <img src="/site-ads/images/katalog1405.jpg" alt="کاتالوگ تبلیغاتی ۱۴۰۵" loading="lazy">
    </div>

    <div class="sa-stitle">
        <h2 style="color:#ff0000">توضیحات مدیریت سایت شتاب در خصوص ثبت آگهی در ۵۰ سایت نیازمندی و تبلیغات کشور</h2>
    </div>

    <div class="sa-audio" style="animation:fadeUp .5s .4s both">
        <audio controls preload="none"><source src="/site-ads/audio/moshavere3-low.mp3" type="audio/mpeg">مرورگر شما از پخش صوت پشتیبانی نمی‌کند.</audio>
    </div>

    <div class="sa-price" style="animation:scaleIn .5s .5s both">
        <span class="sa-price-tag">💰 ویژه + ثبت در ۵۰ سایت</span>
        <div class="sa-amount">۹۵۰,۰۰۰ <span class="sa-unit">تومان</span></div>
        <div class="sa-pdesc">ثبت یکساله آگهی شما در سایت شتاب + ثبت در ۵۰ سایت تبلیغاتی کشور<br>ثبت دستی + گزارش آنلاین نام کاربری و کلمه عبور + فاکتور</div>
        <a class="sa-price-cta" href="https://shetabe.ir/create-listing/">ثبت سفارش</a>
    </div>

    <div class="sa-contact" style="animation:fadeUp .5s .6s both">
        <h3>📞 تلفن مشاوره تبلیغاتی</h3>
        <div class="sa-contact-links">
            <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴ (مستقیم)</a>
            <a href="tel:+982191096385">۰۲۱-۹۱۰۹۶۳۸۵ (داخلی ۳)</a>
            <a href="https://wa.me/989193841239">💬 واتس‌آپ</a>
            <a href="https://t.me/Shetabir">💬 تلگرام</a>
        </div>
    </div>
</div>

<div class="sa-section">
    <div class="sa-benefits" style="animation:fadeUp .5s .1s both">
        <h3>✅ مزایای تبلیغ در سایت‌های تبلیغاتی کشور</h3>
        <ol>
            <li>با ثبت آگهی در این سایت‌ها، روزانه بازدیدکنندگان سایت‌های نیازمندیها، تبلیغات و آگهی کشور، می‌توانند آگهی شما را در گروه شغلی شما بازدید نمایند.</li>
            <li class="highlight">با ثبت آگهی در این سایت‌ها، به صورت جداگانه آگهی شما از طریق تعدادی از این سایت‌ها در موتورهای جستجو گوگل رفته و به تناسب فعالیت رقبایتان در ردیفها و کلمات کلیدی مختلف نمایش داده می‌شوید.</li>
            <li>با درج آگهی در این سایت‌ها، درصورتیکه سایت، کانال تلگرام، صفحه اینستاگرام دارید می‌توان با درج لینک آن در آگهی بازدیدکنندگان به آنجا هدایت و افزایش اعضاء، رتبه و تماس را به دنبال داشته باشد.</li>
        </ol>
        <p style="margin-top:.75rem;font-size:.88rem;color:#374151;line-height:1.8">در صورتیکه سایت ندارید می‌توان با هزینه ۱۹۰ هزار تومان یک دامین سایت .ir به آگهی شما متصل گردد تا از لحاظ بازخورد نتیجه بالاتری بگیرید و در آینده بتوانید سایت خود را گسترش دهید.</p>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>مجوزها</h2>
        <div class="sa-line"></div>
    </div>
    <div class="sa-img">
        <a href="{{ route('site-ads.licenses') }}"><img src="/site-ads/images/a01.jpg" alt="نماد اعتماد" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:140px;border-radius:.5rem"></a>
        <a href="https://trustseal.enamad.ir/?id=253106&Code=4NibPmVewfykiZBv3Fpp" target="_blank" rel="noopener"><img src="/site-ads/images/namad.svg" alt="نماد اعتماد الکترونیک" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:140px;border-radius:.5rem"></a>
        <a href="https://shetabe.ir/" target="_blank" rel="noopener"><img src="/site-ads/images/logo-shetabe.svg" alt="شتاب آگهی" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:140px;border-radius:.5rem"></a>
        <a href="https://logo.saramad.ir/verify.aspx?CodeShamad=1-1-686868-65-0-9" target="_blank" rel="noopener"><img src="/site-ads/images/a01.jpg" alt="ساماد" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:140px;border-radius:.5rem"></a>
    </div>
    <div class="sa-img">
        <a href="{{ route('site-ads.licenses') }}"><img src="/site-ads/images/javaz-low2.jpg" alt="جواز کسب" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:90px;border-radius:.5rem"></a>
        <a href="{{ route('site-ads.licenses') }}"><img src="/site-ads/images/govahi-low2.jpg" alt="گواهی" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:90px;border-radius:.5rem"></a>
        <a href="{{ route('site-ads.licenses') }}"><img src="/site-ads/images/logo-mojavez.svg" alt="مجوزها" loading="lazy" style="display:inline-block;margin:0 .5rem;max-height:130px;border-radius:.5rem"></a>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>چرا درج آگهی در سایت‌های تبلیغاتی را به شبکه شتاب ارائه می‌کنیم؟</h2>
        <div class="sa-line"></div>
    </div>
    <div class="sa-why">
        <div class="sa-why-card" style="animation:slideRight .5s .1s both">
            <div class="sa-why-num">۱</div>
            <div><h3>صرفه‌جویی در هزینه</h3><p>چنانچه جهت درج آگهی به سایت‌های فوق مبلغ پرداخت نمائید اگر ماهی حداقل ۲۰ هزار تومان و سالی ۲۴۰ هزار تومان برای هر سایت در نظر گرفته شود سالانه مبلغ ۱۶ میلیون تومان هزینه می‌شود.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideLeft .5s .2s both">
            <div class="sa-why-num">۲</div>
            <div><h3>صرفه‌جویی در زمان</h3><p>چنانچه شما خودتان بخواهید تک‌تک جستجو، ثبت عضویت، ثبت آگهی و گزارش‌گیری نمایید حداقل یک ماه زمان می‌برد و نمی‌توانید به راحتی آن را مدیریت نمایید.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideRight .5s .3s both">
            <div class="sa-why-num">۳</div>
            <div><h3>اجرا توسط کارشناس</h3><p>شبکه شتاب آگهی شما را با پرسنل حرفه‌ای، درچارچوب قانون تعریف‌شده، با آخرین تکنولوژی در اسرع وقت در سایت‌های موردنظر درج می‌نماید.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideLeft .5s .4s both">
            <div class="sa-why-num">۴</div>
            <div><h3>بروزرسانی آسان</h3><p>با استفاده از گزارشکار و فیلم آموزشی به صورت رایگان با روزی ۱۵ دقیقه آنها را بروزرسانی نمایید. لازم به ذکر ۵۰ تا ۷۰ درصد سایت‌ها گزینه بروزرسانی دارند.</p></div>
        </div>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>مدت زمان و نحوه کار</h2>
        <div class="sa-line"></div>
    </div>
    <div class="sa-benefits">
        <ol>
            <li>مدت زمان نمایش آگهی در سایت‌های تبلیغاتی یاد شده تابع قانون همان سایت می‌باشد که از ۷ روز تا یک سال می‌باشد.</li>
            <li class="highlight">لازم به ذکر است در تعدادی از سایت‌ها آگهی امکان دارد برای بیشتر از یکسال هم باقی بماند و با بروزرسانی ماهیانه می‌توان بازدهی کار را بالا برد.</li>
            <li>ثبت بصورت واقعی و توسط نیروی انسانی انجام می‌گردد. در نهایت تائید آگهی توسط مدیر سایت مربوطه بنا به متن و موضوع آگهی معمولاً ۵۵٪ تا ۹۰٪ می‌باشد.</li>
        </ol>
    </div>
</div>

<div class="sa-section">
    <div class="sa-quality" style="animation:fadeUp .5s both">
        <h3>🏅 میزان اصالت و کیفیت کار</h3>
        <ol class="sa-quality-list">
            <li>شبکه شتاب با بیش از {{ to_persian_digits($yearsOfExperience) }} سال سابقه تنها مرجع معتبر در عرصه تبلیغات انبوه سایت‌های اینترنتی می‌باشد.</li>
            <li>شبکه شتاب مجموعه ثبت شده در اداره ثبت شرکت‌ها و مؤسسات می‌باشد.</li>
            <li>شبکه شتاب دارای نماد اعتماد الکترونیک از وزارت صنعت معدن تجارت می‌باشد.</li>
            <li>شبکه شتاب در ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی ثبت می‌باشد.</li>
            <li>شبکه شتاب دارای پروانه کسب در اتحادیه کسب و کارهای مجازی می‌باشد.</li>
            <li>شبکه شتاب برای خدمات خود فاکتور صادر و ارائه می‌نماید.</li>
            <li>شبکه شتاب در پایان کار آنلاین لیست سایت‌ها به همراه نام کاربری و کلمه عبور را ارائه می‌نماید.</li>
        </ol>
    </div>
</div>

<div class="sa-section">
    <div class="sa-img">
        <img src="/site-ads/images/list-sitha-scaled.jpg" alt="لیست سایت‌های تبلیغاتی" loading="lazy">
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>بیش از ۹۰ هزار نمونه کار و آگهی</h2>
    </div>
    <div class="sa-btn-row">
        <a class="sa-btn" href="{{ route('site-ads.samples') }}" style="background:#1f7505;font-size:.9rem;padding:.6rem 1.5rem">نمونه کار</a>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2 style="color:#000080">تبلیغات مشابه جهت بازدهی بیشتر آگهی</h2>
    </div>
    <div class="sa-btn-row">
        <a href="https://shetabe.ir/sms/" target="_blank" rel="noopener"><img src="/site-ads/images/2.svg" alt="تبلیغات پیامکی" loading="lazy" style="height:60px"></a>
        <a href="https://shetabe.ir/socialnetworks/" target="_blank" rel="noopener"><img src="/site-ads/images/3.svg" alt="تبلیغات شبکه اجتماعی" loading="lazy" style="height:60px"></a>
        <a href="https://shetabe.ir/google-ads/" target="_blank" rel="noopener"><img src="/site-ads/images/4.svg" alt="تبلیغات گوگل" loading="lazy" style="height:60px"></a>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>نمونه گزارش درج آگهی در سایت‌های تبلیغاتی</h2>
    </div>
    <div class="sa-img">
        <img src="/site-ads/images/report.jpg" alt="نمونه گزارش" loading="lazy">
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>نمونه فاکتور</h2>
    </div>
    <div class="sa-img">
        <img src="/site-ads/images/factor1405.jpg" alt="نمونه فاکتور" loading="lazy">
    </div>
</div>

<div class="sa-section">
    <div class="sa-contact" style="animation:fadeUp .5s both">
        <h3>📞 تلفن مشاوره تبلیغاتی</h3>
        <div class="sa-contact-links">
            <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴ (مستقیم)</a>
            <a href="tel:+982191096385">۰۲۱-۹۱۰۹۶۳۸۵ (داخلی ۳)</a>
            <a href="https://wa.me/989193841239">💬 واتس‌آپ</a>
            <a href="https://t.me/Shetabir">💬 تلگرام</a>
        </div>
    </div>

    <div class="sa-price" style="animation:scaleIn .5s both">
        <span class="sa-price-tag">💰 ویژه + ثبت در ۵۰ سایت</span>
        <div class="sa-amount">۹۵۰,۰۰۰ <span class="sa-unit">تومان</span></div>
        <div class="sa-pdesc">ثبت یکساله آگهی شما در سایت شتاب + ثبت در ۵۰ سایت تبلیغاتی کشور<br>ثبت دستی + گزارش آنلاین نام کاربری و کلمه عبور</div>
        <a class="sa-price-cta" href="https://shetabe.ir/create-listing/">ثبت سفارش</a>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2 style="color:#ff0000">توضیحات مدیریت سایت شتاب در خصوص سوالات متداول</h2>
    </div>

    <div class="sa-audio" style="animation:fadeUp .5s both">
        <audio controls preload="none"><source src="/site-ads/audio/voic-soalat.mp3" type="audio/mpeg">مرورگر شما از پخش صوت پشتیبانی نمی‌کند.</audio>
    </div>

    <div class="sa-faq">
        <details><summary>سوال: سایت شتاب معتبر است؟</summary><div class="sa-a">پاسخ: اعتبار سایت شتاب موارد ذیل می‌باشد:<br>۱- سابقه {{ to_persian_digits($yearsOfExperience) }} ساله از سال {{ to_persian_digits($foundedYear) }} تاکنون<br>۲- دارای نماد اعتماد از وزارت صنعت معدن تجارت<br>۳- ثبت در ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی<br>۴- جواز کسب از اتحادیه کسب و کار اینترنتی<br>۵- مجوزهای متعدد که در پایین سایت شتاب قابل مشاهده می‌باشد<br>۶- ارائه فاکتور معتبر<br>۷- پذیرای بیش از ۸۰ هزار آگهی اصناف و مشاغل</div></details>

        <details><summary>سوال: ثبت در سایت‌ها چگونه است؟</summary><div class="sa-a">یکی از ارزان‌ترین، معتبرترین و سریع‌ترین روش تبلیغات فضای مجازی درج آگهی در سایت‌های تبلیغاتی کشور می‌باشد.</div></details>

        <details><summary>سوال: نمونه آگهی را چگونه ببینیم؟</summary><div class="sa-a">پاسخ: در سایت <a href="https://shetabe.ir" style="color:#0d9488;font-weight:600">shetabe.ir</a> نمونه آگهی‌های مختلف شغلی موجود و قابل مشاهده است.</div></details>

        <details><summary>سوال: چه مطالبی در آگهی قید می‌شود؟</summary><div class="sa-a">پاسخ: یک آگهی حاوی عنوان، متن و توضیحات خدمات شما، شماره تماس ثابت و همراه، آدرس، کلمات کلیدی، لینک سایت، کانال تلگرام و اینستاگرام است.</div></details>

        <details><summary>سوال: آگهی در سایت شتاب به چه صورت نمایش داده می‌شود؟</summary><div class="sa-a">پاسخ: آگهی شما در سایت تبلیغاتی شتاب به صورت ویژه و یکساله نمایش داده می‌شود. و نیز همزمان به صورت دستی در ۵۰ سایت تبلیغاتی دیگر رایگان درج می‌شود.</div></details>

        <details><summary>سوال: کلیه مشاغل امکان ثبت آگهی در سایت‌های تبلیغاتی را دارند؟</summary><div class="sa-a">پاسخ: بله؛ اکثریت مشاغل امکان ثبت آگهی در سایت‌ها را دارند. فقط آگهی در موضوعات غیرمجاز مانند ماهواره، همسریابی و ... ممنوع می‌باشد.</div></details>

        <details><summary>سوال: از سراسر کشور آگهی پذیرفته می‌شود؟</summary><div class="sa-a">پاسخ: بله؛ امکان ثبت آگهی در تمام نقاط کشور فراهم است و قابلیت نمایش بیشتر در شهر مورد نظر شما را دارند.</div></details>

        <details><summary>سوال: هزینه چقدر است؟</summary><div class="sa-a">پاسخ: تعرفه بابت ثبت <strong>در هر سایت حدود ۱۰ هزار تومان</strong> است. ثبت در ۵۰ سایت معادل <strong>۹۵۰ هزار تومان</strong>.</div></details>

        <details><summary>سوال: نحوه پرداخت هزینه چگونه است؟</summary><div class="sa-a">پاسخ: در سایت فاکتور معتبر دریافت می‌نمایید. امکان پرداخت از درگاه بانکی معتبر بهپرداخت ملت، یا شماره حساب برای کارت به کارت فراهم است.</div></details>

        <details><summary>سوال: زمان انجام کار چقدر است؟</summary><div class="sa-a">پاسخ: ثبت آگهی توسط پرسنل بصورت دستی انجام می‌شود. زمان انجام کار حدود ۷ تا ۱۴ روز است.</div></details>

        <details><summary>سوال: مدت زمان نگهداری آگهی در سایت‌ها چقدر است؟</summary><div class="sa-a">پاسخ: ما یکبار آگهی ارسالی شما را در سایت‌ها ثبت و منتشر می‌کنیم. مدت زمان نگهداری آگهی‌ها از یکماه تا یکسال متغییر است و حدود ۵۰ درصد سایت‌ها یکساله هستند. پیشنهاد می‌کنیم ماهیانه یا سه ماه یکبار نسبت به سفارش ثبت مجدد آگهی با متن جدید اقدام کنید.</div></details>

        <details><summary>سوال: تفاوت آگهی ویژه و رایگان چیست؟</summary><div class="sa-a">پاسخ: از لحاظ بازدیدکنندگان و گوگل تفاوتی بین آگهی رایگان و ویژه وجود ندارد. اما هنگامی که آگهی ویژه می‌شود در آن سایت در ردیف‌های بالاتری دیده خواهد شد.<br>ولی چنانچه ۵۰ سایت را ویژه کنیم حدود ۲۰ میلیون تومان مبلغ باید بپردازیم که برای اکثر مشاغل مقرون به صرف نیست.</div></details>

        <details><summary>سوال: آیا همه آگهی‌ها در سایت‌ها تائید شده و در گوگل می‌رود؟</summary><div class="sa-a">پاسخ: بعد از ثبت آگهی مدیران سایت‌ها بستگی به موضوع و حوزه کاری آگهی شما را از ۵۰٪ تا ۱۰۰٪ تائید می‌کنند. در صورتیکه رقیبی نداشته یا رقیب ضعیف داشته باشید تعدادی در ردیف‌های گوگل می‌روند. ولی چنانچه رقیب قوی داشته باشید نیازمند چندین بار ثبت آگهی با متنهای مختلف می‌باشید.</div></details>

        <details><summary>سوال: آگهی سئو می‌شود؟</summary><div class="sa-a">پاسخ: توسط تیم شتاب بصورت رایگان ظرف ۷۲ ساعت آگهی بررسی و برای بازدهی بهتر آگهی‌هایی که متن آنها ضعیف می‌باشد کلمات مناسب از گوگل جمع آوری و به آگهی اضافه می‌شود.</div></details>

        <details><summary>سوال: رنک و سئو سایت‌ها چگونه است؟</summary><div class="sa-a">پاسخ: میزان رنک و سئو سایت‌های تبلیغاتی متنوع بوده و از سایت‌های برتر کشور تا مطرح کشور متنوع بوده و همین تنوع از اسپم و ریپورت شدن جلوگیری نموده و بهترین حالت انتشار آگهی شما می‌باشد.</div></details>

        <details><summary>سوال: آیا مجموعه شتاب بروزرسانی آگهی انجام می‌دهد؟</summary><div class="sa-a">پاسخ: جهت بروزرسانی و افزایش بازدید آگهی پیشنهاد می‌گردد:<br>الف- با لینک آگهی، نام کاربری و کلمه عبور که در گزارش برای شما ارسال شده وارد پنل آگهی‌های خود شده و تک‌تک آنها را بروزرسانی نمایید.<br>ب- از داخل پنل آگهی خود در شتاب سرویس بروزرسانی اتوماتیک یکساله را خریداری نمایید.<br>ج- به صورت ماهیانه یا سه ماه یکبار آگهی خود را با متن جدید سفارش دهید تا در سایت‌ها مجدد ثبت گردد.</div></details>

        <details><summary>سوال: برای ثبت و ارسال مطالب چکار کنیم؟</summary><div class="sa-a">پاسخ: لطفاً جهت ارسال مطالب از طریق ثبت سفارش اقدام نمایید.</div></details>
    </div>

    <div class="sa-btn-row" style="margin-top:1.5rem">
        <a class="sa-btn" href="https://shetabe.ir/create-listing/" style="background:#339e33;font-size:1rem;padding:.75rem 2rem">ثبت سفارش</a>
    </div>

    <div class="sa-img" style="margin-top:1.5rem">
        <img src="/site-ads/images/tarife1405.jpg" alt="تعرفه ۱۴۰۵" loading="lazy">
    </div>
</div>
@endsection
