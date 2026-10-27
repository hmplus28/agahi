# راهنمای نصب با داکر (Docker) — سامانه آگهی

این راهنما کل سامانه (وب‌سایت، پنل ادمین، دیتابیس PostgreSQL و کرون‌جاب‌ها) را با داکر بالا می‌آورد. همهٔ اجزا در کانتینر اجرا می‌شوند و روی سیستم شما چیزی جز خودِ داکر نصب نمی‌شود.

---

## ۱. معماری سرویس‌ها

| سرویس | ایمیج | نقش |
|---|---|---|
| `web` | nginx 1.27 (ساخته‌شده از همین ریپو) | وب‌سرور؛ فایل‌های استاتیک (css/js/عکس‌ها) + تحویل درخواست‌های PHP |
| `app` | PHP 8.3-FPM (ساخته‌شده از همین ریپو) | هستهٔ لاراول؛ migration خودکار، کش‌سازی، symlink انبار |
| `db` | postgres 16-alpine | دیتابیس PostgreSQL با ولیوم دائمی |
| `scheduler` | همان ایمیج app | اجرای زمان‌بند لاراول (انقضای آگهی، نردبان، یادآوری تمدید، پاک‌سازی) |

نکته: صف ارسال پیامک‌ها از نوع `sync` است (در همان درخواست اجرا می‌شود)، بنابراین کانتینر queue-worker لازم نیست.

## ۲. پیش‌نیازها

- Docker Engine 24+ یا Docker Desktop
- Docker Compose v2 (`docker compose version` باید جواب بدهد)
- حدود ۲ گیگابایت فضای خالی برای ایمیج‌ها

روی سرور اوبونتو/دبیان:

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER   # بعد از این دستور، یک بار logout/login کنید
```

## ۳. دریافت سورس

```bash
git clone https://github.com/hmplus28/agahi.git
cd agahi
git checkout main        # یا readme-update
```

## ۴. تنظیم متغیرهای محیطی

```bash
cp .env.docker.example .env
```

فایل `.env` را باز کنید و این موارد را حتماً تنظیم کنید:

| متغیر | توضیح |
|---|---|
| `APP_KEY` | **الزامی.** تولید کنید با: `openssl rand -base64 32` و با پیشوند `base64:` بگذارید. اگر خالی بماند، هربار بازسازی کانتینر کلید موقتی جدید ساخته می‌شود و همهٔ کاربران لاگ‌اوت می‌شوند. |
| `APP_URL` | آدرس واقعی سایت، مثلاً `https://agahi.example.com`. تصاویر و استایل‌ها از همین آدرس سرو می‌شوند؛ اشتباه بودنش یعنی عکس‌های خالی. |
| `POSTGRES_PASSWORD` | **الزامی.** یک رمز قوی برای دیتابیس. |
| `WEB_PORT` | پورتی که سایت روش سرو می‌شود (پیش‌فرض `8080`؛ روی سرور با دامنه `80` بگذارید). |
| `SMS_PROVIDER` / `IPPANEL_API_KEY` | برای ارسال واقعی پیامک: `ippanel` و کلید API. برای تست محلی همان `log` بماند. |
| `RUN_SEEDER` | بار اول اجرا `true` بگذارید تا دسته‌بندی‌ها و کاربر ادمین ساخته شوند؛ بعد از اولین اجرا برگردانید به `false`. |

## ۵. اجرا

```bash
docker compose up -d --build
```

اولین بیلد چند دقیقه طول می‌کشد (نصب پکیج‌های composer و npm داخل ایمیج). کانتینر `app` به‌طور خودکار:

1. منتظر بالا آمدن PostgreSQL می‌ماند
2. `php artisan migrate --force` را اجرا می‌کند (و اگر `RUN_SEEDER=true` باشد seeder را هم)
3. `storage:link` می‌سازد
4. در حالت production کش‌های config/route/view/event را می‌سازد

وضعیت را ببینید:

```bash
docker compose ps
docker compose logs -f app
```

سایت: `http://localhost:8080` (یا `http://SERVER_IP` روی سرور)

## ۶. اولین ورود به پنل مدیریت

اگر `RUN_SEEDER=true` گذاشته بودید، کاربر ادمین ساخته شده است:

- آدرس پنل: `/admin/login`
- موبایل: `09120000000`
- رمز عبور: `ChangeMe123!`

⚠️ **بلافاصله بعد از اولین ورود، این رمز را عوض کنید** (سشن ادمین سپس از منوی کاربری رمز را تغییر دهید؛ تغییر رمز با پیامک هم پشتیبانی می‌شود).

اگر سیدر را اجرا نکرده‌اید، هر وقت خواستید: `docker compose exec app php artisan db:seed --force`

## ۷. دستورات روزمره

```bash
docker compose ps                     # وضعیت سرویس‌ها
docker compose logs -f web app        # لاگ زندهٔ وب‌سرور و اپ
docker compose exec app php artisan tinker   # ترمینال لاراول
docker compose exec app php artisan down     # حالت تعمیر
docker compose exec app php artisan up
docker compose stop                   # توقف بدون حذف
docker compose start                  # اجرای مجدد
docker compose down                   # حذف کانتینرها (دیتای دیتابیس می‌ماند)
docker compose down -v                # ⚠️ حذف کامل + پاک‌شدن دیتابیس و فایل‌های آپلودی
```

## ۸. به‌روزرسانی نسخهٔ سایت

```bash
git pull
docker compose up -d --build
```

migrationهای جدید به‌صورت خودکار هنگام بالا آمدن کانتینر `app` اعمال می‌شوند.

## ۹. استقرار روی سرور با دامنهٔ واقعی

1. در `.env` روی سرور: `APP_URL=https://your-domain.com` و `WEB_PORT=80` و `SESSION_SECURE_COOKIE=true`
2. `docker compose up -d --build`
3. برای HTTPS یک معکوس‌پروکسی جلوی آن بگذارید (Caddy ساده‌ترین است):

```
# /etc/caddy/Caddyfile
your-domain.com {
    reverse_proxy 127.0.0.1:80
}
```

اپ هدر `X-Forwarded-Proto: https` را می‌فهمد؛ اگر nginx خارجی دارید حتماً این هدر را پاس بدهید.

## ۱۰. بکاپ و بازگردانی دیتابیس

```bash
# بکاپ
docker compose exec db pg_dump -U agahi agahi > backup-$(date +%F).sql

# بازگردانی
cat backup-2025-01-01.sql | docker compose exec -T db psql -U agahi agahi
```

فایل‌های آپلودی کاربران (عکس آگهی‌ها) در ولیوم `app_public` هستند؛ بکاپ آن:

```bash
docker run --rm -v agahi_app_public:/data -v $(pwd):/out alpine \
    tar czf /out/uploads-$(date +%F).tar.gz -C /data .
```

## ۱۱. عیب‌یابی

| مشکل | علت و راه‌حل |
|---|---|
| صفحه بالا می‌آید ولی عکس‌ها/استایل خراب است | `APP_URL` با آدرس مرورگر نمی‌خواند؛ اصلاحش کنید و `docker compose up -d` (بدون بیلد هم کافی است چون entrypoint دوباره cache می‌سازد) |
| خطای `database not reachable` در لاگ app | هنوز PostgreSQL آماده نشده؛ entrypoint خودش ۲ دقیقه منتظر می‌ماند، اگر ادامه داشت `docker compose logs db` را ببینید |
| بعد از recreate همه لاگ‌اوت شدند | `APP_KEY` خالی بوده؛ در `.env` کلید ثابت بگذارید |
| پورت 8080 اشغال است | در `.env` مقدار `WEB_PORT` را عوض کنید |
| پیامک ارسال نمی‌شود | `SMS_PROVIDER=log` است؛ برای ارسال واقعی `ippanel` + `IPPANEL_API_KEY` بگذارید و کانتینرها را ری‌استارت کنید |

## ۱۲. نکات فنی (برای تیم فنی)

- ایمیج `app` چندمرحله‌ای است: مرحلهٔ `assets` (بیلد Vite با Node 22)، مرحلهٔ `vendor` (composer install --no-dev)، مرحلهٔ نهایی php-fpm-alpine با اکستنشن‌های `gd, intl, pdo_pgsql, zip, opcache, pcntl`
- `OPcache.validate_timestamps=0` است؛ تغییر کد فقط با بیلد مجدد ایمیج اعمال می‌شود (الگوی immutable container)
- دو تارگت از یک Dockerfile ساخته می‌شود: `app` (php-fpm) و `web` (nginx که `public/` بیلدشده را داخل خودش دارد)
- فایل‌های آپلودی از طریق ولیوم مشترک `app_public` بین `app` و `web` به اشتراک گذاشته شده‌اند تا nginx بتواند مستقیم سروشان کند
- کرون‌جاب‌ها (انقضای آگهی، نردبان خودکار، یادآوری، پاک‌سازی) در کانتینر `scheduler` با `php artisan schedule:work` اجرا می‌شوند
- کانتینر `scheduler` صبر می‌کند تا جدول `migrations` موجود شود تا قبل از migration اجرای کرون شروع نشود
