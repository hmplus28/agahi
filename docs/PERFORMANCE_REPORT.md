# گزارش کارایی و بارگذاری صفحات عمومی

## دامنهٔ آزمون

آزمون با `scripts/benchmark_public_requests.py` در محیط Django محلی انجام شد. هر endpoint یک درخواست cold و ۳۰ درخواست warm از طریق Django test client دریافت کرد. درخواست‌ها header `Accept-Encoding: gzip` داشتند. این اندازه‌گیری شامل شبکه، TLS، Passenger، PostgreSQL production، CDN یا فایل‌های media واقعی نیست؛ بنابراین **baseline کد و cache درون‌پردازه** است، نه تضمین latency اینترنتی production.

| Endpoint | وضعیت | حجم پاسخ | GZip | Cold (ms) | Warm P50 (ms) | Warm P95 (ms) | Warm Max (ms) |
|---|---:|---:|---|---:|---:|---:|---:|
| `/` | 200 | 1,778 B | بله | 114.10 | 0.69 | 0.96 | 1.45 |
| `/ads/` | 200 | 1,511 B | بله | 10.83 | 0.66 | 0.83 | 0.85 |
| `/ads/?q=تست` | 200 | 1,313 B | بله | 5.92 | 0.64 | 0.81 | 0.82 |
| `/healthz/` | 200 | 16 B | خیر | 0.49 | 0.43 | 0.65 | 0.99 |
| `/robots.txt` | 200 | 154 B | بله | 0.59 | 0.52 | 0.69 | 0.70 |
| `/sitemap.xml` | 200 | 246 B | بله | 1.70 | 0.51 | 0.68 | 0.69 |

## تفسیر

نتیجهٔ warm برای مسیرهای HTML عمومی در این محیط کمتر از 1 میلی‌ثانیه است. دلیل اصلی، پاسخ‌های cache‌شدهٔ عمومی نسخه‌دار، SSR بدون frontend سنگین، حجم HTML محدود و فعال بودن GZip است. مقدار cold صفحهٔ خانه شامل راه‌اندازی query و ساخت cache اولیه است و به‌صورت طبیعی از نمونه‌های warm بیشتر است.

| کنترل پیاده‌سازی‌شده | اثر مورد انتظار |
|---|---|
| Cache نسخه‌دار برای خانه، فهرست، taxonomy و sitemap | کاهش query و render تکراری؛ invalidation بعد از تغییر داده |
| Pagination و `select_related`/`prefetch_related` | جلوگیری از بارگذاری بدون محدودیت و N+1 در فهرست‌ها |
| WebP thumbnail/display و lazy loading تصاویر غیر اول | کاهش وزن media و بهبود LCP ادراکی |
| فونت Vazirmatn WOFF2 self-hosted با `font-display: swap` | حذف وابستگی runtime به CDN و جلوگیری از متن نامرئی |
| GZip و Conditional GET در Django + قالب Nginx static cache | کاهش payload متنی و استفاده بهتر از cache مرورگر |
| Sitemap بخش‌بندی‌شده و cache نسخه‌دار | جلوگیری از تولید مکرر XML در سایت بزرگ |
| آزمون budget query فهرست و جزئیات | جلوگیری از افزایش پنهان queryها؛ سقف‌های cold-cache به‌ترتیب ۱۰ و ۱۲ query هستند |

## کنترل‌های تأییدشده

آزمون کامل Django شامل **۳۴ آزمون موفق** است؛ این پوشش، cache فهرست و invalidation، GZip، جست‌وجوی فارسی، SEO/canonical/sitemap، rate limit، workflow پرداخت، retry پیامک، backup/restore/رمزنگاری/HMAC، انتقال filesystem و بودجهٔ query صفحات عمومی را در بر می‌گیرد. `manage.py check --deploy` نیز با تنظیمات production و کلید موقت قوی بدون هشدار اجرا شد.

## مرزهای پذیرش production

پیش از بهره‌برداری عمومی، benchmark باید یک‌بار روی دامنهٔ واقعی و با PostgreSQL production تکرار شود. از آن‌جا که Shared Hosting ممکن است محدودیت CPU و دیسک مشترک داشته باشد، معیار عملیاتی مناسب شامل P95 response time، نسبت cache hit، زمان `pg_dump`، حجم media و وضعیت Cron است. پیشنهاد می‌شود endpoint `healthz/` به monitor هاست افزوده شود و logهای backup و ارسال پیامک روزانه بازبینی شوند.

> این گزارش به‌جای پیش‌بینی بار اینترنتی، اندازه‌گیری بازتولیدپذیر کد در محیط آزمایشی را ثبت می‌کند. مقادیر production به داده، ظرفیت هاست و ترافیک واقعی وابسته خواهند بود.
