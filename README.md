# سامانهٔ جامع آگهی فارسی — Laravel / PHP

این مخزن بازپیاده‌سازی **server-side rendered** سامانهٔ آگهی فارسی با PHP است. طراحی آن بر سرعت، کنترل indexation، HTML معنایی، امنیت upload و سازگاری با PHP shared hosting تمرکز دارد. specification اولیهٔ مبتنی بر Django بدون تغییر در [`SPECIFICATION_DJANGO_ORIGINAL.md`](SPECIFICATION_DJANGO_ORIGINAL.md) حفظ شده است؛ تصمیم‌های تبدیل معماری در [`MIGRATION_ANALYSIS.md`](MIGRATION_ANALYSIS.md) مستند شده‌اند.

> نسخهٔ فعلی با **Laravel 13 و PHP 8.3+** ساخته شده است. Laravel 13 به حداقل PHP 8.3 نیاز دارد و تا ۱۷ مارس ۲۰۲۸ پشتیبانی امنیتی دریافت می‌کند.[1]

## اصول معماری

این پروژه یک **modular monolith** است. صفحه‌های عمومی با Blade در سرور render می‌شوند؛ بنابراین محتوای اصلی خانه، جست‌وجو، دسته‌بندی و جزئیات آگهی در HTML پاسخ نخست وجود دارد. هیچ SPA، runtime سنگین frontend، worker دائمی یا Redis اجباری به‌کار نرفته است.

| لایه | فناوری / مسئولیت |
|---|---|
| Web | Laravel routes، controller و Blade SSR |
| Domain | `app/Domains` برای آگهی، SEO، پرداخت، اعلان و حساب‌ها |
| Data | PostgreSQL در production؛ migrationهای نسخه‌پذیر Laravel |
| Media | پردازش امن GD و نگهداری WebPهای thumbnail/display |
| Cache | Laravel Cache با driver database یا file |
| Scheduler | Artisan command + cron سازگار با cPanel |

## قابلیت‌های پیاده‌سازی‌شده

| دامنه | موارد موجود |
|---|---|
| حساب کاربری | ثبت‌نام و ورود با موبایل، password hashing، session، rate limiting و نقش‌های user / moderator / support / accounting / super admin |
| طبقه‌بندی | دسته‌بندی درختی، کشور، استان و شهر با slug و indexهای query-oriented |
| آگهی | ثبت آگهی، وضعیت‌های typed، history، duplicate detection، forbidden-word guard، آگهی‌های مرتبط و شمارندهٔ بازدید deduplicated در سطح session |
| تصویر | decode واقعی تصویر، محدودیت حجم/pixel، resize، WebP، thumbnail/display و حذف فایل اصلی upload |
| پنل | داشبورد کاربر، آگهی‌های من، ثبت آگهی، تیکت؛ داشبورد مدیریت و تعدیل وضعیت آگهی |
| تعدیل | pending/active/needs-permit/inactive/expired/deleted، مجوز و گزارش تخلف rate-limited |
| پرداخت و پیامک | قرارداد interface-based، fake gateway و log SMS provider برای توسعه؛ بدون credential واقعی |
| SEO | canonical، robots policy، Open Graph، JSON-LD BreadcrumbList، 301 در تغییر slug، sitemap index و sitemap صفحه‌بندی‌شده |
| عملیات | commandهای expire، auto-ladder و cleanup temp؛ seed دادهٔ توسعه و تست‌های workflow/SEO/normalization |

## شروع سریع توسعه

ابتدا PHP 8.3، Composer، PostgreSQL driver و GD را نصب کنید. برای اجرای sandbox محلی، SQLite نیز قابل استفاده است.

```bash
composer install
cp .env.example .env
# برای توسعهٔ سریع، DB_CONNECTION=sqlite و DB_DATABASE=/مسیر/کامل/database.sqlite را تنظیم کنید.
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

دادهٔ seed شامل یک مدیر توسعه با موبایل `09120000000` و گذرواژه `ChangeMe123!` است. این credential فقط برای محیط محلی است و پیش از هر استقرار باید حذف یا تغییر کند.

## تست و کنترل کیفیت

```bash
php artisan test --compact
php artisan view:cache
php artisan route:cache
php artisan optimize
```

آزمون‌ها شامل normalizer فارسی، state transition آگهی، صفحهٔ اصلی SSR و policyهای canonical / robots / sitemap هستند. در production دستور `php artisan optimize` config، event، route و view cache را ایجاد می‌کند.[2]

## ساختار پروژه

```text
app/
├── Console/Commands/       # cron-safe Artisan commands
├── Domains/
│   ├── Ads/                # workflow، image pipeline، duplicate و moderation guard
│   ├── Billing/            # payment contract و development gateway
│   ├── Notifications/      # SMS contract و idempotency
│   └── Seo/                # canonical، robots و metadata policy
├── Http/                   # controller، middleware و Form Request
├── Models/                 # Eloquent models
├── Policies/               # authorization server-side
└── Support/                # Persian normalizer
```

## deployment

برای production، document root باید فقط `public/` باشد. Laravel هشدار می‌دهد انتقال `index.php` به ریشهٔ پروژه می‌تواند فایل‌های حساس را در معرض اینترنت قرار دهد.[2] دستور کامل cPanel/PHP-FPM، cron، backup و متغیرهای environment در [`DEPLOYMENT.md`](DEPLOYMENT.md) آمده است.

## مستندات تکمیلی

| فایل | محتوا |
|---|---|
| [`MIGRATION_ANALYSIS.md`](MIGRATION_ANALYSIS.md) | تحلیل دقیق منبع و معماری مقصد PHP |
| [`IMPLEMENTATION_PLAN.md`](IMPLEMENTATION_PLAN.md) | برنامه و وضعیت مراحل اجرا |
| [`DEPLOYMENT.md`](DEPLOYMENT.md) | استقرار، cron، backup و عیب‌یابی |
| [`SPECIFICATION_DJANGO_ORIGINAL.md`](SPECIFICATION_DJANGO_ORIGINAL.md) | نیازمندی اصلی بدون تغییر |
| [`analysis/reference_pages_notes.md`](analysis/reference_pages_notes.md) | یافته‌های صفحات مرجع |

## منابع

[1]: https://laravel.com/docs/13.x/releases "Laravel 13 Release Notes"
[2]: https://laravel.com/docs/13.x/deployment "Laravel 13 Deployment"
