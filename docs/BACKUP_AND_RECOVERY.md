# راهنمای Backup و Recovery سامانه آگهی

## هدف و مدل‌های پشتیبانی‌شده

پیاده‌سازی backup سه لایه دارد تا هم نیاز عملیاتی روزمره و هم سناریوی بازیابی پس از حادثه پوشش داده شود. همهٔ dumpها ابتدا **محلی، فشرده، اعتبارسنجی‌شده و دارای SHA-256** هستند. انتقال برون‌سایتی فقط برای نسخهٔ رمزنگاری‌شده مجاز است. فایل‌های backup و manifest باید در مسیری خارج از `public_html` با مجوز مالک محدود نگهداری شوند.

| مدل | خروجی | کاربرد | فرمان |
|---|---|---|---|
| A — محلی دستی/خودکار | `*.sql.gz` و manifest | بازیابی سریع روی همان میزبان | `manage.py backup_database` |
| B — محلی + برون‌سایتی رمزنگاری‌شده | نسخهٔ محلی + `*.sql.gz.enc` منتقل‌شده با FTPS یا mount خصوصی | تاب‌آوری در برابر حذف یا خرابی میزبان | `manage.py backup_database --encrypt --upload` |
| C — خروجی دستی + زمان‌بندی | همان مدل A یا B، از طریق Cron روزانه | اجرای بدون دخالت روزمره | فرمان Cron پایین |

> خروجی PostgreSQL در این پروژه **plain SQL** است و با `psql` به مقصد صریح restore می‌شود. مستند PostgreSQL توضیح می‌دهد که `pg_dump` خروجی SQL سازگار ایجاد می‌کند و restore این قالب با `psql` انجام می‌شود؛ restore فقط باید از backup مورداعتماد انجام شود، زیرا SQL اجرا می‌شود. [1]

## تنظیمات محیطی

ابتدا مقادیر زیر را در فایل محیطی خارج از repository قرار دهید. برای محیط production مقدارهای نمونه را با مسیر و credential واقعی جایگزین کنید و هیچ‌گاه آن‌ها را در Git ثبت نکنید.

```dotenv
BACKUP_DIR=/home/USERNAME/private/agahi-backups
BACKUP_RETENTION_DAYS=30
BACKUP_RETENTION_COUNT=30
BACKUP_ENCRYPTION_KEY=یک-کلید-طولانی-تصادفی-و-محرمانه

# فقط برای مدل B
BACKUP_REMOTE_PROVIDER=ftps
BACKUP_REMOTE_HOST=backup.example.net
BACKUP_REMOTE_PORT=21
BACKUP_REMOTE_USERNAME=...
BACKUP_REMOTE_PASSWORD=...
BACKUP_REMOTE_PATH=agahi-backups
```

> رمزنگاری از `openssl enc` با AES-256-CBC، salt، PBKDF2 و iteration صریح استفاده می‌کند. مستند OpenSSL بیان می‌کند که گزینهٔ `-pbkdf2` برای مشتق‌سازی کلید با PBKDF2 است. [3]

## اجرای دستی

### مدل A: backup محلی

```bash
cd /home/USERNAME/agahi
/home/USERNAME/venv/bin/python manage.py backup_database
```

این فرمان از PostgreSQL با `pg_dump` یا از SQLite با `iterdump` خروجی SQL تولید می‌کند، آن را با gzip فشرده می‌سازد، ساختار archive را کنترل می‌کند و کنار فایل manifest شامل checksum ایجاد می‌کند.

### مدل B: نسخهٔ رمزنگاری‌شده و انتقال‌شده

```bash
/home/USERNAME/venv/bin/python manage.py backup_database --encrypt --upload
```

گزینهٔ `--upload` بدون `--encrypt` عمداً رد می‌شود. providerهای `ftps`، `ftp` و `filesystem` (برای mount خصوصی یا آزمون) پشتیبانی می‌شوند. برای محیط واقعی، **FTPS** را بر FTP ترجیح دهید.

### retention

```bash
/home/USERNAME/venv/bin/python manage.py backup_database --retention-only --keep-days 30 --keep-count 30
```

سیاست retention هم سن فایل و هم حداقل تعداد نسخهٔ جدید را لحاظ می‌کند تا حذف زودهنگام رخ ندهد.

## زمان‌بندی cPanel (مدل C)

در بخش **Cron Jobs** از cPanel یک اجرای روزانه در ساعت کم‌ترافیک تعریف کنید. cPanel توصیه می‌کند فاصله‌ای کافی میان jobها قرار گیرد تا اجرای پیشین تمام شود؛ command backup نیز lock فایل دارد تا اجرای هم‌پوشان رد شود. [2]

| سناریو | نمونهٔ زمان | Command |
|---|---:|---|
| محلی روزانه | 03:15 | `15 3 * * * cd /home/USERNAME/agahi && /home/USERNAME/venv/bin/python manage.py backup_database >> /home/USERNAME/private/agahi-backup.log 2>&1` |
| محلی + نسخهٔ رمزنگاری‌شدهٔ برون‌سایتی | 03:30 | `30 3 * * * cd /home/USERNAME/agahi && /home/USERNAME/venv/bin/python manage.py backup_database --encrypt --upload >> /home/USERNAME/private/agahi-backup.log 2>&1` |
| یادآوری‌های انقضای آگهی + ارسال پیامک | 09:10 | `10 9 * * * cd /home/USERNAME/agahi && /home/USERNAME/venv/bin/python manage.py send_notifications --type all --dispatch >> /home/USERNAME/private/agahi-notifications.log 2>&1` |

مسیرهای `USERNAME` و virtual environment را مطابق هاست خودتان تغییر دهید. logها را نیز خارج از web root نگه دارید.

## روند Restore امن

فرمان restore به‌طور پیش‌فرض تنها archive را validate می‌کند و هیچ دیتابیسی را تغییر نمی‌دهد:

```bash
/home/USERNAME/venv/bin/python manage.py restore_database --input /home/USERNAME/private/agahi-backups/agahi-....sql.gz
```

برای SQLite، مقصد باید **صریح** تعیین شود:

```bash
/home/USERNAME/venv/bin/python manage.py restore_database \
  --input /home/USERNAME/private/agahi-backups/agahi-....sql.gz \
  --target-sqlite /home/USERNAME/private/restore-check.sqlite3 \
  --confirm-restore
```

برای PostgreSQL نیز connection string مقصد باید صریح باشد. پیش از اجرای آن، سرویس را در maintenance mode قرار دهید، از دیتابیس مقصد snapshot جداگانه بگیرید، ابتدا restore را روی staging آزمایش کنید و سپس کنترل‌های Django و نمونه‌ای از داده‌ها را بررسی کنید.

```bash
/home/USERNAME/venv/bin/python manage.py restore_database \
  --input /home/USERNAME/private/agahi-backups/agahi-....sql.gz \
  --target-postgres-dsn 'postgresql://USER:PASSWORD@HOST:5432/agahi_restore' \
  --confirm-restore
```

برای archive رمزنگاری‌شدهٔ `.enc` همان فرمان استفاده می‌شود؛ کلید محیطی لازم است و فایل موقتِ رمزگشایی‌شده پس از پایان کار پاک می‌شود.

## آزمون دوره‌ای پیشنهادشده

هر ماه یک restore آزمایشی به مقصد staging انجام دهید. معیار موفقیت شامل اعتبارسنجی archive، اجرای migration/check، امکان ورود مدیر، شمارش نمونه‌ای آگهی‌ها و بررسی سلامت `healthz/` است. داشتن فایل backup بدون آزمون restore، تضمین بازیابی نیست.

## منابع

[1] [PostgreSQL — pg_dump](https://www.postgresql.org/docs/current/app-pgdump.html)

[2] [cPanel — Cron Jobs](https://docs.cpanel.net/cpanel/advanced/cron-jobs/)

[3] [OpenSSL — enc](https://docs.openssl.org/3.3/man1/openssl-enc/)
