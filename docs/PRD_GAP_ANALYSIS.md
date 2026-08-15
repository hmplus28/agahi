# ماتریس تطبیق سند جامع و وضعیت پیاده‌سازی

**منبع تطبیق:** `سندجامعسامانهآگهی.pdf` و `README.md` پروژه  
**تاریخ ممیزی:** ۱۵ اوت ۲۰۲۶  
**شاخه:** `completion/stabilize-classified-ads`

## مبنای ارزیابی

پروژه در حال حاضر دارای معماری ماژولار Django، مدل کاربر سفارشی، مدل‌های اصلی آگهی و صورت‌حساب، pipeline تبدیل تصویر به WebP، جست‌وجوی فارسی با fallback، چرخهٔ وضعیت آگهی، صفحات عمومی SSR، و تعدادی command زمان‌بندی‌شدنی است. اجرای baseline در محیط آزمون نشان داد **۱۲ آزمون موجود با موفقیت اجرا می‌شوند** و `manage.py check` خطایی ندارد. این نتیجه فقط سلامت حداقلی کد موجود را نشان می‌دهد؛ پوشش الزامات سند هنوز کامل نیست.

| حوزه | وضعیت فعلی | شکاف یا اقدام تکمیلی |
|---|---|---|
| معماری، Django، SSR و PostgreSQL | هستهٔ ماژولار و تنظیمات پایه آماده است. | باقی‌ماندن توسعه‌ها باید بدون SPA، worker دائمی یا وابستگی الزامی به Redis انجام شود. |
| مدل آگهی و چرخه‌عمر | مدل گسترده، hash نرمال‌شده، soft delete و تاریخچهٔ وضعیت وجود دارد. | URL عمومی باید به `code/slug` پایدار تبدیل شود؛ انتقال‌های تمدید و بازگردانی باید صریح و atomically پشتیبانی شوند؛ قیود پایگاه‌دادهٔ تکمیلی لازم است. |
| ثبت و ویرایش آگهی | فرم اصلی، انتخاب مکان وابسته، تصاویر چندگانه و تشخیص duplicate در ایجاد وجود دارد. | اعتبارسنجی کلمات ممنوعه به فرم متصل نیست؛ duplicate در ویرایش کنترل نمی‌شود؛ فرم مدیریت لینک و مجوز وجود ندارد؛ محدودیت‌ها باید از تنظیمات خوانده شوند. |
| تصویر و مجوز | نسخهٔ thumbnail/display WebP، EXIF correction و محدودیت حجم/پیکسل پیاده است. | تصویر مجوز باید با validation و storage محافظت‌شده مدیریت شود؛ constraint تصویر اصلی و آزمون‌های کامل امنیت upload لازم است. |
| صفحات عمومی و پنل کاربر | صفحهٔ خانه، لیست، جست‌وجو، جزئیات و dashboard پایه وجود دارد. | تمدید، ارسال مجوز، مدیریت خدمات، نمایش کامل ستون‌های موردنیاز، و masking کامل داده‌های حساس آگهی منقضی باید تکمیل شود. |
| گزارش تخلف، تیکت و moderation | مدل‌ها و viewهای پایه وجود دارند. | rate limit گزارش و تیکت، permission نقش‌محور، و جریان کامل مدیریت staff باید افزوده شود. |
| مدیریت و Admin | فقط `User` و `Profile` در Django Admin ثبت شده‌اند. | ثبت و مدیریت تخصصی آگهی، وضعیت‌ها، مجوز، دسته، مکان، تعرفه، پرداخت، ticket، report، SMS و تنظیمات؛ جست‌وجوی بهینهٔ code/mobile؛ bulk actionها؛ داشبورد آماری staff. |
| پرداخت | Order، Invoice، Payment، AdService و adapterهای درگاه وجود دارند؛ finalize idempotent است. | backend توسعه/Fake قابل‌تست، hardening رفتار بدون credential، ثبت audit و مدیریت staff باید افزوده شود. هیچ credential واقعی نیاز نیست. |
| SMS | مدل `SMSLog` و command ساخت صف یادآوری وجود دارد. | adapter واقعی/توسعه، ارسال و retry idempotent، template/policy reminder، admin selection دستی و آزمون mock provider لازم است. Credential بعداً از environment متصل می‌شود. |
| SEO policy و metadata | canonical، robots و JSON-LD پایه در templateها وجود دارد؛ search noindex است. | `seo/policies.py` به‌عنوان source of truth، canonical وابسته به صفحه و query، URL code/slug و redirect 301، 404/410 policy، تست SEO. |
| Landingهای SEO | category page پایه وجود دارد. | صفحات SSR مکان و مکان+دسته با URLهای `/tehran/` و `/tehran/industrial/`، محتوا، breadcrumb، internal links و indexability policy لازم است. |
| Sitemap و robots | robots و یک sitemap inline کوچک وجود دارد. | sitemap index، sitemapهای segment شدهٔ ads/categories/locations، cache یا generation زمان‌بندی‌شده، صرفاً URLهای canonical و indexable، و آزمون مقیاس‌پذیری. |
| عملکرد و assetها | GZip، Conditional GET، cache نسخه‌دار، WebP، queryهای بهینه‌شده و static fingerprinting موجود است. | فونت فارسی باید self-hosted WOFF2 شود؛ query-budget test، response header test، performance regression test و بهبود templateهای LCP/CLS لازم است. |
| امنیت و قابلیت مشاهده | تنظیمات production پایه و validation تصویر وجود دارند. | rate limit فرم‌های حساس، logging عملیاتی ساخت‌یافته، audit عملیات حساس، error templateهای کامل 400/403، privacy مجوز و hardening تنظیمات. |
| عملیات و maintenance | commandهای expire، auto ladder و notification queue موجود است. | cleanup session/temp، sitemap generation، seed/import داده‌های دسته و location، و مستندسازی cronها باید تکمیل شود. |
| backup و restore | هیچ command و config عملیاتی برای backup/restore وجود ندارد. | سه مدل موردنیاز: backup محلی، archive رمزنگاری‌شده و ارسال برون‌سایتی، و زمان‌بندی/retention؛ restore اعتبارسنجی‌شده؛ backup media؛ آزمون سناریوهای موفق و خرابی؛ مستند cPanel. |
| تست | ۱۲ آزمون پایه موفق است. | پوشش workflow کامل، permit، SEO، sitemap، redirect، rate limit، admin permission، SMS، backup/restore، و performance باید افزوده شود. |

## اولویت اجرای مصوب

| اولویت | بستهٔ تحویل | دلیل |
|---|---|---|
| P0 | صفحات landing مکان، URLهای پایدار، policy مرکزی SEO، sitemap مقیاس‌پذیر و تست SEO | برای ایندکس‌پذیری درست و جلوگیری از URLهای کم‌ارزش حیاتی است. |
| P0 | مدیریت staff/Admin، مجوز، تمدید و workflowهای کاربر | بخش‌های اصلی محصول و معیار Done را کامل می‌کند. |
| P0 | adapter توسعهٔ SMS و پرداخت، ارسال idempotent و مدیریت عملیات | بدون credential واقعی قابل تست و آمادهٔ اتصال بعدی است. |
| P0 | backup/restore سه‌مدلی، retention، رمزنگاری و مستند cPanel | الزام مستقیم کاربر و شرط بازیابی امن داده است. |
| P1 | rate limiting، audit/logging، import/seed، cleanup و error handling | برای production readiness و بهره‌برداری ایمن لازم است. |
| P1 | فونت self-hosted، تست query budget و performance regression | برای تکمیل الزامات عملکرد و تجربهٔ RTL لازم است. |

## اصول اجرایی

پیاده‌سازی‌های بعدی باید با migrationهای reversible، تست‌های مستقل از سرویس بیرونی، secrets صرفاً در environment variables، و commandهای idempotent سازگار با cron/cPanel انجام شوند. هیچ gateway یا provider واقعی بدون credential یا اقدام حساس فعال نخواهد شد؛ backendهای توسعه صرفاً رفتار قابل‌آزمون و non-delivery خواهند داشت.
