# ممیزی وابستگی‌ها و آمادگی Offline-First

## نتیجهٔ ممیزی

مسیرهای عمومی سایت هیچ asset خارجی، CDN، API محتوا، tracker، font remote یا JavaScript framework خارجی ندارند. CSS، JavaScript bootstrap آفلاین و Vazirmatn WOFF2 از `/static/` همان دامنه سرو می‌شوند. URLهای `schema.org` در JSON-LD صرفاً شناسهٔ معنایی هستند و browser برای نمایش صفحه به آن‌ها درخواست شبکه نمی‌زند.

| وابستگی یا مسیر | وضعیت پیش از مقاوم‌سازی | وضعیت نهایی |
|---|---|---|
| CSS / JavaScript / فونت | self-hosted | self-hosted + service worker cache |
| صفحات SSR، جست‌وجو، dashboard و Admin | Django + دیتابیس | بدون ارتباط خارجی runtime |
| Redis | پیش‌تر یک گزینهٔ احتمالی cache بود | کد پیکربندی و dependency آن حذف شده؛ فقط `FileBasedCache` محلی فعال است |
| `requests` | dependency هسته برای adapterها | از وابستگی‌های production حذف شد؛ client استاندارد Python در کد باقی مانده اما integrationها مسدودند |
| پرداخت ZarinPal/NextPay | شبکه‌ای و اختیاری | به‌صورت دائمی پیش از هر درخواست مسدود می‌شود |
| SMS Kavenegar | شبکه‌ای و اختیاری | به‌صورت دائمی disabled است و queue محلی حفظ می‌شود |
| SMTP email | قابل‌پیکربندی | console backend محلی به‌طور دائم استفاده می‌شود |
| انتقال backup | FTPS/FTP/filesystem اختیاری | backup محلی ادامه دارد؛ انتقال remote به‌طور دائم رد نرم می‌شود |

## حالت آفلاین دائمی

`OFFLINE_MODE` در سطح کد به‌طور ثابت `True` است و متغیر محیطی برای خاموش‌کردن آن وجود ندارد. هدف آن این نیست که server را بدون database اجرا کند؛ هدف، ادامهٔ امن سرویس هنگامی است که اینترنت عمومی قطع اما وب‌سرور، PostgreSQL و فایل‌های محلی در دسترس‌اند. برای استقلال واقعی، PostgreSQL، media، فایل‌های static و cache باید روی همان میزبان یا شبکهٔ خصوصی قرار داشته باشند.

## تست‌های انجام‌شده

| آزمون | نتیجه |
|---|---|
| اسکن assetهای `src`/`href` خارجی در قالب‌ها و static | هیچ موردی پیدا نشد |
| پیکربندی پیش‌فرض سایت | `FileBasedCache` محلی انتخاب شد و مسیر Redis وجود ندارد |
| تنظیم SMTP خارجی ظاهری | console email backend محلی انتخاب شد |
| پرداخت | پیش از انتخاب adapter خارجی به‌طور دائمی مسدود شد |
| پیامک | Disabled adapter و queue محلی |
| backup با `--encrypt --upload` | backup local موفق؛ انتقال remote نرم رد شد |
| service worker | cache مسیرهای عمومی و assetها؛ منع dashboard، admin، فرم‌ها و media مجوز |

جزئیات راه‌اندازی و wheelhouse در [OFFLINE_OPERATION.md](OFFLINE_OPERATION.md) است.
