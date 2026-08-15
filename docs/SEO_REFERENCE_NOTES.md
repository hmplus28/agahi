# یادداشت‌های مرجع SEO

## منابع رسمی بررسی‌شده

| موضوع | نکتهٔ عملی برای پروژه | منبع |
|---|---|---|
| Sitemap | هر sitemap حداکثر ۵۰٬۰۰۰ URL یا ۵۰MB غیر فشرده دارد؛ برای سایت بزرگ باید فایل‌ها segment و با sitemap index معرفی شوند. URLها باید absolute و UTF-8 باشند و فقط URLهای مطلوب برای نمایش در نتایج—عملاً canonicalها—در sitemap قرار گیرند. `lastmod` فقط در صورت بازتاب تغییر معنادار و قابل‌اعتبار استفاده می‌شود. | [Google: Build and Submit a Sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap) |
| Robots meta | برای اعلام `noindex`، صفحه باید قابل crawl بماند تا موتور جست‌وجو meta robots را ببیند؛ در نتیجه private routeها و صفحات دارای `noindex` نباید به‌طور کورکورانه در `robots.txt` مسدود شوند. سیاست پروژه برای search و filter برابر `noindex,follow` خواهد بود. | [Google: Robots Meta Tags](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag) |

## تصمیم‌های اجرایی

پیاده‌سازی sitemap از sitemap index، sitemap دسته‌بندی، sitemap مکان و sitemapهای قطعه‌ای آگهی تشکیل می‌شود. ساخت URLهای هر sitemap به queryهای indexable و canonical محدود می‌شود، از `priority` و `changefreq` استفاده نمی‌شود، و خروجی با cache نسخه‌دار کوتاه‌مدت یا generation command از scan کاملِ غیرضروری جلوگیری می‌کند.

سیاست indexability در یک ماژول مرکزی قرار می‌گیرد و templateها، viewها و sitemap فقط از همان منبع تصمیم می‌گیرند. صفحات search و filter را `noindex,follow` نگه می‌داریم، اما به آن‌ها `Disallow` در robots.txt نمی‌دهیم تا directive قابل مشاهده باشد.

| فونت فارسی self-hosted | مخزن رسمی Vazirmatn فایل‌های webfont و مجوز OFL را منتشر می‌کند؛ فقط وزن regular WOFF2 برای کاهش حجم و با `font-display: swap` در static محلی استفاده می‌شود. | [rastikerdar/vazirmatn](https://github.com/rastikerdar/vazirmatn) |

| Provider SMS اختیاری | مستند Kavenegar مسیر `https://api.kavenegar.com/v1/{API-KEY}/sms/send.json` و ورودی‌های `receptor`، `message` و `sender` را برای ارسال سادهٔ POST/GET معرفی می‌کند؛ adapter پروژه فقط در صورت فعال‌سازی صریح محیط از آن استفاده می‌کند. | [Kavenegar REST](https://kavenegar.com/rest.html) |
