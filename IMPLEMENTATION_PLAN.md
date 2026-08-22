# برنامهٔ پیاده‌سازی نسخهٔ PHP

این برنامه بر اساس `MIGRATION_ANALYSIS.md` و الزامات محصول تدوین شده است. اصل اجرایی، ارائهٔ یک modular monolith مبتنی بر Laravel، Blade SSR، PostgreSQL و استقرار سازگار با cPanel/PHP-FPM است.

| وضعیت | مرحله | خروجی قابل پذیرش |
|---|---|---|
| `تکمیل‌شده` | ۱. Bootstrap و زیرساخت | Laravel 13، config، PostgreSQL schema، نقش‌ها، user با mobile، پایهٔ RTL و health check |
| `تکمیل‌شده` | ۲. taxonomy و locations | category tree، کشور/استان/شهر، settingها و دادهٔ seed |
| `تکمیل‌شده` | ۳. هستهٔ آگهی | schema آگهی، enumها، state machine، تصاویر WebP، duplicate/forbidden-word guard و history |
| `تکمیل‌شده` | ۴. جریان کاربر | ثبت‌نام، ورود، ثبت آگهی، داشبورد کاربر و تیکت |
| `تکمیل‌شده` | ۵. تعدیل و پشتیبانی | مدیریت آگهی، تغییر وضعیت کنترل‌شده، مجوز، گزارش و audit schema |
| `تکمیل‌شده` | ۶. فروش و اعلان | تعرفه، invoice/payment schema، gateway توسعه، SMS contract و cronهای expire/ladder/cleanup |
| `تکمیل‌شده` | ۷. صفحات عمومی و SEO | home، category، search، detail، canonical، robots، sitemap و JSON-LD |
| `تکمیل‌شده` | ۸. تست و آماده‌سازی تولید | ۱۴ آزمون feature/unit با ۴۸ assertion، migration/seed، audit وابستگی، cache production، cron و شبیه‌سازی هاست بدون Redis/worker |

## تصمیم‌های قطعی

- صفحات عمومی با Blade روی سرور render می‌شوند و JavaScript فقط برای تعاملات کوچک به‌کار می‌رود.
- PostgreSQL پایگاه‌دادهٔ production است. SQLite فقط می‌تواند برای testهای ایزوله استفاده شود.
- کلیدهای پولی integer ریال هستند و رویدادهای مالی transaction و idempotency دارند.
- فایل اصلی تصویر هرگز persistent نمی‌شود؛ تنها WebPهای بهینهٔ thumbnail و display نگهداری می‌شوند.
- Secretها در `.env` هستند، نه در repository؛ payment و SMS در development adapter دارند.
- تمام وظایف زمان‌بندی‌شده با Artisan و cron اجرا می‌شوند؛ queue به‌صورت `sync`، cache و session به‌صورت `database` و بدون Redis یا worker دائمی هستند.

## معیار تکمیل مسیر اصلی

کاربر باید بتواند ثبت‌نام کند، آگهی ثبت و تصویر بهینه بارگذاری کند؛ مدیر آگهی را تأیید کند؛ آگهی فعال به‌صورت SSR با SEO کامل منتشر شود؛ کاربر آن را مدیریت کند؛ expiry و renewal در server enforce شود؛ و صفحات جست‌وجو/فیلتر خارج از index بمانند.
