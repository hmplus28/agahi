# راهنمای بهره‌برداری Offline-First

## نتیجهٔ عملی

هستهٔ سایت شامل صفحات عمومی، جست‌وجو، ثبت و مدیریت آگهی، حساب کاربری، پنل مدیریت، فایل‌های static، فونت فارسی و cache به هیچ درخواست runtime اینترنتی وابسته نیست. تمام CSS، JavaScript و فونت موردنیاز از همان دامنه سرو می‌شوند. service worker نیز assetهای اصلی و صفحات عمومی بازدیدشده را در مرورگر cache می‌کند تا هنگام قطع شبکه، محتوای قبلاً بازشده همچنان قابل مشاهده باشد.

| نوع قابلیت | رفتار در حالت عادی | رفتار با `OFFLINE_MODE=true` |
|---|---|---|
| صفحات SSR، جست‌وجو و مدیریت آگهی | از PostgreSQL و assetهای محلی اجرا می‌شود | بدون تغییر، تا زمانی که سرور و دیتابیس محلی در دسترس باشند |
| CSS، JS و فونت Vazirmatn | self-hosted از `/static/` | از cache مرورگر در بازدیدهای بعدی نیز قابل استفاده است |
| cache | Redis اختیاری یا file/local cache | Redis نادیده گرفته می‌شود؛ `CACHE_DIR` یا LocMem استفاده می‌شود |
| پرداخت | فقط هنگام پیکربندی gateway انجام می‌شود | شروع و verify پرداخت اینترنتی مسدود و سفارش برای پیگیری ثبت می‌شود |
| SMS | فقط با provider و credential فعال ارسال می‌شود | provider غیرفعال می‌شود و queue دست‌نخورده باقی می‌ماند |
| Email | backend انتخاب‌شدهٔ محیط | به console backend محلی تغییر می‌کند |
| backup remote | انتقال FTPS/filesystem اختیاری | backup محلی ادامه دارد؛ انتقال remote عمداً متوقف می‌شود |

## فعال‌سازی سریع هنگام اختلال اینترنت

در فایل محیط production، مقدار زیر را قرار دهید و سپس Passenger/WSGI را reload کنید:

```dotenv
OFFLINE_MODE=true
CACHE_DIR=/home/USERNAME/private/agahi-cache
```

پس از بازگشت ارتباط، تنها در صورت نیاز به gateway، SMS، email یا انتقال remote مقدار را به `false` برگردانید. روشن‌بودن حالت آفلاین مانع فعالیت داده‌های محلی نمی‌شود؛ فقط خروجی‌ها و ارتباط‌های خارج از سرور را محافظه‌کارانه قطع می‌کند.

> PostgreSQL باید روی همان سرور یا یک شبکهٔ خصوصی قابل‌دسترسی باشد. اگر database روی سرویس اینترنتی خارجی باشد، هیچ تنظیم نرم‌افزاری نمی‌تواند سایت را در قطع آن سرویس عملیاتی نگه دارد؛ برای استقلال واقعی، دیتابیس و media را محلی یا روی شبکهٔ خصوصی مستقر کنید.

## آماده‌سازی پیشگیرانهٔ نصب بدون اینترنت

پیش از اختلال، روی یک سیستم دارای دسترسی شبکه، wheelhouse بسازید و آن را در مسیر خصوصی هاست نگهداری کنید:

```bash
cd /home/USERNAME/agahi
deployment/build_offline_wheelhouse.sh /home/USERNAME/private/agahi-wheelhouse
```

روی سرور بدون اینترنت، dependencyهای هسته بدون دسترسی به PyPI نصب می‌شوند:

```bash
cd /home/USERNAME/agahi
VENV_PYTHON=/home/USERNAME/venv/bin/python \
  deployment/install_offline_dependencies.sh /home/USERNAME/private/agahi-wheelhouse
```

فایل `requirements/base.txt` تنها هسته را دارد. `requests` و `redis` به `requirements/integrations.txt` منتقل شده‌اند و فقط در صورت نیاز به integrationهای خارجی نصب می‌شوند. adapterهای پرداخت و SMS پروژه از client استاندارد Python استفاده می‌کنند؛ بنابراین خود هسته برای اجرا به آن package نیاز ندارد.

## Cache مرورگر و صفحات آفلاین

پس از یک بازدید موفق، service worker مسیرهای عمومی و assetهای محلی را نگه می‌دارد. در قطع ارتباط، درخواست صفحهٔ عمومیِ قبلاً بازشده از cache پاسخ می‌گیرد و صفحهٔ جدیدِ cache‌نشده به `/offline/` هدایت می‌شود. مسیرهای احراز هویت، dashboard، فرم‌های POST و media مجوز عمداً cache نمی‌شوند تا اطلاعات خصوصی در browser cache نشود.

کاربرانی که برای نخستین‌بار و بدون هیچ اتصال به سایت می‌آیند، نمی‌توانند سایت را ببینند؛ browser ابتدا باید service worker و assetها را از همان دامنه دریافت کرده باشد. این محدودیت ذاتی یک وب‌سایت است و جایگزین نصب native app نیست.

## کنترل‌های پیش از بهره‌برداری

| کنترل | فرمان یا اقدام |
|---|---|
| بررسی Django | `python manage.py check --deploy` |
| آزمون سایت و offline guardها | `python manage.py test` |
| جمع‌آوری asset محلی | `python manage.py collectstatic --noinput` |
| آزمایش مرورگر | یک بار صفحهٔ خانه و چند آگهی را باز کنید، سپس اتصال مرورگر را قطع و refresh کنید |
| cache محلی | اطمینان از قابل‌نوشتن بودن `CACHE_DIR` خارج از `public_html` |
| backup محلی | `python manage.py backup_database` |

## مرزهای آگاهانه

پرداخت اینترنتی، ارسال SMS واقعی، ایمیل SMTP خارجی، upload به remote backup و Redis روی میزبان دوردست ذاتاً به شبکه نیاز دارند. در این طراحی، این قابلیت‌ها مسیر حیاتی سایت نیستند و با قطع اتصال سبب timeout یا خرابی صفحه‌های اصلی نمی‌شوند. queue و رکوردهای مالی محلی حفظ می‌شوند تا اپراتور پس از بازگشت شبکه آن‌ها را بررسی یا ارسال کند.
