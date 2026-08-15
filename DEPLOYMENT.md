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

وب‌سرور باید مسیر `STATIC_URL` را به `STATIC_ROOT` و مسیر media را به storage مناسب متصل کند. طبق مستندات Django، اجرای `collectstatic` بخشی ضروری از deployment است؛ برای مقیاس بیشتر static/media را از CDN یا object storage سرویس دهید.

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

# صف پیام‌های مرتبط با انقضا
30 2 * * * /home/USER/apps/agahi/.venv/bin/python /home/USER/apps/agahi/manage.py send_notifications --type all --days 3 >> /home/USER/logs/agahi-cron.log 2>&1
```

`send_notifications` فعلاً پیام‌ها را به‌صورت idempotent در `SMSLog` با وضعیت `pending` ثبت می‌کند. اتصال نهایی سرویس‌دهندهٔ SMS باید در یک worker یا integration ایمن، همین رکوردها را ارسال و `provider_id`، `status` و `response` را به‌روزرسانی کند.

## ۵. پرداخت و کنترل پیش از انتشار

جریان پرداخت، payment callback را با تأیید سمت سرور نگه می‌دارد و فقط بعد از verification، invoice و service آگهی را successful می‌کند. پرداخت واقعی را ابتدا با `PAYMENT_SANDBOX=true` و callback HTTPS دامنهٔ staging آزمایش کنید. برای انتشار نهایی، یک مدیر باید تعرفه‌های فعال، مسیر callback و کلید merchant را بررسی کند.

پیش از هر release، حداقل این سه فرمان را اجرا کنید:

```bash
python manage.py check --deploy
python manage.py makemigrations --check --dry-run
python manage.py test
```

## منابع

برای تصمیم‌های SEO و asset deployment، [فهرست منابع رسمی](docs/IMPLEMENTATION_REFERENCES.md) را ببینید.
