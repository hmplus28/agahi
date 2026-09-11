@extends('layouts.app', ['title' => 'تبلیغات در سایت‌های تبلیغاتی کشور | ' . config('app.name'), 'description' => 'ثبت آگهی شما در بیش از 50 سایت تبلیغاتی کشور با هزینه مناسب و بازدهی بالا'])

@section('content')
<style>
.hero-section{background:linear-gradient(135deg,#0d9488 0%,#0f766e 50%,#115e59 100%);color:#fff;padding:3rem 1.5rem;text-align:center;border-radius:0 0 2rem 2rem;margin-bottom:2rem}
.hero-section h1{font-size:1.75rem;font-weight:800;margin-bottom:.75rem}
.hero-section p{font-size:1rem;opacity:.9;max-width:600px;margin:0 auto;line-height:1.8}
.hero-badge{display:inline-block;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);padding:.3rem 1rem;border-radius:999px;font-size:.8rem;margin-bottom:1rem;backdrop-filter:blur(4px)}
.hero-cta{display:inline-flex;align-items:center;gap:.5rem;background:#f97316;color:#fff;padding:.75rem 2rem;border-radius:999px;font-weight:700;font-size:1rem;text-decoration:none;margin-top:1.25rem;transition:all .2s}
.hero-cta:hover{background:#ea580c;transform:translateY(-2px);box-shadow:0 6px 20px rgba(249,115,22,.35)}

.section{max-width:960px;margin:0 auto;padding:0 1rem}
.section-title{font-size:1.2rem;font-weight:700;color:#111827;text-align:center;margin-bottom:1rem}
.section-subtitle{text-align:center;color:#6b7280;font-size:.9rem;margin-bottom:1.5rem;line-height:1.7}

.feature-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin-bottom:2rem}
.feature-card{background:#fff;border:1px solid #f3f4f6;border-radius:1rem;padding:1.5rem;transition:all .2s}
.feature-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.feature-card h3{font-size:1rem;font-weight:700;color:#0d9488;margin-bottom:.5rem}
.feature-card p{font-size:.88rem;color:#4b5563;line-height:1.7}
.feature-icon{width:40px;height:40px;background:#f0fdfa;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:1.2rem;margin-bottom:.75rem}

.price-box{background:linear-gradient(135deg,#f0fdfa,#ccfbf1);border:2px solid #14b8a6;border-radius:1.25rem;padding:2rem;text-align:center;margin:2rem 0}
.price-box .amount{font-size:2.5rem;font-weight:800;color:#0d9488;margin:.5rem 0}
.price-box .unit{font-size:1rem;color:#6b7280}
.price-box .desc{color:#374151;font-size:.9rem;margin-top:.5rem;line-height:1.7}
.price-cta{display:inline-flex;align-items:center;gap:.5rem;background:#0d9488;color:#fff;padding:.75rem 2.5rem;border-radius:999px;font-weight:700;font-size:1rem;text-decoration:none;margin-top:1rem;transition:all .2s}
.price-cta:hover{background:#0f766e;transform:translateY(-2px);box-shadow:0 6px 20px rgba(13,148,136,.3)}

.qa-list{margin:2rem 0}
.qa-item{border:1px solid #f3f4f6;border-radius:.75rem;margin-bottom:.75rem;overflow:hidden}
.qa-item summary{padding:.875rem 1rem;font-weight:600;font-size:.9rem;cursor:pointer;color:#111827;list-style:none;display:flex;align-items:center;gap:.5rem}
.qa-item summary::before{content:'▸';color:#0d9488;font-size:1.1rem;transition:transform .2s}
.qa-item[open] summary::before{transform:rotate(90deg)}
.qa-item .answer{padding:0 1rem 1rem;font-size:.88rem;color:#4b5563;line-height:1.8}

.trust-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.75rem;margin:1.5rem 0}
.trust-item{background:#f9fafb;border:1px solid #f3f4f6;border-radius:.75rem;padding:1rem;text-align:center}
.trust-item .num{font-size:1.5rem;font-weight:800;color:#0d9488}
.trust-item .label{font-size:.82rem;color:#6b7280;margin-top:.25rem}

.contact-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:1rem;padding:1.5rem;text-align:center;margin:2rem 0}
.contact-box a{display:inline-flex;align-items:center;gap:.35rem;color:#0d9488;font-weight:600;text-decoration:none;margin:.3rem .75rem;font-size:.9rem;transition:color .15s}
.contact-box a:hover{color:#0f766e}
</style>

<div class="hero-section">
    <span class="hero-badge">تبلیغات انبوه در سراسر ایران</span>
    <h1>ثبت آگهی در بیش از ۵۰ سایت تبلیغاتی کشور</h1>
    <p>آگهی واحد صنفی شما را در کوتاه‌ترین زمان ممکن با توضیحات کامل و جامع در بیش از ۵۰ سایت تبلیغاتی کشور ثبت می‌کنیم.</p>
    <a class="hero-cta" href="https://shetabe.ir/create-listing/">ثبت سفارش</a>
</div>

<div class="section">
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">📝</div>
            <h3>ثبت دستی توسط کارشناس</h3>
            <p>آگهی شما توسط پرسنل حرفه‌ای و در چارچوب قانون تعریف‌شده در سایت‌ها ثبت می‌گردد.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>گزارش آنلاین</h3>
            <p>نام کاربری و کلمه عبور هر سایت به صورت آنلاین در اختیار شما قرار می‌گیرد.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📋</div>
            <h3>فاکتور معتبر</h3>
            <p>برای خدمات ارائه‌شده فاکتور رسمی صادر می‌گردد.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔍</div>
            <h3>نمایش در گوگل</h3>
            <p>آگهی شما از طریق تعدادی از سایت‌ها در موتورهای جستجوی گوگل نمایش داده می‌شود.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🌐</div>
            <h3>لینک‌سازی مؤثر</h3>
            <p>لینک سایت، کانال تلگرام و اینستاگرام شما در آگهی درج می‌گردد.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⏰</div>
            <h3>نمایش یکساله در شتاب</h3>
            <p>آگهی شما به صورت ویژه و یکساله در سایت شتاب نمایش داده می‌شود.</p>
        </div>
    </div>

    <div class="price-box">
        <div class="section-title">تعرفه ثبت آگهی</div>
        <div class="desc">ثبت یکساله آگهی شما در سایت شتاب + ثبت در ۵۰ سایت تبلیغاتی کشور</div>
        <div class="amount">۹۵۰,۰۰۰ <span class="unit">تومان</span></div>
        <div class="desc">ثبت دستی + گزارش آنلاین نام کاربری و کلمه عبور + فاکتور</div>
        <a class="price-cta" href="https://shetabe.ir/create-listing/">ثبت سفارش</a>
    </div>

    <div class="section-title">مزایای تبلیغ در سایت‌های تبلیغاتی</div>
    <div class="section-subtitle">چرا درج آگهی در سایت‌های تبلیغاتی را به شبکه شتاب ارائه می‌کنیم؟</div>

    <div class="feature-grid">
        <div class="feature-card">
            <h3>۱. صرفه‌جویی در هزینه</h3>
            <p>چنانچه جهت درج آگهی به تک‌تک سایت‌ها مبلغ پرداخت نمائید، سالانه مبلغ ۱۶ میلیون تومان هزینه می‌شود.</p>
        </div>
        <div class="feature-card">
            <h3>۲. صرفه‌جویی در زمان</h3>
            <p>چنانچه شما خودتان بخواهید تک‌تک جستجو، ثبت عضویت، ثبت آگهی و گزارش‌گیری نمایید، حداقل یک ماه زمان می‌برد.</p>
        </div>
        <div class="feature-card">
            <h3>۳. اجرا توسط کارشناس</h3>
            <p>شبکه شتاب آگهی شما را با پرسنل حرفه‌ای، در چارچوب قانون تعریف‌شده، با آخرین تکنولوژی در اسرع وقت ثبت می‌نماید.</p>
        </div>
        <div class="feature-card">
            <h3>۴. بروزرسانی آسان</h3>
            <p>با استفاده از گزارشکار و فیلم آموزشی به صورت رایگان با روزی ۱۵ دقیقه می‌توانید آگهی‌ها را بروزرسانی نمایید.</p>
        </div>
    </div>

    <div class="trust-grid">
        <div class="trust-item"><div class="num">۱۵+</div><div class="label">سال سابقه</div></div>
        <div class="trust-item"><div class="num">۹۰,۰۰۰+</div><div class="label">نمونه کار و آگهی</div></div>
        <div class="trust-item"><div class="num">۵۰+</div><div class="label">سایت تبلیغاتی</div></div>
        <div class="trust-item"><div class="num">۵۵٪-۹۰٪</div><div class="label">نرخ تأیید آگهی</div></div>
    </div>

    <div class="section-title">سوالات متداول</div>
    <div class="qa-list">
        <details class="qa-item">
            <summary>سایت شتاب معتبر است؟</summary>
            <div class="answer">
                سابقه ۱۳ ساله از سال ۱۳۹۰ تاکنون • دارای نماد اعتماد از وزارت صنعت معدن تجارت • ثبت در ستاد ساماندهی وزارت فرهنگ و ارشاد اسلامی • جواز کسب از اتحادیه کسب و کار اینترنتی • ارائه فاکتور معتبر • پذیرای بیش از ۸۰ هزار آگهی اصناف و مشاغل
            </div>
        </details>
        <details class="qa-item">
            <summary>ثبت در سایت‌ها چگونه است؟</summary>
            <div class="answer">یکی از ارزان‌ترین، معتبرترین و سریع‌ترین روش تبلیغات فضای مجازی، درج آگهی در سایت‌های تبلیغاتی کشور می‌باشد.</div>
        </details>
        <details class="qa-item">
            <summary>چه مطالبی در آگهی قید می‌شود؟</summary>
            <div class="answer">یک آگهی حاوی عنوان، متن و توضیحات خدمات شما، شماره تماس ثابت و همراه، آدرس، کلمات کلیدی، لینک سایت، کانال تلگرام و اینستاگرام است.</div>
        </details>
        <details class="qa-item">
            <summary>هزینه چقدر است؟</summary>
            <div class="answer">تعرفه بابت ثبت در هر سایت حدود ۱۰ هزار تومان است. ثبت در ۵۰ سایت معادل ۹۵۰ هزار تومان.</div>
        </details>
        <details class="qa-item">
            <summary>زمان انجام کار چقدر است؟</summary>
            <div class="answer">ثبت آگهی توسط پرسنل به صورت دستی انجام می‌شود. زمان انجام کار حدود ۷ تا ۱۴ روز است.</div>
        </details>
        <details class="qa-item">
            <summary>مدت زمان نگهداری آگهی در سایت‌ها چقدر است؟</summary>
            <div class="answer">مدت زمان نگهداری آگهی‌ها از یکماه تا یکسال متغیر است و حدود ۵۰ درصد سایت‌ها یکساله هستند. پیشنهاد می‌کنیم ماهیانه یا سه ماه یکبار نسبت به سفارش ثبت مجدد آگهی با متن جدید اقدام کنید.</div>
        </details>
        <details class="qa-item">
            <summary>آیا همه آگهی‌ها در سایت‌ها تأیید شده و در گوگل می‌رود؟</summary>
            <div class="answer">بعد از ثبت آگهی، مدیران سایت‌ها بستگی به موضوع و حوزه کاری، آگهی شما را از ۵۰٪ تا ۱۰۰٪ تأیید می‌کنند. در صورتیکه رقیبی نداشته یا رقیب ضعیف داشته باشید، تعدادی در ردیف‌های گوگل قرار می‌گیرند.</div>
        </details>
        <details class="qa-item">
            <summary>تفاوت آگهی ویژه و رایگان چیست؟</summary>
            <div class="answer">از لحاظ بازدیدکنندگان و گوگل تفاوتی وجود ندارد، اما هنگامی که آگهی ویژه می‌شود در آن سایت در ردیف‌های بالاتری دیده خواهد شد.</div>
        </details>
    </div>

    <div class="contact-box">
        <div class="section-title" style="margin-bottom:.75rem">تلفن مشاوره تبلیغاتی</div>
        <a href="tel:+982166248174">📞 ۰۲۱-۶۶۲۴۸۱۷۴ (مستقیم)</a>
        <a href="tel:+982191096385">📞 ۰۲۱-۹۱۰۹۶۳۸۵ (داخلی ۳)</a>
        <a href="https://wa.me/989193841239">💬 واتس‌آپ</a>
        <a href="https://t.me/Shetabir">💬 تلگرام</a>
    </div>
</div>
@endsection
