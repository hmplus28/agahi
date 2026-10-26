# گزارش سنجش سرعت و SEO

**تاریخ:** ۲۰ اوت ۲۰۲۶  
**دامنهٔ سنجش:** نسخهٔ PHP/Laravel در محیط محلی seeded، با پیکربندی SSR و cache دیتابیسی.  
**نویسنده:** Manus AI

## نتیجهٔ خلاصه

نسخهٔ فعلی از نظر ساختار **SSR، سبک‌بودن assetها و SEO فنی** در وضعیت مطلوبی قرار دارد. ممیزی Lighthouse روی صفحهٔ اصلی و جزئیات آگهی، امتیاز **۱۰۰ از ۱۰۰** در هر دو دستهٔ Performance و SEO ثبت کرد. در سنجش تکراری HTTP، همهٔ ۷۰ درخواست به هفت مسیر عمومی با پاسخ **۲۰۰** تمام شدند.

> این اعداد مربوط به Chromium و PHP development server محلی هستند؛ آن‌ها برای مقایسهٔ regression و شناسایی bottleneck معتبرند، اما جایگزین اندازه‌گیری نهایی روی دامنه، PHP-FPM و شبکهٔ هاست واقعی نیستند.

## نتایج Lighthouse

| صفحه | Performance | SEO | FCP | LCP | Speed Index | TBT | CLS | TTI |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| صفحهٔ اصلی | 100 | 100 | 0.8s | 0.8s | 0.8s | 0ms | 0 | 0.8s |
| جزئیات آگهی | 100 | 100 | 0.9s | 0.9s | 0.9s | 0ms | 0 | 0.9s |

Lighthouse وجود و اعتبار title، meta description، canonical، لینک‌های crawlable، robots.txt، وضعیت HTTP موفق و قابلیت index شدن را تأیید کرد. JavaScript اجرایی در صفحهٔ اصلی وجود ندارد؛ در نتیجه TBT صفر و SSR بدون وابستگی به frontend runtime محقق شده است.

## سنجش پاسخ سرور

هر مسیر ده بار با `curl` اندازه‌گیری شده است. TTFB و total در جدول زیر میانگین نمونه‌ها هستند.

| مسیر | پاسخ‌ها | حجم HTML | میانگین TTFB | میانگین زمان کل | درخواست ناموفق |
|---|---:|---:|---:|---:|---:|
| صفحهٔ اصلی | 10 | 3,824 B | 12.92ms | 13.29ms | 0 |
| جست‌وجو | 10 | 2,729 B | 15.28ms | 15.85ms | 0 |
| دسته‌بندی | 10 | 1,817 B | 13.49ms | 13.98ms | 0 |
| جزئیات آگهی | 10 | 4,875 B | 17.27ms | 17.83ms | 0 |
| robots.txt | 10 | 127 B | 11.17ms | 11.72ms | 0 |
| sitemap index | 10 | 269 B | 11.25ms | 11.81ms | 0 |
| sitemap دسته‌ها | 10 | 320 B | 10.55ms | 11.05ms | 0 |

فایل CSS اصلی تنها **7,248 بایت** است. این طراحی با عدم استفاده از SPA، bundle بزرگ JavaScript و assetهای third-party، مبنای مناسبی برای سرعت روی هاست اشتراکی فراهم می‌کند.

## کنترل‌های SEO تأییدشده

| کنترل | نتیجه |
|---|---|
| H1 یکتا در خانه | تأیید شد |
| H1 یکتا در جزئیات آگهی | تأیید شد |
| canonical در آگهی | تأیید شد |
| JSON-LD در آگهی | تأیید شد |
| Open Graph title در آگهی | تأیید شد |
| noindex برای جست‌وجو | تأیید شد |
| robots شامل Sitemap | تأیید شد |
| sitemap index شامل sitemapهای آگهی و دسته | تأیید شد |
| Content-Type XML و cache-control sitemap | تأیید شد |
| HTML اصلی بدون JavaScript اجرایی در خانه | تأیید شد |

## اصلاح‌های انجام‌شده در خلال تست

دو ایراد اثرگذار بر availability و SEO شناسایی و رفع شد. نخست، cache دیتابیسی Laravel برای payload مدل‌های Eloquent در این پیکربندی مناسب نبود و در page home و sitemap دسته‌ها می‌توانست به پاسخ ۵۰۰ منجر شود. cacheها به payload سادهٔ array تبدیل شدند و کلیدهای invalidation نیز به نسخهٔ جدید هماهنگ شدند. دوم، فایل static پیش‌فرض `public/robots.txt` مسیر پویای robots را shadow می‌کرد و Sitemap را نمایش نمی‌داد. فایل متداخل حذف شد تا route پویا، robots استاندارد و وابسته به `APP_URL` پاسخ دهد.

همچنین یک خطای syntax در Blade صفحهٔ دسته‌بندی رفع شد. مجموعهٔ آزمون‌ها پس از این اصلاح‌ها با **۱۴ تست و ۵۰ assertion** با موفقیت اجرا شد.

## الزامات استقرار برای حفظ نتیجه

در `.env` production، `APP_URL` باید روی دامنهٔ HTTPS واقعی تنظیم شود؛ زیرا canonical و Sitemap در robots با این مقدار ساخته می‌شوند. وب‌سرور باید document root را به `public/` متصل کند و `.htaccess` موجود اجازهٔ cache طولانی برای CSS، JavaScript، WebP و SVG و فشرده‌سازی پاسخ‌های متنی را بدهد. پس از هر deployment نیز دستورهای زیر اجرا شوند.

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## توصیهٔ نهایی پیش از انتشار دامنه

پس از اتصال دامنهٔ نهایی، یک ممیزی خارجی با Lighthouse یا PageSpeed Insights روی همان URL HTTPS انجام دهید، سپس `https://YOUR_DOMAIN/robots.txt`، `https://YOUR_DOMAIN/sitemap.xml` و یک URL واقعی آگهی را در Google Search Console ثبت و بررسی کنید. این مرحله latency شبکه، TLS، تنظیم واقعی PHP-FPM، OPcache و CDN احتمالی هاست را نیز پوشش می‌دهد.
