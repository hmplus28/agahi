# 🚀 مستندات استقرار سامانه آگهی

راهنمای کامل نصب و راه‌اندازی روی **هاست اشتراکی cPanel** و همچنین **سرور مجازی (VPS)**. پیش از شروع، این سند را کامل مطالعه کنید.

---

## ۱. پیش‌نیازها

| مورد | حداقل نسخه | توضیحات |
|---|---|---|
| PHP | **8.3** | `^8.3` در `composer.json` قفل شده است |
| PostgreSQL یا MySQL | PG 14+ / MySQL 8+ / MariaDB 10.6+ | برای هاست اشتراکی، MySQL از پنل cPanel ساخته می‌شود |
| Composer | 2.x | نصب پکیج‌های PHP |
| Node.js + npm | 20+ | فقط برای بیلد فرانت‌اند (روی سرور لازم نیست بماند) |
| اکستنشن‌های PHP | — | `pdo_mysql` یا `pdo_pgsql`، `mbstring`، `openssl`، `gd`، `curl`، `zip`، `intl`، `fileinfo`، `exif` |

> ⚠️ صفحهٔ PHP در cPanel (Select PHP Version / MultiPHP INI Editor) را بررسی کنید که نسخهٔ PHP دامنه روی **8.3** باشد و اکستنشن‌های بالا فعال باشند.

---

## ۲. ساختار استقرار

پروژه باید **بیرون از `public_html`** قرار بگیرد و Document Root دامنه به پوشهٔ `public` اشاره کند:

```
/home/ACCOUNT/
├── apps/
│   └── agahi/            ← کل پروژه اینجا (هم‌سطح public_html)
│       ├── app/
│       ├── public/       ← Document Root باید اینجا باشد
│       ├── resources/
│       ├── storage/
│       └── …
└── public_html/          ← اگر Document Root قابل تغییر نیست (بخش ۴ را ببینید)
```

**چرا؟** قرار دادن کل پروژه داخل `public_html` باعث می‌شود فایل‌های `.env`، `composer.json` و پوشهٔ `storage` از وب قابل دسترسی شوند. اگر اکیداً مجبورید داخل `public_html` نصب کنید، فایل `.htaccess` ریشهٔ پروژه این فایل‌ها را مسدود می‌کند، اما باز هم روش «Document Root به `public`» به‌مراتب امن‌تر است.

---

## ۳. مراحل نصب روی هاست اشتراکی cPanel (با دسترسی SSH)

### ۳-۱. آپلود کد

```bash
mkdir -p ~/apps && cd ~/apps
git clone https://github.com/hmplus28/agahi.git agahi
cd agahi
git checkout readme-update
```

اگر SSH ندارید، پروژه را به‌صورت zip آپلود و Extract کنید (مسیر خارج از `public_html`).

### ۳-۲. ساخت فایل `.env`

```bash
cp .env.production.example .env
nano .env
```

مقادیر الزامی که حتماً باید تغییر دهید:

| متغیر | مقدار |
|---|---|
| `APP_URL` | آدرس نهایی سایت، مثلاً `https://yourdomain.ir` (با `https`) |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | دیتابیس ساخته‌شده در cPanel |
| `PAYMENT_GATEWAY` | `sadad` برای درگاه واقعی (تا قبل از اتصال ترمینال، `fake` نگذارید و ببینید بخش ۶) |
| `SADAD_MERCHANT_ID` / `SADAD_TERMINAL_ID` / `SADAD_TRANSACTION_KEY` | از پنل درگاه صاد |
| `SMS_PROVIDER` | `ippanel` در تولید + `IPPANEL_API_KEY` |
| `SUPPORT_PHONE` / `SUPPORT_PHONE_HREF` | شمارهٔ پشتیبانی نمایش‌داده‌شده در هدر |

### ۳-۳. اجرای اسکریپت استقرار

اسکریپت `deploy.sh` همهٔ مراحل نصب، بیلد، مایگریشن، کش و مجوزها را خودکار انجام می‌دهد:

```bash
bash deploy.sh
```

اگر ترجیح می‌دهید دستی اجرا کنید:

```bash
/usr/local/bin/php /usr/local/bin/composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# بیلد فرانت‌اند — فقط وقتی لازم است که CSS/JS را تغییر داده باشید
# فایل‌های بیلدشده (public/build) داخل ریپو کامیت شده‌اند و روی هاست به npm نیازی نیست
npm ci --no-audit --no-fund
npm run build

/usr/local/bin/php artisan key:generate --force
/usr/local/bin/php artisan migrate --force
/usr/local/bin/php artisan storage:link --force
/usr/local/bin/php artisan optimize:clear
/usr/local/bin/php artisan config:cache && /usr/local/bin/php artisan route:cache && /usr/local/bin/php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

> ✅ **خبر خوب:** فایل‌های کامپایل‌شدهٔ CSS/JS (پوشهٔ `public/build`) و فونت وزیرمتن داخل ریپو کامیت شده‌اند؛ پس حتی اگر هاست شما Node/npm ندارد، سایت با استایل و فونت اصلی بالا می‌آید. `npm run build` فقط بعد از تغییر `resources/css` یا `resources/js` لازم است — بعد از بیلد، `public/build` را دوباره کامیت و پوش کنید.

### ۳-۴. تنظیم Document Root

در cPanel → **Domains** → ویرایش دامنه → **Document Root** را به مسیر زیر تغییر دهید:

```
/home/ACCOUNT/apps/agahi/public
```

سپس مطمئن شوید `https://yourdomain.ir` صفحهٔ اصلی سایت را نشان می‌دهد و مسیرهای `/.env` یا `/storage` خطای 403 می‌دهند.

---

## ۴. نصب بدون امکان تغییر Document Root

برخی هاست‌های اشتراکی اجازهٔ تغییر Document Root نمی‌دهند. در این حالت:

1. کل پروژه را داخل `public_html` بریزید (محتویات `public` هم داخل همان پوشه می‌ماند).
2. فایل `.htaccess` ریشهٔ پروژه دسترسی مستقیم به `.env`، `vendor`، `storage`، `tests`، `database` و … را می‌بندد.
3. یک فایل `.htaccess` داخل `public_html` بسازید که همهٔ درخواست‌ها را به `public/index.php` هدایت کند:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

> این روش کار می‌کند ولی گزینهٔ امن‌تر، روش بخش ۳-۴ است.

---

## ۵. Cron Jobs (الزامی)

برنامه‌های زمان‌بندی‌شدهٔ سیستم فقط با این کرون اجرا می‌شوند. در cPanel → **Cron Jobs**:

```
* * * * * cd /home/ACCOUNT/apps/agahi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

این کرون به‌صورت خودکار این کارها را زمان‌بندی می‌کند:

| ساعت | دستور | کار |
|---|---|---|
| هر ساعت | `ads:expire` | منقضی‌کردن آگهی‌های گذشته از موعد |
| 09:00 | `ads:process-auto-ladders` | نردبان خودکار (فلگ `auto_ladder` و سرویس نردبانِ خریداری‌شده) |
| 09:30 | `ads:create-expiry-reminders` | ساخت یادآوری‌های انقضا برای تأیید ادمین |
| 10:00 | `ads:send-expiry-notifications` | پیامک به مالکین آگهی‌های منقضی با لینک مستقیم صفحهٔ تمدید گروهی |
| 03:30 | `media:cleanup-temp` | پاک‌سازی فایل‌های موقت تصاویر |

> دکمهٔ «بروزرسانی نردبان» در نوار ادمین **بدون کرون هم کار می‌کند**؛ کرون برای اجرای خودکار روزانه است.

---

## ۶. درگاه پرداخت (صاد — بانک ملی)

1. در `.env` مقادیر `PAYMENT_GATEWAY=sadad` و سه کلید `SADAD_MERCHANT_ID`، `SADAD_TERMINAL_ID`، `SADAD_TRANSACTION_KEY` را از پنل صاد وارد کنید.
2. `SADAD_TRANSACTION_KEY` باید **base64** کلید ۲۴ بایتی باشد (مطابق فایل‌های صادرشدهٔ صاد). امضای `SignData` با الگوریتم استاندارد TripleDES/zero-IV تولید می‌شود.
3. آدرس **ReturnUrl** که به بانک داده می‌شود به‌صورت خودکار با امضای HMAC امضا می‌شود: `https://yourdomain.ir/user/payments/callback/{authority}?signature=…` — این مسیر از CSRF مستثناست و فقط با همان امضا پذیرفته می‌شود؛ تغییر دستی آن باعث 403 می‌شود.
4. پس از اتصال ترمینال، یک خرید آزمایشی انجام دهید و این سه چیز را بررسی کنید:
   - ریدایرکت به صفحهٔ بانک (`sadad.shaparak.ir/VPG/Purchase`)
   - بازگشت موفق و نمایش پیام «پرداخت و فعال‌سازی سرویس با موفقیت انجام شد»
   - ثبت پرداخت موفق در «پرداخت‌ها»ی پنل کاربر و پنل ادمین (پیش‌فرض فقط تراکنش‌های موفق نمایش داده می‌شوند)

> در حالت `PAYMENT_GATEWAY=fake` (فقط برای توسعه) خرید به‌صورت شبیه‌سازی‌شده بلافاصله تسویه می‌شود.

---

## ۷. پیامک (IPPanel)

در `.env`:

```
SMS_PROVIDER=ippanel
IPPANEL_API_KEY=کلید-از-پنل
IPPANEL_BASE_URL=https://edge.ippanel.com/v1
SMS_SENDER_NUMBER=شماره-فرستنده
```

کاربردهای پیامک در سامانه: ارسال رمز ثبت‌نام، یادآوری انقضای آگهی (با لینک تمدید گروهی)، پاسخ تیکت‌ها. تا وقتی `SMS_PROVIDER=log` باشد، پیام‌ها فقط در جدول لاگ ذخیره می‌شوند.

---

## ۸. چک‌لیست امنیتی پیش از تحویل

- [ ] `APP_DEBUG=false` و `APP_ENV=production` در `.env`
- [ ] `APP_KEY` با `artisan key:generate` روی سرور تولید شده (از لوکال کپی نشده باشد)
- [ ] HTTPS فعال و `APP_URL` با `https://` تنظیم شده
- [ ] Document Root روی `public` است و `https://domain/.env` خطای 403/404 می‌دهد
- [ ] دسترسی `storage` و `bootstrap/cache` روی 775 (مالک: یوزر cPanel)
- [ ] `vendor` و `node_modules` وب‌قابل‌دسترس نیستند
- [ ] کرون `schedule:run` ثبت شده
- [ ] یک تراکنش آزمایشی موفق در درگاه انجام شده
- [ ] دکمهٔ «فراموشی رمز» و پیامک ثبت‌نام تست شده

---

## ۹. به‌روزرسانی نسخهٔ آینده

```bash
cd ~/apps/agahi
git pull origin readme-update
bash deploy.sh
```

`deploy.sh` مایگریشن‌های جدید را اجرا و کش‌ها را بازسازی می‌کند. پیش از آپدیت، از دیتابیس و پوشهٔ `storage` بکاپ بگیرید.

اگر در آپدیت، `public/build` تغییر کرده باشد، مطمئن شوید فایل‌های قدیمی `public/build/assets` روی هاست حذف شده‌اند (آپلود روی فایل‌های قبلی کافی است چون نام فایل‌ها هش‌دار است، ولی اگر zip را Extract کردید پوشهٔ قبلی را پاک کنید). بعد از هر آپدیت، `php artisan optimize:clear` را فراموش نکنید.

---

## ۱۰. بنر صفحهٔ اصلی (Hero)

بالای صفحهٔ اصلی یک جای‌بان بنر دارد. تا وقتی بنری تنظیم نکنید، یک متن معرفی پیش‌فرض با دکمهٔ «ثبت آگهی رایگان» نمایش داده می‌شود (جعبهٔ خالی نیست).

برای تنظیم بنر اختصاصی: **پنل ادمین → نوار ابزار → 🖼️ بنر** — فایل تصویر (jpg/png/webp/svg) یا فایل HTML حداکثر ۴ مگابایت را بارگذاری کنید. از همان‌جا می‌توانید فعال/غیرفعال کنید یا حذف کنید. آدرس مستقیم: `https://yourdomain.ir/admin/banner`

---

## ۱۱. بکاپ‌گیری

- **دیتابیس:** cPanel → phpMyAdmin → Export (هفتگی + قبل از هر آپدیت)
- **فایل‌ها:** پوشهٔ `storage/app/public` (تصاویر آگهی‌ها و مجوزها) را دوره‌ای بکاپ بگیرید
- ساده‌ترین خودکارسازی: یک کرون روزانه اضافه با `mysqldump` و rsync به فضای بکاپ

---

## ۱۲. عیب‌یابی‌های رایج

### ۱۲-۱. «ظاهر سایت بعد از استقرار کلاً متفاوت است!»

به ترتیب این ۴ علت را چک کنید (شایع‌ترین‌ها اول):

| # | علامت | علت | راه‌حل |
|---|---|---|---|
| ۱ | کل صفحه بدون فونت فارسی (متن‌ها با Tahoma) | فونت وزیرمتن قدیمی‌ها CDN بود و لود نمی‌شد | از نسخهٔ فعلی ریپو استفاده کنید؛ فونت وزیرمتن سلف‌هاست شده و همراه `public/build` کامیت است |
| ۲ | استایل‌ها شبیه طراحی‌اند ولی بعضی بخش‌ها خراب/کم‌رنگ است | `public/build` قدیمی روی هاست مانده (بیلد از سورس قدیمی) | آخرین `public/build` را از ریپو آپلود کنید؛ اگر خودتان بیلد می‌گیرید حتماً بعد از `git pull` و روی سورس جدید بیلد بگیرید |
| ۳ | بعد از آپدیت هنوز استایل قدیمی می‌بینید | کش مرورگر یا CDN (آروان/کلودفلر) | کش مرورگر را با Ctrl+Shift+R پاک کنید؛ در پنل CDN، Purge بزنید |
| ۴ | بخش hero خالی است | هیچ بنری تنظیم نشده | بخش ۱۰ — از پنل ادمین بنر تنظیم کنید (بدون بنر، متن معرفی پیش‌فرض نمایش داده می‌شود) |

> نکته: اگر صفحه کاملاً بدون استایل رندر می‌شود (HTML خام)، یعنی `public/build/manifest.json` روی هاست نیست — پوشهٔ `public/build` را از ریپو آپلود کنید.

### ۱۲-۲. سایر عیب‌یابی‌ها

| علامت | علت احتمالی | راه‌حل |
|---|---|---|
| صفحهٔ سفید یا 500 | دسترسی `storage` / `bootstrap/cache` | `chmod -R 775 storage bootstrap/cache` |
| تصاویر آگهی‌ها 404 | `storage:link` اجرا نشده | `php artisan storage:link --force` |
| خطای 419 (CSRF) | دامنه/HTTPS با `APP_URL` نمی‌خواند | `APP_URL` را دقیقاً برابر دامنهٔ واقعی بگذارید |
| بعد از تغییر `.env` اثری نیست | کش کانفیگ | `php artisan config:clear && php artisan config:cache` |
| پرداخت بعد از بانک 403 می‌شود | `APP_KEY` تغییر کرده یا `APP_URL` غلط است | `APP_KEY` را ثابت نگه دارید؛ امضای callback به `APP_KEY` وابسته است |
| پیامک نمی‌رود | `SMS_PROVIDER=log` مانده | کلید ippanel را در `.env` تنظیم و `config:cache` کنید |
| آگهی‌ها منقضی نمی‌شوند | کرون ثبت نشده | بخش ۵ (Cron Jobs) |

---

## ۱۳. نکتهٔ مهم برای توسعه‌دهندگان

در این سامانه `auth()->id()` **شماره موبایل** کاربر را برمی‌گرداند نه کلید اصلی جدول (`User::getAuthIdentifierName` روی `mobile` است). هر کوئری ownership روی ستون `user_id` باید از `auth()->user()->id` استفاده کند؛ در غیر این صورت کوئری بی‌خطا، ردیف خالی برمی‌گرداند. تست‌های رگرسیون (`tests/Feature/ThirtyFlowsTest.php` فلوهام ۲۰ تا ۲۲ و تست‌های پرداخت) این رفتار را قفل کرده‌اند.

---

## ۱۴. اجرای تست‌ها قبل از استقرار (اختیاری ولی توصیه‌شده)

```bash
composer install
php artisan test
```

کل مجموعهٔ ۱۶۸ تست باید سبز باشد؛ این مجموعه شامل ۳۷ فلوی سرتاسری (جستجو، ثبت آگهی، انقضا، پنل ادمین، بنر صفحهٔ اصلی، فونت و بیلد کامیت‌شده) و ۱۶ سناریوی عمیق پرداخت است.
