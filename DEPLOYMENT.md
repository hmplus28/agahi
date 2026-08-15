# راهنمای استقرار پروژه آگهی

این راهنما برای سرور Linux، cPanel/CloudLinux و محیط‌های دارای Passenger نوشته شده است. پروژه در production باید با `config.settings.production` اجرا شود؛ این تنظیمات `DEBUG=False`، HTTPS cookieها، HSTS، `ManifestStaticFilesStorage` و logging در خروجی استاندارد را فعال می‌کند.

> **پیش از استقرار:** اگر فایل `.env` قبلی در تاریخچهٔ مخزن یا سرور شامل `SECRET_KEY`، رمز پایگاه داده یا کلید درگاه بوده است، کلیدها را rotate کنید. خارج‌کردن فایل از Git، اطلاعاتی را که قبلاً push شده‌اند بی‌اثر نمی‌کند.

## ۱. آماده‌سازی محیط

روی سرور، نسخهٔ سازگار Python و PostgreSQL را نصب کنید. سپس کد را در مسیری مانند `/home/USER/apps/agahi` قرار داده و محیط جداگانه ایجاد کنید.

```bash
cd /home/USER/apps/agahi
python3 -m venv .venv
source .venv/bin/activate
pip install --upgrade pip
pip install -r requirements/production.txt
# برای نصب بدون اینترنت، به‌جای دو خط بالا از deployment/install_offline_dependencies.sh استفاده کنید.
cp .env.example .env
chmod 600 .env
```

در `.env` مقادیر `SECRET_KEY`، نام و رمز پایگاه داده، `ALLOWED_HOSTS`، `CSRF_TRUSTED_ORIGINS` و در صورت استفاده از درگاه، `ZARINPAL_MERCHANT_ID` را تکمیل کنید. همهٔ originهای CSRF باید با `https://` آغاز شوند.

| متغیر | مقدار نمونه | نقش |
|---|---|---|
| `DJANGO_SETTINGS_MODULE` | `config.settings.production` | فعال‌سازی تنظیمات production |
| `ALLOWED_HOSTS` | `example.com,www.example.com` | محدودسازی Host header |
| `CSRF_TRUSTED_ORIGINS` | `https://example.com` | پذیرش POST امن از دامنهٔ سایت |
| `PAYMENT_SANDBOX` | `false` در production | انتخاب محیط واقعی یا آزمایشی درگاه |

## ۲. اعمال schema و static files

قبل از تغییر version در production، نسخهٔ پشتیبان پایگاه داده تهیه کنید. سپس migration و assetها را اعمال کنید.

```bash
source /home/USER/apps/agahi/.venv/bin/activate
cd /home/USER/apps/agahi
export DJANGO_SETTINGS_MODULE=config.settings.production
python manage.py check --deploy
python manage.py migrate --noinput
python manage.py collectstatic --noinput
```

وب‌سرور باید مسیر `STATIC_URL` را به `STATIC_ROOT` و مسیر media را به storage محلی مناسب متصل کند. اجرای `collectstatic` بخشی ضروری از deployment است. فونت، CSS و JavaScript پروژه self-hosted هستند و برای کارکرد سایت به CDN یا object storage خارجی نیاز نیست.

## ۲.۱. حالت آفلاین و نصب بدون اینترنت

برای مقاومت در قطع اینترنت، `OFFLINE_MODE=true` و یک `CACHE_DIR` قابل‌نوشتن خارج از `public_html` تنظیم کنید. این حالت پرداخت، SMS، email خارجی، Redis و انتقال remote backup را غیرفعال می‌کند؛ هستهٔ SSR و PostgreSQL محلی به کار ادامه می‌دهند. پیش از رخداد، wheelhouse را با `deployment/build_offline_wheelhouse.sh` بسازید. راهنمای کامل در [OFFLINE_OPERATION.md](docs/OFFLINE_OPERATION.md) قرار دارد.

## ۳. Passenger و دامنه

در cPanel، application root را مسیر پروژه و startup file را `passenger_wsgi.py` قرار دهید. application entry point همان `application` است. بعد از هر تغییر کد، از پنل اپلیکیشن restart کنید یا در صورت پشتیبانی میزبان، فایل restart Passenger را ایجاد کنید.

```bash
mkdir -p tmp
touch tmp/restart.txt
```

SSL معتبر را روی دامنه فعال کنید. اگر reverse proxy یا CDN در جلوی برنامه قرار می‌دهید، ابتدا redirect loop را در یک محیط staging بررسی کرده و سپس `SECURE_SSL_REDIRECT=true` را نگه دارید.

## ۴. عملیات زمان‌بندی‌شده

دستورهای زیر باید از cron و با virtualenv همان پروژه اجرا شوند. مسیرها را مطابق سرور جایگزین کنید و خروجی را به log قابل‌دسترسی هدایت کنید.

```cron
# انقضای آگهی‌های فعال
5 2 * * * /home/USER/apps/agahi/.venv/bin/python /home/USER/apps/agahi/manage.py expire_ads --limit 500 >> /home/USER/logs/agahi-cron.log 2>&1

# اعمال نردبان خودکار
15 2 * * * /home/USER/apps/agahi/.venv/bin/python /home/USER/apps/agahi/manage.py auto_ladder --days 7 --limit 100 >> /home/USER/logs/agahi-cron.log 2>&1

# صف و ارسال پیام‌های مرتبط با انقضا؛ در OFFLINE_MODE فقط queue محلی حفظ می‌شود.
30 2 * * * /home/USER/apps/agahi/.venv/bin/python /home/USER/apps/agahi/manage.py send_notifications --type all --days 3 --dispatch >> /home/USER/logs/agahi-cron.log 2>&1
```

`send_notifications` صف پیامک را idempotent ایجاد می‌کند و در صورت `--dispatch` فقط با provider پیکربندی‌شده ارسال می‌کند. در `OFFLINE_MODE=true` provider خارجی غیرفعال است و queue محلی دست‌نخورده می‌ماند تا پس از بازگشت ارتباط ارسال شود.

## ۵. پرداخت و کنترل پیش از انتشار

جریان پرداخت، payment callback را با تأیید سمت سرور نگه می‌دارد و فقط بعد از verification، invoice و service آگهی را successful می‌کند. پرداخت واقعی را ابتدا با `PAYMENT_SANDBOX=true` و callback HTTPS دامنهٔ staging آزمایش کنید. برای انتشار نهایی، یک مدیر باید تعرفه‌های فعال، مسیر callback و کلید merchant را بررسی کند.

پیش از هر release، حداقل این سه فرمان را اجرا کنید:

```bash
python manage.py check --deploy
python manage.py makemigrations --check --dry-run
python manage.py test
```

## منابع

برای عملیات backup و بازیابی، [BACKUP_AND_RECOVERY.md](docs/BACKUP_AND_RECOVERY.md) و برای رفتار در قطع اینترنت، [OFFLINE_OPERATION.md](docs/OFFLINE_OPERATION.md) را ببینید.
