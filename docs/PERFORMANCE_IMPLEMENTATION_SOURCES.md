# منابع فنی اصلاحات عملکرد

این منابع رسمی مبنای انتخاب cache نسخه‌دار، GZip و جست‌وجوی PostgreSQL در این تغییرات هستند.

| تصمیم | منبع | نکتهٔ اجرایی |
|---|---|---|
| Cache پاسخ و دادهٔ عمومی | [Django cache framework](https://docs.djangoproject.com/en/6.0/topics/cache/) | Django cache را در سطوح view، fragment و site پشتیبانی می‌کند؛ LocMem فقط per-process است و Redis برای cache مشترک production پیکربندی شده است. |
| فشرده‌سازی پاسخ HTML | [Django middleware: GZipMiddleware](https://docs.djangoproject.com/en/6.0/ref/middleware/) | GZipMiddleware برای clientهای دارای `Accept-Encoding: gzip` پاسخ‌های مناسب را فشرده می‌کند و باید پیش از middlewareهای پردازش body قرار گیرد. |
| جست‌وجوی رشدپذیر | [Django PostgreSQL full-text and trigrams](https://docs.djangoproject.com/en/6.0/ref/contrib/postgres/search/) | برای داده‌های بزرگ‌تر، PostgreSQL از `pg_trgm` و index GIN برای trigram search پشتیبانی می‌کند؛ migration پروژه در SQLite no-op است. |
| LCP و اولویت تصویر | [web.dev: Optimize Largest Contentful Paint](https://web.dev/articles/optimize-lcp) | تصویر LCP نباید lazy-load شود و `fetchpriority="high"` باید فقط برای کاندیدای محتمل LCP استفاده شود. |
