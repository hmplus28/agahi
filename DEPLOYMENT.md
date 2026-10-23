# 📘 راهنمای استقرار سامانه آگهی روی هاست اشتراکی cPanel (Laravel 13)

این سند مرجع رسمی استقرار پروژه است و بر اساس همان ساختاری تدوین شده که سایت `karafarine.ir` با آن مستقر شده است: هستهٔ Laravel بیرون از `public_html`، فایل `index.php` اصلاح‌شده با `usePublicPath`، آپلود `vendor` و فایل‌های build به‌صورت zip.

## 📋 فهرست

1. [پیش‌نیازها](#۱-پیشنیازها)
2. [تنظیمات اولیه cPanel](#۲-تنظیمات-اولیه-cpanel)
3. [ساختار پوشه‌ها](#۳-ساختار-پوشهها)
4. [تنظیم فایل index.php](#۴-تنظیم-فایل-indexphp)
5. [تنظیم فایل .htaccess](#۵-تنظیم-فایل-htaccess)
6. [تنظیم فایل .env](#۶-تنظیم-فایل-env)
7. [ساخت دیتابیس MySQL](#۷-ساخت-دیتابیس-mysql)
8. [آپلود پوشه vendor](#۸-آپلود-پوشه-vendor)
9. [ساخت و آپلود فایل‌های Vite](#۹-ساخت-و-آپلود-فایلهای-vite)
10. [تنظیم مجوز پوشه‌ها](#۱۰-تنظیم-مجوز-پوشهها)
11. [اجرای مایگریشن‌ها](#۱۱-اجرای-مایگریشنها)
12. [تنظیم Cron Jobs (الزامی)](#۱۲-تنظیم-cron-jobs-الزامی)
13. [درگاه پرداخت و پیامک](#۱۳-درگاه-پرداخت-و-پیامک)
14. [رفع خطاهای رایج](#۱۴-رفع-خطاهای-رایج)
15. [چک‌لیست نهایی](#۱۵-چکلیست-نهایی)
16. [به‌روزرسانی نسخهٔ آینده](#۱۶-بهروزرسانی-نسخهٔ-آینده)

---

## ۱. پیش‌نیازها

| مورد | حداقل نسخه | توضیح |
|------|-----------|-------|
| PHP | 8.3+ | Laravel 13 به PHP 8.3 نیاز دارد (`^8.3` در `composer.json` قفل شده) |
| MySQL | 5.7+ | برای دیتابیس production (MariaDB 10.6+ هم قابل قبول است) |
| Composer | 2.x | روی سیستم لوکال |
| Node.js | 18+ | برای build کردن Vite — فقط روی سیستم لوکال |
| npm | 9+ | همراه Node.js |

### افزونه‌های PHP موردنیاز

`Ctype`, `cURL`, `DOM`, `Fileinfo`, `Filter`, `Hash`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `Session`, `Tokenizer`, `XML`, `GD` (برای پردازش تصاویر آگهی), `Zip`

---

## ۲. تنظیمات اولیه cPanel

### ۲-۱. تغییر نسخه PHP دامنه

1. وارد **cPanel** → **MultiPHP Manager** شوید
2. دامنه مورد نظر را تیک بزنید
3. از منوی کشویی **PHP Version**، نسخه **PHP 8.3 (ea-php83)** را انتخاب کنید
4. روی **Apply** کلیک کنید
5. مطمئن شوید **PHP-FPM** روی حالت **On** است

### ۲-۲. فعال‌سازی افزونه‌ها

1. وارد **Select PHP Version** شوید
2. تمام افزونه‌های ذکر شده در بخش پیش‌نیازها را تیک بزنید

---

## ۳. ساختار پوشه‌ها

### ساختار توصیه‌شده (امن)

هسته Laravel باید **بیرون از `public_html`** قرار بگیرد تا فایل‌های حساس (مثل `.env` و `storage`) از دسترس عموم خارج باشند.

```
/home/USERNAME/
├── public_html/                    ← DocumentRoot دامنه
│   ├── build/                      ← فایل‌های build شده Vite
│   │   ├── manifest.json
│   │   └── assets/
│   ├── assets/
│   ├── fonts/
│   ├── img/
│   ├── favicon.ico
│   ├── index.php                   ← اصلاح شده
│   ├── .htaccess                   ← اصلاح شده
│   └── ...
└── laravel/                        ← هسته Laravel
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/
    │   └── build/                  ← کپی از build (برای @vite)
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env
    ├── artisan
    ├── composer.json
    └── composer.lock
```

### مراحل انتقال

1. یک پوشه به نام `laravel` در **ریشه هاست** بسازید
2. تمام محتویات پروژه به جز پوشه `public` را به `laravel` منتقل کنید
3. **محتویات** پوشه `public` را در `public_html` قرار دهید (نه خود پوشه public)

> ✅ **نکته:** فایل‌های کامپایل‌شدهٔ CSS/JS (پوشهٔ `public/build`) و فونت وزیرمتن داخل ریپو کامیت شده‌اند؛ پس با خود کد، فایل‌های build هم می‌آیند و روی هاست به npm نیازی نیست.

---

## ۴. تنظیم فایل `index.php`

فایل `public_html/index.php` را با محتوای زیر جایگزین کنید (نسخهٔ آمادهٔ این فایل در مسیر `deploy/cpanel/index.php` ریپو هست — کافی است کپی کنید):

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
```

**نکته کلیدی:** خط `$app->usePublicPath(__DIR__);` به Laravel می‌گوید مسیر public همان `public_html` است و فایل `manifest.json` را از آنجا بخواند.

---

## ۵. تنظیم فایل `.htaccess`

فایل `public_html/.htaccess` را با محتوای زیر جایگزین کنید (نسخهٔ آماده در `deploy/cpanel/htaccess` ریپو موجود است — `USERNAME` را با یوزر cPanel خود جایگزین کنید). ریدایرکت‌های دامنه‌های دیگر را حفظ کنید:

```apache
RewriteEngine on

# BEGIN cPanel-generated php ini directives, do not edit
<IfModule php8_module>
   php_value error_log "/home/USERNAME/logs/php.error.log"
   php_flag log_errors On
</IfModule>
<IfModule lsapi_module>
   php_value error_log "/home/USERNAME/logs/php.error.log"
   php_flag log_errors On
</IfModule>
# END cPanel-generated php ini directives, do not edit

# ریدایرکت‌های دامنه‌های دیگر (در صورت وجود)
# RewriteCond %{HTTP_HOST} ^example\.ir$
# RewriteRule ^/?$ "https://target.ir/" [R=301,L]

# BEGIN Laravel
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
# END Laravel

# php -- BEGIN cPanel-generated handler, do not edit
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php83 .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit
```

---

## ۶. تنظیم فایل `.env`

فایل `laravel/.env` را با محتوای زیر جایگزین کنید (مقادیر را با اطلاعات واقعی جایگزین کنید):

```env
APP_NAME="سامانه آگهی"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://yourdomain.ir

SANCTUM_STATEFUL_DOMAINS=yourdomain.ir,www.yourdomain.ir

APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa
APP_FAKER_LOCALE=fa_IR

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=USERNAME_dbname
DB_USERNAME=USERNAME_dbuser
DB_PASSWORD=رمز_واقعی_دیتابیس

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.yourdomain.ir

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync

CACHE_STORE=database

# ─── درگاه پرداخت (صاد — بانک ملی) ─────────────────────────
PAYMENT_GATEWAY=sadad
SADAD_MERCHANT_ID=
SADAD_TERMINAL_ID=
SADAD_TRANSACTION_KEY=

# ─── پیامک (IPPanel) ────────────────────────────────────────
SMS_PROVIDER=ippanel
IPPANEL_API_KEY=
IPPANEL_BASE_URL=https://edge.ippanel.com/v1
SMS_SENDER_NUMBER=
SMS_PATTERN_OTP=gbg6a9k9zw
SMS_PATTERN_EXPIRY=

# ─── تنظیمات آگهی ───────────────────────────────────────────
FREE_AD_DURATION_DAYS=30
MAX_AD_IMAGES=5
MAX_AD_LINKS=5
```

### نکات مهم `.env`

| کلید | توضیح |
|------|-------|
| `APP_ENV` | در production حتماً `production` باشد |
| `APP_DEBUG` | حتماً `false` باشد (برای امنیت) |
| `APP_KEY` | نباید خالی باشد — با `php artisan key:generate --show` بسازید؛ **پس از اولین استقرار دیگر تغییرش ندهید** (امضای callback پرداخت به آن وابسته است) |
| `APP_URL` | حتماً با `https://` باشد و دقیقاً برابر دامنهٔ واقعی |
| `DB_DATABASE` | نام کامل با پیشوند cPanel |
| `DB_PASSWORD` | رمز واقعی کاربر MySQL |
| `PAYMENT_GATEWAY` | `sadad` برای درگاه واقعی؛ `fake` فقط برای توسعهٔ محلی |
| `SMS_PROVIDER` | `ippanel` در تولید؛ `log` پیامک را فقط در لاگ می‌نویسد |

---

## ۷. ساخت دیتابیس MySQL

1. cPanel → **MySQL Databases**
2. **Create New Database** → نام دیتابیس (مثلاً `agahi`) → cPanel پیشوند اضافه می‌کند
3. **Add New User** → نام کاربر + رمز قوی → **Create User**
4. **Add User To Database** → انتخاب کاربر + دیتابیس → **ALL PRIVILEGES** → **Make Changes**

---

## ۸. آپلود پوشه `vendor`

چون در production نباید پکیج‌های dev نصب باشند، از `--no-dev` استفاده می‌کنیم.

### راه میان‌بُر (اسکریپت آماده)

اسکریپت `scripts/package-cpanel.sh` در ریپو، هر دو بستهٔ `vendor.zip` و `build.zip` را با یک اجرا می‌سازد:

```bash
bash scripts/package-cpanel.sh
```

### روی سیستم لوکال (روش دستی)

```bash
cd ~/Downloads/agahi
rm -rf vendor
composer install --no-dev --optimize-autoloader
zip -r vendor.zip vendor
```

### روی سرور

1. فایل `vendor.zip` را در `laravel/` آپلود کنید
2. Extract کنید
3. فایل zip را حذف کنید
4. مطمئن شوید `laravel/vendor/autoload.php` وجود دارد

---

## ۹. ساخت و آپلود فایل‌های Vite

> ⚠️ **مهم:** پوشهٔ `public/build` داخل ریپو کامیت شده است. اگر کد را از گیت گرفته‌اید، build جدید همراه کد است و فقط باید آن را در دو مسیر زیر آپلود کنید. اگر خودتان بیلد می‌گیرید، **حتماً بعد از آخرین `git pull` و روی سورس جدید** بیلد بگیرید.

### روی سیستم لوکال

```bash
cd ~/Downloads/agahi
npm install
npm run build
cd public
zip -r build.zip build
```

### روی سرور

پوشه `build` را در **دو مسیر** قرار دهید:

1. `/home/USERNAME/public_html/build/` (برای دسترسی مرورگر)
2. `/home/USERNAME/laravel/public/build/` (برای `@vite`)

> 💡 هنگام Extract روی فایل‌های قبلی، پوشهٔ `build` قبلی را اول پاک کنید — اگرچه نام فایل‌ها هش‌دار است، ولی باقی‌ماندن فایل‌های قدیمی فضای هاست را اشغال می‌کند.

### چطور بفهمیم نسخهٔ build روی هاست قدیمی است؟

سورس صفحه را باز کنید (`Ctrl+U`) و نام فایل CSS را ببینید:

```html
<link rel="stylesheet" href="https://yourdomain.ir/build/assets/app-XXXXXXXX.css" />
```

این نام باید **دقیقاً** با `public/build/manifest.json` در ریپو یکی باشد. مثلاً نسخهٔ فعلی ریپو `app-BfQ8eVmi.css` است؛ اگر سایت نام قدیمی‌تری (مثل `app-nWBB9O1y.css` یا `app-CO4fwPko.css`) سرو می‌کند یعنی build قدیمی روی هاست مانده و باید از ریپو دوباره آپلود شود. بعد از تعویض فایل‌ها هم یک‌بار `Ctrl+Shift+R` بزنید یا کش CDN را Purge کنید.

### اصلاح `composer.json` برای جلوگیری از خطای پکیج‌های dev

فایل `laravel/composer.json` را ویرایش کنید:

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "laravel/pail",
            "laravel/pao",
            "nunomaduro/collision"
        ]
    }
}
```

**دلیل:** این پکیج‌ها dev-only هستند و در production نصب نمی‌شوند، ولی Laravel تلاش می‌کند آن‌ها را بارگذاری کند و خطای `Class not found` می‌دهد.

> ✅ این تغییر **از قبل در ریپو اعمال شده** — اگر از آخرین نسخهٔ ریپو استفاده می‌کنید، نیازی به ویرایش دستی نیست.

---

## ۱۰. تنظیم مجوز پوشه‌ها

پوشه‌های زیر باید مجوز **775** داشته باشند:

- `laravel/storage`
- `laravel/bootstrap/cache`

**نحوه:** File Manager → راست‌کلیک روی پوشه → **Change Permissions** → عدد `775` → تیک **"Apply to all subfolders/files"**

---

## ۱۱. اجرای مایگریشن‌ها

### سناریو الف: دسترسی به Terminal در cPanel

```bash
cd ~/laravel
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### سناریو ب: بدون دسترسی Terminal (روش SSH Tunnel یا mysqldump)

**روش ۱ — SSH Tunnel:**

```bash
# روی مک/لینوکس لوکال
ssh -L 3307:127.0.0.1:3306 USERNAME@SERVER_IP -p 22 -N
```

در ترمینال دوم، `.env` لوکال را به `DB_PORT=3307` تغییر دهید و اجرا کنید:

```bash
php artisan migrate --force
```

**روش ۲ — mysqldump (توصیه‌شده برای محیط‌های سخت‌گیر):**

1. MySQL لوکال نصب کنید:
   ```bash
   sudo apt install mysql-server -y
   sudo systemctl start mysql
   ```

2. دیتابیس و کاربر لوکال بسازید:
   ```sql
   CREATE DATABASE agahi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'agahi'@'localhost' IDENTIFIED BY 'agahi';
   GRANT ALL PRIVILEGES ON agahi.* TO 'agahi'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. `.env` لوکال را به MySQL لوکال وصل کنید و مایگریشن بزنید:
   ```bash
   php artisan migrate --force
   ```

4. Dump بگیرید:
   ```bash
   mysqldump -u agahi -pagahi --no-tablespaces --skip-comments \
     --set-gtid-purged=OFF --default-character-set=utf8mb4 \
     agahi > ~/Downloads/agahi_export.sql
   ```

5. فایل را zip کنید و از طریق **phpMyAdmin** سرور → **Import** آپلود کنید.

> ⚠️ `php artisan storage:link` را یادتان نرود — بدون آن تصاویر آگهی‌ها 404 می‌شوند.

---

## ۱۲. تنظیم Cron Jobs (الزامی)

برنامه‌های زمان‌بندی‌شدهٔ سامانه فقط با یک کرون اجرا می‌شوند. در cPanel → **Cron Jobs** این دستور را ثبت کنید:

```
* * * * * cd /home/USERNAME/laravel && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

> مسیر دقیق PHP را می‌توانید با دستور `which php` در Terminal (یا از هاستینگ بپرسید) پیدا کنید.

این یک کرون به‌صورت خودکار این کارها را زمان‌بندی می‌کند:

| ساعت | دستور | کار |
|---|---|---|
| هر ساعت | `ads:expire` | منقضی‌کردن آگهی‌های گذشته از موعد |
| 09:00 | `ads:process-auto-ladders` | نردبان خودکار (فلگ `auto_ladder` و سرویس نردبان خریداری‌شده) |
| 09:30 | `ads:create-expiry-reminders` | ساخت یادآوری‌های انقضا برای تأیید ادمین |
| 10:00 | `ads:send-expiry-notifications` | پیامک به مالکین آگهی‌های در شرف انقضا با لینک مستقیم تمدید گروهی |
| 03:30 | `media:cleanup-temp` | پاک‌سازی فایل‌های موقت تصاویر |

> دکمهٔ «بروزرسانی نردبان» در نوار ابزار ادمین **بدون کرون هم کار می‌کند**؛ کرون فقط برای اجرای خودکار روزانه است. بدون کرون، آگهی‌ها منقضی نمی‌شوند و پیامک انقضا ارسال نمی‌شود.

---

## ۱۳. درگاه پرداخت و پیامک

### درگاه صاد (بانک ملی)

1. در `.env` مقادیر `PAYMENT_GATEWAY=sadad` و سه کلید `SADAD_MERCHANT_ID`، `SADAD_TERMINAL_ID`، `SADAD_TRANSACTION_KEY` را از پنل صاد وارد کنید.
2. `SADAD_TRANSACTION_KEY` باید **base64 کلید ۲۴ بایتی** باشد (مطابق فایل‌های صادرشدهٔ صاد). امضای `SignData` با الگوریتم استاندارد TripleDES/zero-IV تولید می‌شود.
3. آدرس **ReturnUrl** به‌صورت خودکار با امضای HMAC امضا می‌شود: `https://yourdomain.ir/user/payments/callback/{authority}?signature=…` — این مسیر از CSRF مستثناست و فقط با همان امضا پذیرفته می‌شود.
4. پس از اتصال ترمینال، یک خرید آزمایشی انجام دهید و این سه چیز را بررسی کنید:
   - ریدایرکت به صفحهٔ بانک (`sadad.shaparak.ir/VPG/Purchase`)
   - بازگشت موفق و نمایش پیام «پرداخت و فعال‌سازی سرویس با موفقیت انجام شد»
   - ثبت پرداخت موفق در «پرداخت‌ها»ی پنل کاربر و پنل ادمین

### پیامک IPPanel

در `.env` (بخش ۶):

```
SMS_PROVIDER=ippanel
IPPANEL_API_KEY=کلید-از-پنل
SMS_SENDER_NUMBER=شماره-فرستنده
```

کاربردهای پیامک در سامانه: ارسال رمز ثبت‌نام (OTP)، یادآوری انقضای آگهی با لینک تمدید گروهی، پاسخ تیکت‌ها. تا وقتی `SMS_PROVIDER=log` باشد پیام‌ها فقط در لاگ ذخیره می‌شوند و به کاربر نمی‌رسند.

---

## ۱۴. رفع خطاهای رایج

### خطای ۵۰۰ Internal Server Error

| علت | راه‌حل |
|------|--------|
| مجوز پوشه‌ها | `storage` و `bootstrap/cache` → 775 |
| کش قدیمی | حذف `bootstrap/cache/*.php` و `storage/framework/views/*` |
| `.env` اشتباه | بررسی `APP_KEY`، `DB_*` |
| `vendor` ناقص | آپلود مجدد با `--no-dev` |

### خطای `Class "Laravel\Pail\PailServiceProvider" not found`

**راه‌حل:** پکیج‌های dev را در `composer.json` به `dont-discover` اضافه کنید (بخش ۹) — در آخرین نسخهٔ ریپو این مورد از قبل اعمال شده است. سپس کش‌ها را پاک کنید.

### خطای `No application encryption key has been specified`

**راه‌حل:** `APP_KEY` خالی است. با `php artisan key:generate --show` روی لوکال یک کلید بسازید و در `.env` سرور کپی کنید.

### استایل‌ها بارگذاری نمی‌شوند / ظاهر سایت متفاوت است

به ترتیب این علت‌ها را چک کنید (شایع‌ترین اول):

1. **پوشه `build` آپلود نشده یا قدیمی است:** باید هم در `public_html/build/` و هم در `laravel/public/build/` باشد و **هش فایل CSS با `manifest.json` ریپو یکی باشد** (بخش ۹). صفحهٔ کاملاً بدون استایل (HTML خام) یعنی `manifest.json` روی هاست نیست.
2. **مسیر public اشتباه:** خط `$app->usePublicPath(__DIR__);` را به `index.php` اضافه کنید (بخش ۴).
3. **Mixed Content (HTTP به جای HTTPS):** این مورد **در کد اعمال شده** — در `AppServiceProvider.php` در حالت production آدرس‌ها اجباراً `https` تولید می‌شوند. اگر باز آدرس http دیدید، `APP_URL` را چک کنید و `php artisan config:clear && php artisan config:cache` بزنید.
4. **`@vite` در layout نیست:** در ریپو موجود است؛ به `<head>` لایوت این دستور باید باشد:
   ```blade
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   ```
5. **کل صفحه با فونت Tahoma (بدون فونت فارسی):** از نسخهٔ قدیمی ریپو استفاده می‌کنید — فونت وزیرمتن در نسخه‌های جدید سلف‌هاست شده و همراه `public/build` کامیت است.
6. **کش مرورگر یا CDN:** با `Ctrl+Shift+R` کش مرورگر را پاک کنید؛ در پنل CDN (کلودفلر/آروان) Purge بزنید.
7. **جعبهٔ خالی بالای صفحهٔ اصلی:** هیچ بنری تنظیم نشده — از **پنل ادمین → نوار ابزار → 🖼️ بنر** (آدرس `https://yourdomain.ir/admin/banner`) تصویر یا HTML بنر را بارگذاری کنید. بدون بنر هم متن معرفی پیش‌فرض با دکمهٔ «ثبت آگهی رایگان» نمایش داده می‌شود.

### خطای `SQLSTATE[HY000] [2002] Connection timed out`

**علت:** فایروال سرور پورت 3306 را برای IP شما بسته است.

**راه‌حل‌ها:**
- **SSH Tunnel** (بهترین راه)
- **درخواست از هاستینگ** برای باز کردن پورت 3306
- **استفاده از mysqldump + phpMyAdmin** (بدون نیاز به اتصال مستقیم)

### سایر خطاها

| علامت | علت احتمالی | راه‌حل |
|---|---|---|
| صفحهٔ سفید یا 500 | دسترسی `storage` / `bootstrap/cache` | مجوز 775 (بخش ۱۰) |
| تصاویر آگهی‌ها 404 | `storage:link` اجرا نشده | `php artisan storage:link --force` |
| خطای 419 (CSRF) | دامنه/HTTPS با `APP_URL` نمی‌خواند | `APP_URL` را دقیقاً برابر دامنهٔ واقعی بگذارید |
| بعد از تغییر `.env` اثری نیست | کش کانفیگ | `php artisan config:clear && php artisan config:cache` |
| پرداخت بعد از بانک 403 می‌شود | `APP_KEY` تغییر کرده یا `APP_URL` غلط است | `APP_KEY` را ثابت نگه دارید |
| پیامک نمی‌رود | `SMS_PROVIDER=log` مانده | کلید ippanel را تنظیم و کش کانفیگ را بازسازی کنید |
| آگهی‌ها منقضی نمی‌شوند | کرون ثبت نشده | بخش ۱۲ |

---

## ۱۵. چک‌لیست نهایی

### پیش از استقرار

- [ ] PHP 8.3 روی دامنه فعال شد
- [ ] افزونه‌های PHP بررسی شدند
- [ ] دیتابیس MySQL ساخته شد
- [ ] کاربر MySQL با ALL PRIVILEGES اضافه شد
- [ ] `composer install --no-dev` روی لوکال اجرا شد
- [ ] `npm run build` روی لوکال اجرا شد (یا build کامیت‌شدهٔ ریپو استفاده شد)

### استقرار

- [ ] ساختار پوشه `public_html` + `laravel` ایجاد شد
- [ ] فایل `index.php` اصلاح شد (با `usePublicPath`)
- [ ] فایل `.htaccess` اصلاح شد
- [ ] فایل `.env` با مقادیر production تنظیم شد
- [ ] پوشه `vendor` آپلود شد
- [ ] پوشه `build` در دو مسیر آپلود شد و هش CSS با ریپو یکی است
- [ ] `composer.json` برای `dont-discover` ویرایش شد (در آخرین نسخهٔ ریپو اعمال شده)
- [ ] مجوز پوشه‌ها 775 شد
- [ ] مایگریشن‌ها اجرا شدند
- [ ] `storage:link` اجرا شد
- [ ] کرون `schedule:run` ثبت شد

### پس از استقرار

- [ ] دامنه با موفقیت باز شد
- [ ] استایل‌ها و فونت فارسی (وزیرمتن) بارگذاری شدند
- [ ] لاگین و ثبت‌نام کار می‌کنند (پیامک OTP می‌رسد)
- [ ] آپلود تصاویر کار می‌کند
- [ ] یک تراکنش آزمایشی در درگاه صاد انجام شد
- [ ] بنر صفحهٔ اصلی از پنل ادمین تنظیم شد
- [ ] `APP_DEBUG=false` تنظیم شد
- [ ] کش‌ها پاک شدند

### امنیت

- [ ] `APP_DEBUG=false` و `APP_ENV=production`
- [ ] `.env` بیرون از `public_html`
- [ ] `.git` (اگر وجود دارد) حذف شد
- [ ] فایل‌های zip موقت حذف شدند
- [ ] پوشه `storage` از دسترس وب خارج است
- [ ] `https://yourdomain.ir/.env` خطای 403/404 می‌دهد
- [ ] `vendor` و `node_modules` وب‌قابل‌دسترس نیستند

---

## ۱۶. به‌روزرسانی نسخهٔ آینده

```bash
# روی لوکال
git pull origin readme-update
bash scripts/package-cpanel.sh     # ساخت vendor.zip و build.zip جدید
php artisan migrate --force        # اگر مایگریشن جدید هست (یا با mysqldump طبق بخش ۱۱)
```

سپس روی هاست:

1. از دیتابیس (phpMyAdmin → Export) و پوشهٔ `storage/app/public` بکاپ بگیرید
2. `vendor.zip` جدید را در `laravel/` Extract کنید (قبلی را پاک کنید)
3. `build.zip` جدید را در **هر دو مسیر** Extract کنید (پوشهٔ قبلی `build` را پاک کنید)
4. فایل‌های تغییرکردهٔ کد را آپلود کنید
5. اگر دسترسی Terminal دارید: `php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache`
6. سایت را با `Ctrl+Shift+R` بررسی کنید و هش CSS را با `manifest.json` جدید مقایسه کنید

> در حالت `PAYMENT_GATEWAY=fake` (فقط توسعهٔ محلی) خرید شبیه‌سازی‌شده بلافاصله تسویه می‌شود — هرگز روی production باقی نگذارید.

---

## پیوست: نکات مهم

### چرا `--no-dev`؟

پکیج‌های dev مثل `phpunit`, `faker`, `pint` و... در production لازم نیستند و فقط حجم `vendor` را زیاد می‌کنند. حذف آن‌ها هم امنیت را بالا می‌برد و هم سرعت بارگذاری را.

### چرا `dont-discover`؟

برخی پکیج‌ها (مثل `laravel/pail` و `nunomaduro/collision`) در `composer.json` به عنوان dev-only تعریف شده‌اند، ولی Laravel از طریق **Package Discovery** سعی می‌کند آن‌ها را در همه محیط‌ها بارگذاری کند. `dont-discover` این رفتار را غیرفعال می‌کند.

### چرا پوشه `build` در دو مسیر؟

- `public_html/build/` برای **دسترسی مستقیم مرورگر** به فایل‌های CSS و JS
- `laravel/public/build/` برای **`@vite`** که `manifest.json` را از مسیر پیش‌فرض (public در هسته Laravel) می‌خواند

با `usePublicPath(__DIR__)` فقط یکی کافی است، ولی برای اطمینان در هر دو مسیر قرار می‌دهیم.

### چرا mysqldump بهتر از SSH Tunnel است؟

- فایروال سرورهای اشتراکی معمولاً پورت 3306 را می‌بندند
- SSH اغلب در هاست‌های اشتراکی غیرفعال است
- mysqldump + phpMyAdmin روی همه هاست‌ها کار می‌کند

### مدیریت `.env` بین لوکال و سرور

پیشنهاد می‌شود `.env` را در گیت قرار ندهید و برای هر محیط یک فایل جدا داشته باشید:
- `.env.example` / `.env.local` (لوکال)
- `.env.production.example` (الگوی سرور — در ریپو موجود است)

با کامند `php artisan env:decrypt` یا به سادگی کپی دستی مدیریت کنید.

### نکتهٔ مهم برای توسعه‌دهندگان

در این سامانه `auth()->id()` **شماره موبایل** کاربر را برمی‌گرداند نه کلید اصلی جدول (`User::getAuthIdentifierName` روی `mobile` است). هر کوئری ownership روی ستون `user_id` باید از `auth()->user()->id` استفاده کند؛ در غیر این صورت کوئری بی‌خطا، ردیف خالی برمی‌گرداند. تست‌های رگرسیون این رفتار را قفل کرده‌اند.

### بکاپ‌گیری

- **دیتابیس:** cPanel → phpMyAdmin → Export (هفتگی + قبل از هر آپدیت)
- **فایل‌ها:** پوشهٔ `laravel/storage/app/public` (تصاویر آگهی‌ها و مجوزها) را دوره‌ای بکاپ بگیرید

### اجرای تست‌ها قبل از استقرار (اختیاری ولی توصیه‌شده)

```bash
composer install
composer test
```

کل مجموعهٔ ۱۷۱ تست باید سبز باشد؛ شامل ۴۰ فلوی سرتاسری (جستجو، ثبت آگهی، انقضا، پنل ادمین، بنر، فونت و بیلد) و ۱۶ سناریوی عمیق پرداخت.

> ℹ️ چون `nunomaduro/collision` در `dont-discover` است (بخش ۹)، در محیط توسعه به‌جای `php artisan test` از `composer test` یا `vendor/bin/phpunit` استفاده کنید — خروجی همان است.

---

## 🎓 منابع بیشتر

- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)
- [Laravel Vite Integration](https://laravel.com/docs/vite)
- [cPanel MultiPHP Manager](https://go.cpanel.net/EA4ModifyINI)
