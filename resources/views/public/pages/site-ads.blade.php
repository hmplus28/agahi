@extends('layouts.app', ['title' => 'تبلیغات در سایت‌های تبلیغاتی کشور | ' . config('app.name'), 'description' => 'ثبت آگهی شما در بیش از 50 سایت تبلیغاتی کشور با هزینه مناسب و بازدهی بالا'])

@section('content')
<style>
@keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideRight{from{opacity:0;transform:translateX(-30px)}to{opacity:1;transform:translateX(0)}}
@keyframes slideLeft{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
@keyframes scaleIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:scale(1)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(13,148,136,.3)}50%{box-shadow:0 0 0 12px rgba(13,148,136,0)}}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
@keyframes countUp{from{opacity:0;transform:scale(.5)}to{opacity:1;transform:scale(1)}}
@keyframes borderGlow{0%,100%{border-color:#14b8a6}50%{border-color:#2dd4bf}}

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

.sa-why{display:grid;grid-template-columns:repeat(2,1fr);gap:1rem}
.sa-why-card{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.25rem;display:flex;gap:1rem;align-items:flex-start;transition:all .3s}
.sa-why-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.06);transform:translateY(-2px)}
.sa-why-num{width:36px;height:36px;background:linear-gradient(135deg,#0d9488,#14b8a6);color:#fff;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0}
.sa-why-card h3{font-size:.9rem;font-weight:700;color:#111827;margin-bottom:.3rem}
.sa-why-card p{font-size:.82rem;color:#6b7280;line-height:1.7}

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

.sa-img-block{text-align:center;margin:1.5rem 0}
.sa-img-block img{max-width:100%;border-radius:1rem;box-shadow:0 4px 20px rgba(0,0,0,.08)}

@media(max-width:768px){
  .sa-hero h1{font-size:1.5rem}
  .sa-features,.sa-why{grid-template-columns:1fr}
  .sa-trust{grid-template-columns:repeat(2,1fr)}
  .sa-price .sa-amount{font-size:2.2rem}
}
</style>

<div class="sa-hero">
    <span class="sa-hero-badge">🔥 تبلیغات انبوه در سراسر ایران</span>
    <h1>ثبت آگهی در بیش از <span>۵۰ سایت</span> تبلیغاتی کشور</h1>
    <p>آگهی واحد صنفی شما را در کوتاه‌ترین زمان ممکن با توضیحات کامل و جامع در بیش از ۵۰ سایت تبلیغاتی کشور ثبت می‌کنیم. بازدیدکنندگان سایت‌های نیازمندی، تبلیغات و آگهی کشور می‌توانند آگهی شما را مشاهده کنند.</p>
    <a class="sa-hero-cta" href="https://shetabe.ir/create-listing/">🚀 ثبت سفارش</a>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>چرا تبلیغات در سایت‌های تبلیغاتی؟</h2>
        <p>مزایای ثبت آگهی انبوه در شبکه شتاب</p>
        <div class="sa-line"></div>
    </div>
    <div class="sa-features">
        <div class="sa-feat" style="animation:fadeUp .5s .1s both">
            <div class="sa-feat-icon">✍️</div>
            <h3>ثبت دستی توسط کارشناس</h3>
            <p>آگهی شما توسط پرسنل حرفه‌ای و در چارچوب قانون در سایت‌ها ثبت می‌گردد.</p>
        </div>
        <div class="sa-feat" style="animation:fadeUp .5s .2s both">
            <div class="sa-feat-icon">📊</div>
            <h3>گزارش آنلاین</h3>
            <p>نام کاربری و کلمه عبور هر سایت به صورت آنلاین در اختیار شما قرار می‌گیرد.</p>
        </div>
        <div class="sa-feat" style="animation:fadeUp .5s .3s both">
            <div class="sa-feat-icon">🧾</div>
            <h3>فاکتور معتبر</h3>
            <p>برای خدمات ارائه‌شده فاکتور رسمی صادر می‌گردد.</p>
        </div>
        <div class="sa-feat" style="animation:fadeUp .5s .4s both">
            <div class="sa-feat-icon">🔍</div>
            <h3>نمایش در گوگل</h3>
            <p>آگهی شما از طریق تعدادی از سایت‌ها در موتورهای جستجوی گوگل نمایش داده می‌شود.</p>
        </div>
        <div class="sa-feat" style="animation:fadeUp .5s .5s both">
            <div class="sa-feat-icon">🔗</div>
            <h3>لینک‌سازی مؤثر</h3>
            <p>لینک سایت، کانال تلگرام و اینستاگرام شما در آگهی درج می‌گردد.</p>
        </div>
        <div class="sa-feat" style="animation:fadeUp .5s .6s both">
            <div class="sa-feat-icon">📅</div>
            <h3>نمایش یکساله</h3>
            <p>آگهی شما به صورت ویژه و یکساله در سایت شتاب نمایش داده می‌شود.</p>
        </div>
    </div>
</div>

<div class="sa-section">
    <div class="sa-price">
        <span class="sa-price-tag">💰 ویژه + ثبت در ۵۰ سایت</span>
        <div class="sa-amount">۹۵۰,۰۰۰ <span class="sa-unit">تومان</span></div>
        <div class="sa-pdesc">ثبت یکساله آگهی شما در سایت شتاب + ثبت در ۵۰ سایت تبلیغاتی کشور<br>ثبت دستی + گزارش آنلاین نام کاربری و کلمه عبور + فاکتور</div>
        <a class="sa-price-cta" href="https://shetabe.ir/create-listing/">ثبت سفارش</a>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>چرا شبکه شتاب؟</h2>
        <p>مزایای درج آگهی در سایت‌های تبلیغاتی از طریق شتاب</p>
        <div class="sa-line"></div>
    </div>
    <div class="sa-why">
        <div class="sa-why-card" style="animation:slideRight .5s .1s both">
            <div class="sa-why-num">۱</div>
            <div><h3>صرفه‌جویی در هزینه</h3><p>هزینه ثبت تک‌تک در سایت‌ها سالانه ۱۶ میلیون تومان می‌شود. با شتاب فقط ۹۵۰ هزار تومان.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideLeft .5s .2s both">
            <div class="sa-why-num">۲</div>
            <div><h3>صرفه‌جویی در زمان</h3><p>جستجو، ثبت عضویت، ثبت آگهی و گزارش‌گیری تک‌تک سایت‌ها حداقل یک ماه زمان می‌برد.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideRight .5s .3s both">
            <div class="sa-why-num">۳</div>
            <div><h3>اجرا توسط کارشناس</h3><p>آگهی شما با پرسنل حرفه‌ای، در چارچوب قانون و با آخرین تکنولوژی ثبت می‌گردد.</p></div>
        </div>
        <div class="sa-why-card" style="animation:slideLeft .5s .4s both">
            <div class="sa-why-num">۴</div>
            <div><h3>بروزرسانی آسان</h3><p>با فیلم آموزشی رایگان، روزی ۱۵ دقیقه زمان برای بروزرسانی کافی است.</p></div>
        </div>
    </div>
</div>

<div class="sa-section">
    <div class="sa-trust">
        <div class="sa-trust-item" style="animation:scaleIn .4s .1s both">
            <div class="sa-trust-num">۱۵+</div>
            <div class="sa-trust-label">سال سابقه</div>
        </div>
        <div class="sa-trust-item" style="animation:scaleIn .4s .2s both">
            <div class="sa-trust-num">۹۰K+</div>
            <div class="sa-trust-label">نمونه کار</div>
        </div>
        <div class="sa-trust-item" style="animation:scaleIn .4s .3s both">
            <div class="sa-trust-num">۵۰+</div>
            <div class="sa-trust-label">سایت تبلیغاتی</div>
        </div>
        <div class="sa-trust-item" style="animation:scaleIn .4s .4s both">
            <div class="sa-trust-num">۹۰٪</div>
            <div class="sa-trust-label">نرخ تأیید</div>
        </div>
    </div>
</div>

<div class="sa-section">
    <div class="sa-stitle">
        <h2>سوالات متداول</h2>
        <div class="sa-line"></div>
    </div>
    <div class="sa-faq">
        <details><summary>سایت شتاب معتبر است؟</summary><div class="sa-a">سابقه ۱۳ ساله از سال ۱۳۹۰ تاکنون • دارای نماد اعتماد از وزارت صنعت معدن تجارت • ثبت در ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی • جواز کسب از اتحادیه کسب و کار اینترنتی • ارائه فاکتور معتبر • پذیرای بیش از ۸۰ هزار آگهی</div></details>
        <details><summary>ثبت در سایت‌ها چگونه است؟</summary><div class="sa-a">یکی از ارزان‌ترین، معتبرترین و سریع‌ترین روش تبلیغات فضای مجازی، درج آگهی در سایت‌های تبلیغاتی کشور می‌باشد.</div></details>
        <details><summary>چه مطالبی در آگهی قید می‌شود؟</summary><div class="sa-a">عنوان، متن و توضیحات خدمات، شماره تماس ثابت و همراه، آدرس، کلمات کلیدی، لینک سایت، کانال تلگرام و اینستاگرام.</div></details>
        <details><summary>هزینه چقدر است؟</summary><div class="sa-a">تعرفه ثبت در هر سایت حدود ۱۰ هزار تومان. ثبت در ۵۰ سایت معادل ۹۵۰ هزار تومان.</div></details>
        <details><summary>زمان انجام کار چقدر است؟</summary><div class="sa-a">ثبت آگهی توسط پرسنل به صورت دستی انجام می‌شود. زمان انجام کار حدود ۷ تا ۱۴ روز است.</div></details>
        <details><summary>مدت زمان نگهداری آگهی چقدر است؟</summary><div class="sa-a">از یکماه تا یکسال متغیر. حدود ۵۰٪ سایت‌ها یکساله هستند. پیشنهاد: ماهیانه یا سه ماه یکبار ثبت مجدد با متن جدید.</div></details>
        <details><summary>آیا آگهی در گوگل نمایش داده می‌شود؟</summary><div class="sa-a">مدیران سایت‌ها آگهی را از ۵۰٪ تا ۱۰۰٪ تأیید می‌کنند. در صورت نداشتن رقیب، آگهی در ردیف‌های گوگل قرار می‌گیرد.</div></details>
        <details><summary>تفاوت آگهی ویژه و رایگان؟</summary><div class="sa-a">از لحاظ بازدید و گوگل تفاوتی ندارد، اما آگهی ویژه در ردیف‌های بالاتری نمایش داده می‌شود.</div></details>
    </div>
</div>

<div class="sa-section">
    <div class="sa-contact">
        <h3>📞 تلفن مشاوره تبلیغاتی</h3>
        <div class="sa-contact-links">
            <a href="tel:+982166248174">۰۲۱-۶۶۲۴۸۱۷۴ (مستقیم)</a>
            <a href="tel:+982191096385">۰۲۱-۹۱۰۹۶۳۸۵ (داخلی ۳)</a>
            <a href="https://wa.me/989193841239">💬 واتس‌آپ</a>
            <a href="https://t.me/Shetabir">💬 تلگرام</a>
        </div>
    </div>
</div>
@endsection
