# ممیزی وابستگی‌ها و آمادگی Offline-First

## نتیجهٔ ممیزی

مسیرهای عمومی سایت هیچ asset خارجی، CDN، API محتوا، tracker، font remote یا JavaScript framework خارجی ندارند. CSS، JavaScript bootstrap آفلاین و Vazirmatn WOFF2 از `/static/` همان دامنه سرو می‌شوند. URLهای `schema.org` در JSON-LD صرفاً شناسهٔ معنایی هستند و browser برای نمایش صفحه به آن‌ها درخواست شبکه نمی‌زند.

| وابستگی یا مسیر | وضعیت پیش از مقاوم‌سازی | وضعیت نهایی |
|---|---|---|
| CSS / JavaScript / فونت | self-hosted | self-hosted + service worker cache |
| صفحات SSR، جست‌وجو، dashboard و Admin | Django + دیتابیس | بدون ارتباط خارجی runtime |
| Redis | اختیاری از `CACHE_URL` | در `OFFLINE_MODE` نادیده گرفته و file/LocMem cache محلی فعال می‌شود |
| `requests` | dependency هسته برای adapterها | از `requirements/base.txt` حذف؛ client استاندارد Python فقط هنگام integration فعال استفاده می‌شود |
| پرداخت ZarinPal/NextPay | شبکه‌ای و اختیاری | در `OFFLINE_MODE` پیش از هر درخواست مسدود می‌شود |
| SMS Kavenegar | شبکه‌ای و اختیاری | adapter disabled و queue محلی حفظ می‌شود |
| SMTP email | قابل‌پیکربندی | در `OFFLINE_MODE` console backend محلی جایگزین می‌شود |
| انتقال backup | FTPS/FTP/filesystem اختیاری | نسخهٔ محلی ادامه دارد؛ انتقال remote در حالت آفلاین رد نرم می‌شود |

## حالت آفلاین

`OFFLINE_MODE=true` یک kill switch عملیاتی است. هدف آن این نیست که server را بدون database اجرا کند؛ هدف، ادامهٔ امن سرویس هنگامی است که اینترنت عمومی قطع اما وب‌سرور، PostgreSQL و فایل‌های محلی در دسترس‌اند. برای استقلال واقعی، PostgreSQL، media، فایل‌های static و cache باید روی همان میزبان یا شبکهٔ خصوصی قرار داشته باشند.

## تست‌های انجام‌شده

| آزمون | نتیجه |
|---|---|
| اسکن assetهای `src`/`href` خارجی در قالب‌ها و static | هیچ موردی پیدا نشد |
| تنظیم `OFFLINE_MODE=true` همراه با `CACHE_URL` غیرقابل‌دسترسی | `FileBasedCache` محلی انتخاب شد |
| تنظیم SMTP خارجی ظاهری همراه با `OFFLINE_MODE=true` | console email backend انتخاب شد |
| پرداخت در حالت آفلاین | پیش از انتخاب adapter خارجی مسدود شد |
| پیامک در حالت آفلاین | Disabled adapter و queue محلی |
| backup با `--encrypt --upload` در حالت آفلاین | backup local موفق؛ انتقال remote نرم رد شد |
| service worker | cache مسیرهای عمومی و assetها؛ منع dashboard، admin، فرم‌ها و media مجوز |

جزئیات راه‌اندازی و wheelhouse در [OFFLINE_OPERATION.md](OFFLINE_OPERATION.md) است.
