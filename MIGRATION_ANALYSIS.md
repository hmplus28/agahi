# تحلیل مهاجرت سامانهٔ آگهی به PHP

**تهیه‌کننده:** Manus AI  
**تاریخ:** ۲۰ اوت ۲۰۲۶  
**وضعیت:** مبنای اجرای بازنویسی

## نتیجهٔ بررسی اولیه

مخزن فعلی **کد اجرایی، پایگاه‌داده، migration، asset یا فایل پیکربندی ندارد** و فقط یک سند نیازمندی بسیار کامل در `README.md` نگه می‌دارد. بنابراین، این کار «تبدیل کد Django به PHP» نیست؛ بلکه **بازپیاده‌سازی کنترل‌شدهٔ محصول (greenfield rewrite)** با حفظ قواعد کسب‌وکار، قراردادهای URL، اهداف SEO و الزامات کارایی است. این تمایز مهم است: تبدیل خط‌به‌خط View یا Model وجود ندارد و باید به‌جای آن یک مدل دامنه، schema و چرخه‌های کاری معتبر در PHP ایجاد شود.

صفحه‌های مرجع نیز صرفاً رفتار مورد انتظار را نشان می‌دهند. صفحهٔ عمومیِ مرجع دارای انتخاب شهر، جست‌وجوی دسته/بازهٔ قیمت، ثبت آگهی، پنل کاربر و کارت آگهی است؛ پنل مرجع نیز تب‌های تعدیل وضعیت، جست‌وجو، سرویس‌ها، مجوز و عملیات مدیریت را تأیید می‌کند. UI جدید این صفحات را کپی نمی‌کند؛ بلکه با HTML معنایی، RTL، طراحی mobile-first و CSS کم‌حجم جایگزین خواهد کرد.

| حوزه | وضعیت موجود | تصمیم مهاجرت |
|---|---|---|
| پیاده‌سازی | فقط specification؛ هیچ کد یا schema عملیاتی موجود نیست | بازپیاده‌سازی با پوشش test و migration از روز نخست |
| معماری قبل | Django modular monolith با Django Templates | Laravel modular monolith با Blade و SSR |
| runtime استقرار | Python/WSGI/Passenger روی cPanel | PHP 8.3+ با Apache + PHP-FPM یا FPM compatible cPanel |
| database هدف | PostgreSQL | PostgreSQL؛ SQL و indexها با migrationهای Laravel |
| UI | نمونه‌های قدیمی HTML | Blade SSR، RTL، semantic HTML، CSS محلی کم‌حجم و JavaScript حداقلی |
| دادهٔ فعلی | export، schema یا credential تحویل نشده است | import idempotent پس از دریافت export؛ فعلاً seed دادهٔ توسعه |

## معماری مقصد

پایهٔ مناسب این بازنویسی **Laravel 13 با PHP 8.3 یا بالاتر** است. Laravel 13 حداقل PHP 8.3 را نیاز دارد و برنامهٔ انتشار آن تا ۱۷ مارس ۲۰۲۸ پشتیبانی امنیتی دارد؛ ازاین‌رو انتخابی قابل نگهداری برای بازنویسی جدید است.[1] این انتخاب به معنی ساخت SPA یا وابستگی به سرویس دائمی نیست. پاسخ‌های عمومی با Blade روی سرور تولید می‌شوند و مرورگر در پاسخ نخست، HTML کامل و قابل خزیدن دریافت می‌کند.

> **اصل تصمیم:** یک modular monolith مبتنی بر Laravel اجرا می‌شود؛ نه microservice، نه API-first، نه صف و worker اجباری، نه Redis اجباری و نه فرآیند Node.js در production.

ساختار هدف به‌صورت زیر است. هر دامنه مدل، request validation، policy، service و controllerهای خود را دارد؛ اما application واحد می‌ماند تا با میزبانی اشتراکی سازگار باشد.

```text
app/
├── Domains/
│   ├── Accounts/        # User، Profile، ورود و کنترل دسترسی
│   ├── Ads/             # Ad، Image، Link، workflow، views و renewals
│   ├── Taxonomy/        # Category tree
│   ├── Locations/       # Country، Province، City
│   ├── Moderation/      # status history، permit، forbidden words، reports
│   ├── Billing/         # tariffs، orders، invoices، payments و gateway contract
│   ├── Support/         # tickets و messages
│   ├── Notifications/   # SMS contract، log و cron-safe delivery
│   └── Seo/             # policy، canonical، sitemap و structured data
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   ├── User/
│   │   └── Admin/
│   └── Middleware/
├── Support/             # Persian normalization، cache keys و shared value objects
└── View/Components/     # cards، breadcrumb و meta components
```

| لایه | مسئولیت | ممنوعیت طراحی |
|---|---|---|
| Route / Controller | تطبیق URL، authorization، response و pagination | قرار دادن workflow یا SQL پیچیده در controller |
| Form Request | اعتبارسنجی ورودی و پیام فارسی | اعتماد به اعتبارسنجی client-side |
| Domain Service | state transition، صورت‌حساب، پرداخت، image pipeline و SEO policy | وابستگی مستقیم به View یا global state |
| Model / Query scope | relationها، constraintها و queryهای پایه | N+1، query در loop و eager loading بی‌هدف |
| Blade | HTML اولیه، metadata و accessibility | منطق کسب‌وکار یا رندر محتوای اصلی بعد از API request |

## نگاشت مفاهیم Django به Laravel

| نیاز سند قبلی | معادل PHP / Laravel | نکتهٔ اجرایی |
|---|---|---|
| Django custom user | مدل `User` سفارشی با `mobile` یکتا و Laravel auth | ورود اصلی با موبایل و گذرواژه؛ ایمیل اختیاری |
| Django migrations | Laravel migrations | تمام indexها و constraintهای مهم در schema ثبت می‌شوند |
| TextChoices | PHP backed enum | وضعیت آگهی، پرداخت، تیکت، مجوز و نوع سرویس enum می‌شوند |
| `transaction.atomic()` | `DB::transaction()` | renew، verify-payment و تغییرات چندجدولی اتمیک‌اند |
| `select_for_update()` | `lockForUpdate()` | جلوگیری از race در پرداخت، تمدید و نردبان |
| Django permissions/groups | Laravel gates، policies و roles | هر عمل حساس در سرور enforce می‌شود |
| management commands | Artisan commands + cPanel cron | commandها idempotent، loggable و قابل اجرای دستی‌اند |
| Django cache abstraction | Laravel Cache | file/database cache در فاز نخست؛ Redis افزونهٔ اختیاری آینده |

## مدل داده و invariants

مدل داده از راهبرد «حفظ داده، تغییر وضعیت با تاریخچه» استفاده می‌کند. آگهی و locationهایی که به دادهٔ واقعی متصل‌اند hard-delete نمی‌شوند. آگهی دارای کد عمومی یکتا، `slug` قابل تغییر، زمان‌های UTC، عنوان/توضیح normalized، hashهای SHA-256 و تاریخچهٔ وضعیت است. وضعیت‌ها شامل `draft`، `pending_approval`، `active`، `needs_permit`، `inactive`، `expired` و `deleted` هستند؛ فقط transitionهای مجاز در یک service مرکزی اجرا می‌شوند.

| گروه جدول | جدول‌های کلیدی | قیدها و indexهای کلیدی |
|---|---|---|
| حساب | `users`, `profiles` | `users.mobile` یکتا؛ mobile normalized؛ ایمیل اختیاری یکتا |
| طبقه‌بندی و مکان | `categories`, `countries`, `provinces`, `cities` | slug یکتا در scope مناسب؛ parent id برای tree نامحدود |
| آگهی | `ads`, `ad_images`, `ad_links`, `ad_status_histories` | `code` یکتا؛ `(status,published_at)`، `(status,category_id)`، `(status,city_id)`، `(category_id,city_id,status)`، `(user_id,status)` و `(expires_at,status)` |
| تعدیل | `forbidden_words`, `ad_permits`, `ad_reports`, `audit_logs` | word normalized و index؛ history append-only |
| مالی | `tariffs`, `ad_services`, `orders`, `order_items`, `invoices`, `invoice_items`, `payments` | invoice number یکتا؛ gateway authority یکتا در scope مناسب؛ monetary values integer ریال |
| پشتیبانی و اعلان | `tickets`, `ticket_messages`, `sms_logs` | ownership در policy؛ idempotency key برای SMS و callback |

جست‌وجوی اولیه با PostgreSQL اجرا می‌شود و برای عنوان، توضیح، نام کسب‌وکار، دسته و شهر، scopeهای محدود و normalized دارد. جست‌وجوی `code` و موبایل exact/prefix است. فعال‌سازی `pg_trgm` یک بهینه‌سازی اختیاری پس از بررسی سطح دسترسی هاست است؛ هستهٔ جست‌وجو بدون آن نیز کار می‌کند.

## کارایی و بهینه‌سازی منابع

هدف‌های داخلی محصول—LCP حداکثر ۲ ثانیه، INP حداکثر ۱۵۰ میلی‌ثانیه و CLS حداکثر ۰٫۰۵—با معماری SSR، محدودکردن query و رسانهٔ بهینه دنبال می‌شود. بخش‌های cacheable مانند درخت دسته‌ها، تنظیمات سایت، فهرست مکان‌ها، کارت‌های صفحهٔ اصلی و sitemap از cache file/database با invalidation دامنه‌ای استفاده می‌کنند؛ بنابراین Redis پیش‌نیاز deployment نیست.

تصاویر پس از upload فقط به‌طور موقت نگهداری می‌شوند. نوع MIME و decode واقعی بررسی، orientation EXIF اصلاح، metadata غیرضروری حذف، ابعاد و pixel count محدود و خروجی به WebP تبدیل می‌شود. فقط نسخهٔ thumbnail حدود ۴۸۰ پیکسل و display حدود ۱۲۸۰ پیکسل باقی می‌ماند؛ فایل اصلی حذف می‌شود. `width` و `height` در HTML، lazy loading برای رسانه‌های پایین صفحه و `fetchpriority="high"` برای تصویر LCP جلوی layout shift و انتقال غیرضروری داده را می‌گیرند.

Laravel برای استقرار تولیدی cache کردن config، route و Blade view را فراهم می‌کند؛ اجرای `php artisan optimize` در فرایند deployment شامل این cacheها است.[2] این مرحله همراه با OPcache فعال در هاست، cache-control بلندمدت برای assetهای versioned، فشرده‌سازی وب‌سرور و جلوگیری از dependencyهای سنگین frontend، مبنای کارایی خواهد بود.

## SEO و قرارداد URL

همهٔ URLهای عمومی در پاسخ اول server-side render می‌شوند. جزئیات آگهی با ساختار پایدار `/ad/{code}/{slug}/` خواهد بود؛ تغییر slug، با حفظ code، به canonical جدید **301** می‌شود. landingهای مکان و دسته URLهای خوانا دارند، اما تمام ترکیب‌های فیلتر و شهر به‌طور خودکار indexable نمی‌شوند.

| نوع صفحه | ایندکس‌پذیری | canonical و نشانه‌گذاری |
|---|---|---|
| صفحهٔ اصلی، آگهی فعال، landing ارزشمند مکان/دسته | `index, follow` | canonical خود صفحه، H1 یکتا، Open Graph و BreadcrumbList |
| صفحهٔ جست‌وجو و فیلتر دلخواه | `noindex, follow` | canonical policy-based؛ query کاربر escaped |
| ورود، ثبت‌نام، داشبورد، پرداخت، تیکت و مدیریت | `noindex, nofollow` یا private | دسترسی احراز هویت‌شده؛ خارج از sitemap |
| پیش‌نویس، pending، deleted و expired پیش‌فرض | public نیست یا `noindex, follow` | هرگز داخل sitemap قرار نمی‌گیرد |

سرویس واحد `SeoPolicy` مرجع نهایی برای robots directive، canonical، indexability، title، meta description و inclusion در sitemap است. `robots.txt` فقط مسیرهای خصوصی را disallow می‌کند و صفحه‌ای که باید meta `noindex` آن توسط crawler خوانده شود را به اشتباه block نمی‌کند. sitemap index و sitemapهای صفحه‌بندی‌شده از query محدود/cache شده تولید می‌شوند؛ نه با اسکن کامل database در هر درخواست.

## امنیت و عملیات حساس

تمام POSTها CSRF دارند؛ upload با extension تصمیم‌گیری نمی‌شود؛ نام فایل تصادفی است؛ خطاهای تولیدی جزئیات داخلی را نمایش نمی‌دهند؛ و rate limit برای ورود، ثبت‌نام، بازیابی گذرواژه، گزارش، تیکت و آشکارسازی شماره اعمال می‌شود. مقدارهای مالی با integer ریال ذخیره می‌شوند، callbackهای payment idempotent هستند و activation service به همراه invoice/payment در یک transaction اجرا می‌شود. هیچ secret یا credential پرداخت/SMS در repository ذخیره نمی‌شود.

| عامل بیرونی | قرارداد در نسخهٔ اول | رفتار توسعه |
|---|---|---|
| درگاه پرداخت | `PaymentGateway` با create و verify | adapter توسعه، بدون credential واقعی و قابل تست |
| سرویس پیامک | `SmsProvider` با send و idempotency key | ثبت event در log؛ بدون ارسال پیام واقعی |
| cache | Laravel Cache | database/file driver؛ قابل ارتقا به Redis/Valkey |
| storage | Laravel filesystem | disk محلی؛ قرارداد قابل تغییر برای object storage/CDN |
| background work | Artisan + cPanel cron | بدون daemon؛ commandهای ایمن و idempotent |

## مسیر مهاجرت داده و استقرار

چون export واقعی هنوز در دسترس نیست، import واقعی در این مرحله قابل انجام نیست؛ اما structure آن آماده می‌شود. در زمان دریافت داده، یک mapping versioned از جداول/CSV/JSON مبدأ به staging table اجرا می‌شود؛ موبایل و متن فارسی normalize می‌شوند؛ foreign keyها بر اساس lookupهای صریح resolve می‌شوند؛ media ابتدا به pipeline امن وارد می‌شود؛ سپس integrity report (ردیف‌های واردشده/ردشده/تکراری/بدون وابستگی) تولید می‌شود. importها با natural key یا `legacy_id` قابل اجرای مجدد می‌مانند.

در production، document-root فقط باید به پوشهٔ `public/` اشاره کند؛ قرار دادن `index.php` در ریشهٔ پروژه فایل‌های حساس را در معرض وب قرار می‌دهد.[2] استقرار مشترک PHP با Apache rewrite به `public/index.php` یا تنظیم document root از cPanel پشتیبانی می‌شود. مسیرهای قابل‌نوشتن فقط `storage/` و `bootstrap/cache/` هستند؛ PHP، Composer، PostgreSQL و extensionهای موردنیاز باید در hosting موجود باشند.

## توالی پیاده‌سازی

پیاده‌سازی به ترتیب زیر انجام می‌شود تا نخستین مسیر ارزشمند محصول زودتر قابل تست باشد و هر مرحله با test و schema check تثبیت شود.

1. Bootstrap، config، PostgreSQL، user مبتنی بر mobile، پایهٔ RTL، نقش‌ها و health check.
2. Category tree، country/province/city، settingهای سایت و seedهای توسعه.
3. مدل آگهی، state machine، image pipeline، duplicate detection، forbidden-word guard و history.
4. احراز هویت، ثبت و ویرایش آگهی، پنل کاربر و عملیات مجاز هر وضعیت.
5. dashboard مدیریت، moderation، permit، report و audit log.
6. تعرفه، order، invoice، payment adapter و renewal/ladder.
7. ticket، SMS contract، commandهای expire/ladder/notification/cleanup.
8. home، listing، search، detail، صفحات error و پاسخ‌گویی mobile.
9. canonical، robots، sitemap، JSON-LD، performance tuning، testها و deployment docs.

## وابستگی‌ها و اطلاعات باقی‌مانده

برای تکمیل production تنها اطلاعاتی که عمداً با adapter توسعه جایگزین می‌شوند عبارت‌اند از credential درگاه، provider پیامک، دامنهٔ نهایی، لوگو/brand و دادهٔ واقعی. هیچ‌کدام مانع ساخت هستهٔ کاربردی، test یا UI نمی‌شوند. از آنجا که مخزن جدید پس از اتمام معرفی خواهد شد، توسعه در همین clone محلی انجام می‌شود و به remote جدید push نخواهد شد.

## منابع

[1]: https://laravel.com/docs/13.x/releases "Laravel 13 Release Notes"
[2]: https://laravel.com/docs/13.x/deployment "Laravel 13 Deployment"
