# راهنمای استقرار Production

این سند روش استقرار سامانهٔ PHP را روی میزبانی اشتراکی سازگار با **PHP-FPM یا Apache + PHP 8.3** توضیح می‌دهد. نسخهٔ تولیدی باید از PostgreSQL استفاده کند و هرگز فایل `.env`، credentialها، خروجی log یا رسانهٔ runtime را وارد Git نکند.

## پیش‌نیازها

| مورد | حداقل لازم | دلیل |
|---|---:|---|
| PHP | 8.3 | نیاز Laravel 13 |
| PHP extensions | `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `pdo`, `pdo_pgsql`, `xml`, `gd` | framework، PostgreSQL و بهینه‌سازی تصویر |
| Database | PostgreSQL 14+ | transaction، indexهای ترکیبی و جست‌وجوی مبنایی |
| Cron | هر دقیقه | اجرای scheduler و وظایف idempotent |
| Web root | `public/` | جلوگیری از افشای `.env` و فایل‌های برنامه |

## فرایند استقرار

ابتدا یک database و user محدود PostgreSQL ایجاد کنید و مقادیر آن را فقط در `.env` وارد کنید. سپس repository را بیرون از `public_html` قرار دهید و document root دامنه یا subdomain را به پوشهٔ `public/` تغییر دهید. اگر کنترل‌پنل اجازهٔ تغییر document root نمی‌دهد، فقط محتویات پوشهٔ `public/` را به web root کپی و در `index.php` مسیر bootstrap را به محل واقعی application اصلاح کنید؛ کل repository را در web root قرار ندهید.

```bash
cd /home/ACCOUNT/apps/agahi
composer install --no-dev --prefer-dist --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

مجوز نوشتن وب‌سرور باید فقط برای `storage/` و `bootstrap/cache/` برقرار باشد. پس از هر انتشار، `php artisan optimize` اجرا شود. در صورت تغییر environment variable یا route/view، ابتدا `php artisan optimize:clear` و سپس `php artisan optimize` را اجرا کنید.

## `.env` تولیدی

نمونهٔ حداقلی زیر صرفاً ساختار را نشان می‌دهد؛ مقدارهای واقعی نباید در مستند یا repository قرار گیرند.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.ir
APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agahi
DB_USERNAME=agahi_app
DB_PASSWORD=CHANGE_ME

CACHE_STORE=database
SESSION_DRIVER=database
# بدون daemon یا worker دائمی روی shared hosting
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
FREE_AD_DURATION_DAYS=30
MAX_AD_IMAGES=5
IMAGE_MAX_BYTES=5242880
IMAGE_MAX_PIXELS=24000000
```

## تنظیم Cron

در cPanel یک cron با دورهٔ هر دقیقه تعریف کنید. scheduler فقط commandهای زمان‌رسیده را اجرا می‌کند و commandها از `withoutOverlapping` استفاده می‌کنند.

```bash
* * * * * cd /home/ACCOUNT/apps/agahi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

در صورت نیاز به اجرای دستی و قابل بررسی، commandهای زیر در دسترس‌اند.

```bash
php artisan ads:expire --dry-run
php artisan ads:expire
php artisan ads:process-auto-ladders
php artisan media:cleanup-temp --hours=24
```

## بهینه‌سازی و cache

در production از `APP_DEBUG=true` استفاده نکنید. static assetها با cache-control بلندمدت توسط وب‌سرور یا CDN سرو شوند. cache درخت دسته‌ها و تنظیمات با driver database/file قابل استفاده است و بعداً می‌توان آن را بدون تغییر منطق دامنه به Redis/Valkey منتقل کرد. بررسی کنید OPcache در هاست فعال باشد و PHP-FPM از پوشهٔ `public/` سرو شود.

## پشتیبان‌گیری و بازگردانی

روزانه PostgreSQL و media را جدا پشتیبان‌گیری کنید. قبل از هر migration، snapshot جدید بگیرید.

```bash
pg_dump -Fc -h DB_HOST -U DB_USERNAME DB_DATABASE > backups/agahi-$(date +%F).dump
tar -C storage/app/public -czf backups/agahi-media-$(date +%F).tgz ads
```

برای بازگردانی database ابتدا برنامه را در maintenance mode قرار دهید، سپس فایل dump را با `pg_restore` در database خالی restore کنید و رسانه را به همان مسیر `storage/app/public/` برگردانید. پس از بررسی health route (`/up`) و sitemap، maintenance mode را بردارید.

## بررسی نهایی انتشار

| کنترل | دستور یا نتیجهٔ مورد انتظار |
|---|---|
| migration | `php artisan migrate --force` بدون خطا |
| code quality | `php artisan test --compact` با نتیجهٔ pass |
| config | `php artisan optimize` بدون secret در output |
| SEO | `/robots.txt`، `/sitemap.xml` و صفحهٔ آگهی active پاسخ ۲۰۰ بدهند |
| امنیت | `APP_DEBUG=false`، HTTPS، document root روی `public/` |
| media | `php artisan storage:link` برقرار و فقط نسخه‌های بهینه عمومی باشند |

## عیب‌یابی کوتاه

اگر پاسخ ۵۰۰ دریافت شد، ابتدا `storage/logs/laravel.log` را بررسی کنید و هرگز جزئیات آن را برای بازدیدکننده نمایش ندهید. اگر تصویر نمایش داده نمی‌شود، symlink `public/storage` و permissionهای storage را کنترل کنید. اگر sitemap تهی است، وضعیت آگهی باید `active` و `published_at` آن پر باشد. اگر cron اجرا نمی‌شود، مسیر واقعی PHP در control panel و مسیر مطلق پروژه را دوباره بررسی کنید.

> برای فهرست رسمی نیازمندی‌ها و دستورهای optimization، به [مستند استقرار Laravel 13](https://laravel.com/docs/13.x/deployment) مراجعه کنید.[1]

## منابع

[1]: https://laravel.com/docs/13.x/deployment "Laravel 13 Deployment"
