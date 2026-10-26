# ارزیابی آمادگی برای هاست اشتراکی بدون Redis

## نتیجهٔ اجرایی

**بله؛ فرانت فعلی قابل ادامه است.** این رابط بر پایهٔ Blade و HTML سمت‌سرور ساخته شده، CSS آن یک فایل static مستقل در `public/assets/app.css` است و برای رندر صفحه‌های اصلی، جست‌وجو، دسته‌بندی و آگهی به Node.js، Vite dev server، SPA یا API سمت مرورگر وابسته نیست. بنابراین برای cPanel/PHP-FPM سبک‌تر و مناسب‌تر از یک frontend جداگانه است.

## وابستگی‌های عملیاتی انتخاب‌شده

| قابلیت | انتخاب نهایی برای هاست | دلیل |
|---|---|---|
| Cache | `database` | جدول cache با migration موجود است؛ به daemon یا extension خاص نیاز ندارد. |
| Session | `database` | بین چند process PHP سازگار و قابل پاک‌سازی است. |
| Queue | `sync` | هیچ worker دائمی لازم نیست؛ کارهای کوتاه در همان request انجام می‌شوند. |
| زمان‌بندی | `php artisan schedule:run` از cron cPanel | فقط یک process کوتاه‌مدت در هر دقیقه اجرا می‌شود؛ daemon نیست. |
| Storage | disk محلی public + WebP بهینه | با filesystem رایج shared hosting کار می‌کند. |
| Database | PostgreSQL | transaction، constraint و indexهای موردنیاز را فراهم می‌کند. |

وجود گزینه‌های Redis در فایل‌های استاندارد Laravel به معنای وابستگی runtime نیست. در محیط عملیاتی این گزینه‌ها انتخاب نمی‌شوند، اتصال Redis ایجاد نمی‌شود و هیچ command worker یا daemon در فرایند استقرار وجود نخواهد داشت.

## اصلاح‌های لازم در تکمیل

پیکربندی queue از `database` به `sync` تغییر می‌کند تا هیچ job بدون worker باقی نماند. cache و session database حفظ می‌شوند. وظایف انقضا، نردبان و cleanup با commandهای idempotent و cron اجرا می‌شوند. طراحی این مسیر به این معناست که کارهای سنگین یا ارسال‌های انبوه SMS در یک request web انجام نمی‌شوند؛ آن‌ها فقط از cron با batch محدود عبور می‌کنند.

## ریسک‌ها و کنترل‌ها

| ریسک هاست اشتراکی | کنترل در پروژه |
|---|---|
| محدودیت CPU و timeout | pagination سمت‌سرور، batch کوچک برای commandها، query index و نبود dependency سنگین frontend |
| نبود Redis | database cache/session و invalidation مبتنی بر key/version |
| نبود worker | queue sync و cron کوتاه‌مدت idempotent |
| محدودیت filesystem | تولید فقط نسخه‌های WebP thumbnail/display؛ حذف اصل تصویر |
| استقرار با document root نامناسب | الزام صریح `public/` در `DEPLOYMENT.md` |

## معیار پذیرش تکمیلی

نسخهٔ نهایی باید با `CACHE_STORE=database`، `SESSION_DRIVER=database` و `QUEUE_CONNECTION=sync` تست شود؛ `schedule:run` باید بدون process دائمی اجرا شود؛ همهٔ صفحه‌های public باید بدون JavaScript محتوای اصلی را داشته باشند؛ و cache/config/route/view در deployment بدون Redis قابل build باشند.
