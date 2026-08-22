# MASTER IMPLEMENTATION PROMPT

# پیاده‌سازی صفر تا صد سامانه جامع آگهی

تو مسئول **طراحی، معماری، پیاده‌سازی، تست، بهینه‌سازی و آماده‌سازی Production** یک سامانه جامع ثبت و مدیریت آگهی فارسی هستی.

این پروژه باید از صفر تا نسخه Production-Ready توسط تو پیاده‌سازی شود.

فقط Prototype یا Demo نساز.

خروجی نهایی باید یک پروژه واقعی، تمیز، تست‌شده، SEO-Friendly، Performance-Oriented و قابل استقرار روی **Python Shared Hosting / cPanel / Passenger / WSGI** باشد.

در طول کار تا زمانی که با یک مانع واقعی و غیرقابل حل بدون اطلاعات خارجی مواجه نشده‌ای، برای تأیید مراحل از کاربر سؤال نپرس.

تصمیم‌های فنی مشخص‌شده در این سند را تغییر نده مگر اینکه یک مشکل فنی واقعی وجود داشته باشد. در آن صورت ابتدا دلیل را مستند کن و کم‌ریسک‌ترین جایگزین را انتخاب کن.

---

# 1. هدف محصول

یک سامانه جامع Classified Ads فارسی طراحی و پیاده‌سازی کن که کاربران بتوانند:

* ثبت‌نام و ورود کنند.
* آگهی ثبت کنند.
* آگهی‌های خود را مشاهده و مدیریت کنند.
* آگهی را ویرایش کنند.
* وضعیت تأیید آگهی را مشاهده کنند.
* مجوز ارسال کنند.
* آگهی را تمدید کنند.
* سرویس‌های ویژه بخرند.
* فاکتور مشاهده کنند.
* پرداخت انجام دهند.
* تیکت ارسال کنند.
* آگهی‌های خود را حذف کنند.

مدیران سامانه باید بتوانند:

* آگهی‌ها را مدیریت کنند.
* آگهی را تأیید یا رد کنند.
* آگهی را نیازمند مجوز کنند.
* آگهی را فعال یا غیرفعال کنند.
* آگهی حذف‌شده را مشاهده و Restore کنند.
* دسته‌بندی‌ها را مدیریت کنند.
* کشور، استان و شهر را مدیریت کنند.
* تعرفه‌ها را مدیریت کنند.
* پرداخت‌ها را بررسی کنند.
* گزارش‌های تخلف را مدیریت کنند.
* لغات غیرمجاز را مدیریت کنند.
* تیکت کاربران را پاسخ دهند.
* پیامک مربوط به آگهی‌های منقضی را مدیریت کنند.
* تنظیمات عمومی سایت را کنترل کنند.

هدف اصلی فنی:

**SEO بسیار قوی + سرعت بسیار بالا + مصرف پایین منابع + قابلیت اجرا روی Python Hosting ارزان.**

---

# 2. صفحات نمونه مرجع

این صفحات فقط به‌عنوان مرجع Business Logic، فیلدها و UI مورد استفاده قرار بگیرند:

پنل مدیریت:

https://test.agahi2.ir/admin-ads.htm

صفحه تعرفه:

https://test.agahi2.ir/admin-tarefe.htm

مدیریت دسته‌بندی:

https://test.agahi2.ir/admin-categury.htm

مدیریت پرداخت:

https://test.agahi2.ir/admin-pay.htm

تیکت مدیریت:

https://test.agahi2.ir/admin-tiket.htm

لغات غیرمجاز:

https://test.agahi2.ir/admin-geirmojaz.htm

ارسال پیام تمدید:

https://test.agahi2.ir/admin.tamdid.htm

پنل کاربر:

https://test.agahi2.ir/user-ads.htm

ثبت آگهی:

https://test.agahi2.ir/user-sabt-agahi.htm

پرداخت کاربر:

https://test.agahi2.ir/user-pay.htm

تیکت کاربر:

https://test.agahi2.ir/user-ticket.htm

فاکتور:

https://test.agahi2.ir/factor.htm

صفحه آگهی:

https://test.agahi2.ir/ui-ads.htm

صفحه اصلی:

https://test.agahi2.ir/ui-home.htm

این صفحات را عیناً Clone نکن.

Business Ruleها و فیلدهای لازم را استخراج کن، ولی UI جدید باید:

* مدرن‌تر باشد.
* Responsive باشد.
* RTL و فارسی باشد.
* ساده‌تر باشد.
* سبک‌تر باشد.
* سریع‌تر باشد.
* Semantic HTML داشته باشد.
* از نظر SEO بهتر باشد.
* از نظر Accessibility قابل قبول باشد.

---

# 3. تکنولوژی‌های قطعی

Backend:

Django 5.2.17 LTS

معماری:

Django Modular Monolith

Frontend Rendering:

Django Templates

JavaScript:

حداقلی

برای تعاملات کوچک ابتدا Vanilla JavaScript استفاده کن.

HTMX فقط در جاهایی که واقعاً باعث بهبود UX و کاهش کد می‌شود استفاده شود.

SPA نساز.

ممنوع:

React

Next.js

Vue SPA

Nuxt

Angular

Frontend API Architecture غیرضروری

Database:

PostgreSQL

Driver:

psycopg 3

Image Processing:

Pillow

Production Server:

WSGI / Passenger روی Python Hosting

Static Files:

collectstatic + Web Server

Development:

Python virtualenv

---

# 4. اصل معماری پروژه

سیستم را Modular Monolith نگه دار.

Microservice نساز.

ساختار پیشنهادی:

project/
config/
core/
accounts/
ads/
taxonomy/
locations/
billing/
moderation/
support/
notifications/
seo/
templates/
static/
media/

مسئولیت‌ها باید واضح و جدا باشند.

Business Logic پیچیده را داخل Template یا Viewهای حجیم قرار نده.

در صورت نیاز Service Layer سبک ایجاد کن.

مثلاً:

ads/services/
billing/services/
notifications/services/

ولی از abstraction غیرضروری خودداری کن.

---

# 5. Config پروژه

Config را حداقل به این بخش‌ها تقسیم کن:

config/settings/base.py

config/settings/development.py

config/settings/production.py

اطلاعات حساس از Environment Variable خوانده شوند.

هیچ رمز یا Secret داخل Repository قرار نگیرد.

مواردی مانند:

SECRET_KEY

DATABASE_NAME

DATABASE_USER

DATABASE_PASSWORD

DATABASE_HOST

DATABASE_PORT

ALLOWED_HOSTS

SMS credentials

Payment credentials

از Environment Variables دریافت شوند.

یک فایل:

.env.example

ایجاد کن ولی Secret واقعی داخل آن نباشد.

---

# 6. پایگاه داده

از PostgreSQL استفاده کن.

SQLite فقط برای تست‌های کاملاً محلی در صورت ضرورت قابل استفاده است ولی Production هرگز SQLite نباشد.

Database schema را با Migrationهای استاندارد Django ایجاد کن.

روی فیلدهای پرتکرار Index مناسب ایجاد کن.

به‌صورت خاص Query Patternهای زیر را در طراحی Index لحاظ کن:

status + published_at

status + category

status + city

category + city + status

user + status

expires_at + status

is_featured + sort_at

slug

code

mobile

از ایجاد Index بی‌دلیل روی همه فیلدها خودداری کن.

---

# 7. Custom User Model

از ابتدای پروژه Custom User داشته باش.

بعداً User Model را تغییر نده.

از AbstractUser یا AbstractBaseUser مناسب استفاده کن.

حداقل اطلاعات:

id

mobile

email

first_name

last_name

is_active

is_staff

date_joined

last_login

موبایل باید Normalize شود.

ترجیحاً Login اصلی:

mobile + password

باشد.

ایمیل اختیاری باشد مگر Business Rule دیگری نیاز داشته باشد.

زیرساخت را طوری طراحی کن که SMS OTP در آینده قابل اضافه شدن باشد.

فعلاً Authentication نباید وابسته به سرویس SMS پولی باشد.

---

# 8. زبان و Locale

کل UI فارسی و RTL باشد.

Backend timestampها را استاندارد و timezone-aware ذخیره کن.

تاریخ واقعی دیتابیس را Gregorian/UTC نگه دار.

اگر تاریخ شمسی لازم است، فقط هنگام Presentation تبدیل کن.

هرگز تاریخ شمسی را به‌صورت رشته به‌عنوان تاریخ اصلی Database ذخیره نکن.

---

# 9. مدل Profile

Profile شامل اطلاعات تکمیلی کاربر باشد.

مانند:

business_name

address

province

city

postal_code

avatar در صورت نیاز

و سایر اطلاعاتی که واقعاً در محصول استفاده می‌شود.

از تکرار اطلاعاتی که داخل User وجود دارد خودداری کن.

---

# 10. Category

دسته‌بندی به‌صورت Tree طراحی شود.

مدل:

Category

فیلدها:

id

parent

title

slug

description

seo_title

seo_description

sort_order

is_active

created_at

updated_at

ساختار باید تعداد سطح نامحدود داشته باشد.

مثال:

خدمات

→ تعمیر لوازم

→ برقی و گازی

→ بخاری

از ایجاد CategoryLevel1، CategoryLevel2 و ... خودداری کن.

---

# 11. Locations

مدل‌های زیر را بساز:

Country

Province

City

Relationship:

Country
Province
City

فیلدهای عمومی:

name

slug

is_active

sort_order

مدیریت CRUD کامل فراهم کن.

Location دارای آگهی نباید Hard Delete شود.

به‌جای آن:

is_active=False

استفاده کن.

---

# 12. مدل اصلی Ad

مدل Ad باید حداقل شامل موارد زیر باشد:

id

code

user

title

normalized_title

description

normalized_description

price

full_name

business_name

country

province

city

address

mobile_1

show_mobile_1

mobile_2

phone_1

phone_2

email

category

referrer

source

submit_ip

status

created_at

updated_at

published_at

expires_at

sort_at

last_ladder_at

views_count

is_featured

is_colored

is_urgent

auto_ladder

deleted_at

Slug مناسب نیز داشته باشد.

code باید یک شناسه عمومی مناسب و Unique باشد.

---

# 13. وضعیت آگهی

Statusها را با TextChoices پیاده‌سازی کن.

حداقل:

DRAFT

PENDING_APPROVAL

ACTIVE

NEEDS_PERMIT

INACTIVE

EXPIRED

DELETED

Business State Transitionها مشخص و کنترل‌شده باشند.

مثلاً:

DRAFT
→ PENDING_APPROVAL
→ ACTIVE

اگر کاربر آگهی ACTIVE را ویرایش کند:

ACTIVE
→ PENDING_APPROVAL

مدیر می‌تواند:

PENDING_APPROVAL
→ ACTIVE

یا:

PENDING_APPROVAL
→ NEEDS_PERMIT

یا:

PENDING_APPROVAL
→ INACTIVE

کند.

حذف به صورت Soft Delete انجام شود.

Hard Delete آگهی‌های واقعی انجام نده.

---

# 14. AdStatusHistory

تمام تغییرات مهم وضعیت را Log کن.

مدل:

AdStatusHistory

ad

from_status

to_status

changed_by

reason

created_at

این History باید در Admin قابل مشاهده باشد.

---

# 15. منبع ثبت آگهی

Source را Enum کن.

حداقل:

USER_PANEL

PUBLIC_FORM

ADMIN

IMPORT

UI نباید منبع اصلی تشخیص Source باشد.

رنگ‌بندی فقط برای Presentation استفاده شود.

---

# 16. ثبت آگهی

فرم ثبت آگهی باید بخش‌های زیر را داشته باشد.

عنوان:

Required

حداکثر 300 کاراکتر.

توضیحات:

Required

حداکثر 6000 کاراکتر.

کلمات کلیدی:

حداکثر 11 مورد.

قیمت:

اختیاری.

اگر قیمت خالی باشد در سایت:

«توافقی»

نمایش بده.

مشخصات:

نام و نام خانوادگی

نام واحد تجاری

Location:

کشور

استان

شهر Required

آدرس

Contact:

موبایل اول Required

نمایش موبایل اول

موبایل دوم

تلفن اول

تلفن دوم

ایمیل

دسته‌بندی:

Hierarchical category selection.

تصاویر.

لینک‌ها.

اطلاعات مجوز در صورت نیاز.

---

# 17. Persian Normalization

یک Utility مرکزی برای Normalize متن فارسی بنویس.

حداقل:

ي → ی

ك → ک

Normalization فاصله

Normalization نیم‌فاصله

Trim

Collapse whitespace

Normalization اعداد در صورت نیاز

از این Utility برای:

Search

Duplicate Detection

Forbidden Words

Slug generation در صورت نیاز

استفاده کن.

منطق Normalize نباید در چند جای پروژه Duplicate شود.

---

# 18. Duplicate Detection

قبل از ثبت یا ویرایش آگهی Duplicate Detection انجام بده.

حداقل روی:

normalized_title

normalized_description

بررسی شود.

برای lookup سریع امکان ذخیره Hash را در نظر بگیر:

normalized_title_hash

normalized_description_hash

از Hash مناسب مانند SHA-256 استفاده کن.

False Positive غیرمنطقی ایجاد نکن.

ادمین در صورت نیاز باید بتواند Duplicate Check را Override کند.

---

# 19. Forbidden Words

مدل:

ForbiddenWord

word

normalized_word

is_active

created_at

updated_at

در زمان ثبت/ویرایش حداقل بررسی کن:

title

description

keywords

business_name

اگر مورد غیرمجاز وجود داشت، Validation Error فارسی و واضح بده.

لیست Forbidden Words از Admin قابل مدیریت باشد.

---

# 20. تصاویر آگهی

اصل مهم:

**نسخه Original تصویر هرگز به صورت دائمی ذخیره نشود.**

Upload اولیه فقط Temporary است.

Pipeline:

Upload

→ File Validation

→ MIME Validation

→ Image Decode

→ EXIF Orientation Correction

→ Strip unnecessary metadata

→ Resize

→ Optimize

→ Convert to WebP

→ Save optimized files

→ Remove original/temp file

تصاویر بسیار بزرگ باید Resize شوند.

حداکثر Dimension تصویر بزرگ:

حدود 1280 تا 1600 پیکسل.

Quality WebP:

تقریباً 78 تا 82

پس از تست بصری.

برای سرعت بهتر ترجیحاً دو نسخه کاملاً Optimize شده نگهداری کن:

thumbnail حدود 480px

display حدود 1280px

هیچ نسخه Original نگهداری نشود.

مدل:

AdImage

ad

image_thumb

image_display

width

height

sort_order

is_primary

created_at

حداکثر تعداد اولیه تصاویر مطابق Requirement نمونه 5 تصویر باشد، ولی Limit از Setting/Tariff خوانده شود و در مدل Hardcode نشود.

Upload غیرتصویری Reject شود.

تصاویر Decompression Bomb و تصاویر بسیار بزرگ Validate شوند.

---

# 21. نمایش تصاویر

HTML باید width و height داشته باشد تا Layout Shift ایجاد نشود.

برای Card:

thumbnail استفاده شود.

برای Gallery:

display استفاده شود.

تصاویر خارج از viewport:

loading="lazy"

داشته باشند.

تصویر اصلی صفحه آگهی Lazy Load نشود.

در صورت نیاز برای تصویر LCP:

fetchpriority="high"

قرار بده.

Gallery را بدون کتابخانه JavaScript سنگین طراحی کن.

---

# 22. لینک‌های آگهی

مدل:

AdLink

ad

type

url

sort_order

is_active

تا پنج لینک در تنظیمات اولیه مجاز باشد.

Limit قابل تنظیم باشد.

لینک User Generated:

rel="ugc"

داشته باشد.

اگر placement پولی است:

rel="ugc sponsored"

قرار بده.

---

# 23. Permit

مدل:

AdPermit

ad

permit_number

issuer

issued_at

image

status

admin_note

created_at

updated_at

Status:

PENDING

APPROVED

REJECTED

Workflow:

Admin → NEEDS_PERMIT

User → Permit Upload

Admin → Review

Approved → امکان ACTIVE

در Upload مجوز نیز تصاویر باید Validate و Optimize شوند.

---

# 24. expiration

هر Ad دارای:

expires_at

باشد.

Expiration را از created_at به صورت Hardcoded محاسبه نکن.

Package/Tariff تعیین کند مدت اعتبار چیست.

مثلاً:

Free → 1 month

Annual → 1 year

مقادیر مدت از تنظیمات/تعرفه خوانده شوند.

Cron/Management Command برای تغییر وضعیت آگهی‌های منقضی ایجاد کن.

---

# 25. Expired Ad

آگهی منقضی باید همچنان قابل مشاهده باشد مگر اینکه Business Setting خلاف آن باشد.

در صفحه پیام واضح:

«این آگهی منقضی شده است.»

نمایش بده.

اطلاعات تماس آگهی منقضی بر اساس Business Rule Mask شود.

اصل داده Database را تغییر نده.

Masking هنگام Rendering باشد.

شماره‌ها و سایر اطلاعات حساس قابل نمایش نباشند.

برای SEO یک Policy مرکزی ایجاد کن که تعیین کند آگهی منقضی:

index

یا

noindex

باشد.

Default مناسب:

EXPIRED → noindex, follow

ACTIVE → index, follow

این Rule از یک function/service مرکزی SEO خوانده شود، نه در چند Template پراکنده.

---

# 26. Low View

«فاقد بازدید» Status مستقل نباشد.

به‌عنوان Filter محاسبه شود.

مثلاً:

old + views_count < threshold

Threshold قابل تنظیم باشد.

---

# 27. View Counter

بازدید آگهی را پیاده‌سازی کن.

هر Refresh ساده نباید بی‌نهایت Count تولید کند.

حداقل یک مکانیسم Deduplication سبک بر اساس Session/IP/time-window ایجاد کن.

از Query update اتمیک با F expression استفاده کن.

Counter نباید باعث Race Condition شود.

---

# 28. User Panel

پنل کاربر حداقل منوهای:

داشبورد

آگهی‌های من

ثبت آگهی

پرداخت‌ها

فاکتورها

تیکت‌ها

پروفایل

را داشته باشد.

صفحه آگهی‌های من دارای:

تاریخ

تصویر

عنوان

کد

بازدید

معرف

خدمات

وضعیت

Actions

باشد.

Filter بر اساس Status وجود داشته باشد.

---

# 29. Expired User Actions

اگر آگهی EXPIRED باشد:

Actions زیر غیرفعال:

Edit

Ladder

Auto Ladder

Actions زیر مجاز:

Renew

Upload Permit

Invoice

Payment

Delete

این Restriction در Backend enforce شود.

فقط Disable کردن Button کافی نیست.

---

# 30. Admin Architecture

دو نوع Admin داشته باش.

Django Admin استاندارد برای CRUDهای ساده:

Category

Country

Province

City

ForbiddenWord

Tariff

Settings

و Custom Management Dashboard برای Workflowهای پیچیده:

Ads

Payments

Reports

Tickets

Expired SMS

Statistics

Admin UI فارسی و RTL باشد.

---

# 31. Admin Dashboard

Dashboard شامل آمار:

تعداد کل آگهی

Pending

Active

Expired

Needs Permit

Inactive

Deleted

Payments Today

Open Tickets

New Reports

باشد.

Queryهای Dashboard باید Optimize و در صورت نیاز Cache شوند.

---

# 32. Admin Ad Management

صفحه مدیریت آگهی شامل Tab/Counter:

همه

نیاز به تأیید

نیاز به تمدید

نیاز به مجوز

فعال

غیرفعال

حذف

باشد.

جدول:

تاریخ

عنوان

کد

بازدید

نویسنده

معرف

خدمات

وضعیت

Actions

داشته باشد.

Search حداقل روی:

code

title

description

mobile

business_name

city

user

انجام شود.

Page size قابل تنظیم باشد.

نمونه اولیه می‌تواند 50/100/200/400 داشته باشد.

Pagination حتماً Server-Side باشد.

400 object را بدون نیاز prefetch سنگین نکن.

---

# 33. Admin Actions

Actions:

Approve

Reject

Needs Permit

Activate

Deactivate

Delete

Restore

Edit

View

Approve Permit

Reject Permit

در صورت Business Rule مناسب Bulk Actions نیز ایجاد کن.

تمام عملیات حساس باید Permission Check داشته باشند.

---

# 34. Role & Permission

از Permission System خود Django استفاده کن.

حداقل نقش‌ها:

SuperAdmin

Moderator

Support

Accounting

در صورت نیاز با Group پیاده‌سازی کن.

مثلاً Moderator نباید به تنظیمات پرداخت دسترسی کامل داشته باشد.

---

# 35. Tariff

مدل:

Tariff

code

title

description

price

service_type

duration_days

is_active

sort_order

settings

created_at

updated_at

سرویس‌های اولیه:

Annual Ad

Annual Renewal

Featured

Colored Card

Urgent

Extra Link

Extra Image

Ladder

Automatic Ladder

و سرویس‌های قابل توسعه آینده.

---

# 36. Ad Services

خدمات خریداری‌شده آگهی را در مدل جدا ذخیره کن.

AdService

ad

tariff

starts_at

expires_at

status

metadata

created_at

مثلاً Featured نباید فقط یک Boolean بدون History باشد.

در صورت نیاز Booleanهای cached روی Ad برای Query سریع قابل نگهداری هستند، اما Source of Truth سرویس خریداری‌شده باشد.

---

# 37. Ladder

برای نردبان هرگز created_at را تغییر نده.

از:

sort_at

و:

last_ladder_at

استفاده کن.

Default ordering مناسب:

is_featured DESC

sort_at DESC

published_at DESC

Auto Ladder توسط Management Command انجام شود.

Cron قابل استقرار روی cPanel فراهم کن.

مثلاً ساعت 09:00.

---

# 38. Billing

مدل‌های:

Order

OrderItem

Invoice

InvoiceItem

Payment

را در صورت نیاز ایجاد کن.

Payment حداقل:

user

ad

invoice

amount

method

status

gateway

authority

reference_id

paid_at

verified_at

admin_note

created_at

Method:

FREE

ONLINE

CARD_TO_CARD

Status:

PENDING

SUCCESSFUL

FAILED

CANCELLED

MANUAL_REVIEW

---

# 39. Payment Gateway

Payment Gateway را Hardcode نکن.

Interface/Adapter ایجاد کن.

مثلاً:

PaymentGateway

create_payment()

verify_payment()

Development Gateway یا Fake Gateway ایجاد کن تا سیستم بدون Credential قابل تست باشد.

بعداً بتوان درگاه ایرانی موردنظر را با Adapter اضافه کرد.

هیچ Secret در Repository قرار نگیرد.

Callback باید:

idempotent

و امن باشد.

Payment دوبار Verify نشود.

---

# 40. Invoice

Invoice دارای:

invoice_number

user

ad

status

subtotal

discount

total

created_at

paid_at

باشد.

InvoiceItem:

invoice

title

quantity

unit_price

total_price

metadata

تعداد ردیف Unlimited باشد.

Invoice Number Unique باشد.

---

# 41. Ticket System

مدل:

Ticket

user

subject

status

priority

created_at

updated_at

closed_at

TicketMessage

ticket

sender

message

created_at

کاربر فقط Ticket خودش را ببیند.

Admin/Support براساس Permission پاسخ دهد.

Status:

OPEN

WAITING_USER

WAITING_SUPPORT

CLOSED

---

# 42. Report Abuse

صفحه عمومی Ad امکان Report داشته باشد.

مدل:

AdReport

ad

reason

description

reporter_user

reporter_ip

status

admin_note

created_at

reviewed_at

Reasons اولیه:

عدم پاسخگویی

کلاهبرداری

خلاف واقع

محتوای غیرمجاز

محتوای نامناسب

گران‌فروشی

سایر

Status:

NEW

REVIEWING

RESOLVED

REJECTED

Rate Limit برای Report پیاده‌سازی کن.

---

# 43. SMS

SMS Provider را Adapter-based طراحی کن.

SMSBackend:

send()

Development backend فقط Log کند.

Production backend بعداً Credential دریافت کند.

مدل:

SMSLog

user

ad

mobile

type

provider_id

status

sent_at

response

برای پیام تمدید، یک SMS مشابه نباید چند بار ناخواسته ارسال شود.

Idempotency ایجاد کن.

---

# 44. Expired SMS Admin

Admin صفحه‌ای داشته باشد که بتواند کاربران دارای آگهی منقضی را مشاهده یا انتخاب کند.

مطابق نمونه امکان انتخاب حداکثر N کاربر وجود داشته باشد.

Default می‌تواند 50 باشد.

ارسال‌های قبلی بررسی شوند.

Management Command و Manual Action هر دو قابل استفاده باشند.

---

# 45. Site Settings

مدل Singleton یا key/value کنترل‌شده ایجاد کن.

موارد:

site_name

site_description

default_meta_description

logo

contact_phone

support_email

free_ad_duration

max_images

max_links

SEO thresholds

SMS settings غیرحساس

maintenance settings

Header/Footer متغیر در صورت نیاز.

Secretها در Database عمومی ذخیره نشوند مگر Encrypt شده باشند.

---

# 46. Homepage

صفحه اصلی باید Server-Side Rendered باشد.

حداقل:

Header

Logo

Location Selector

Search

Category selector

Price range

Main categories

Featured ads

Latest ads

Footer

را نمایش دهد.

Card آگهی:

تصویر thumbnail

عنوان

قیمت یا «توافقی»

شهر

تاریخ

Badge فوری

Badge ویژه

باشد.

Card بسیار سبک باشد.

---

# 47. Public Ad Detail

صفحه آگهی شامل:

Breadcrumb

تصاویر

عنوان

دسته‌بندی

قیمت

شهر

تاریخ

کد آگهی

بازدید

توضیحات

کلمات کلیدی/Tags در صورت نیاز

اطلاعات تماس

نام

واحد تجاری

کشور

استان

شهر

آدرس

لینک‌ها

گزارش تخلف

آگهی‌های مرتبط

باشد.

فیلدی که مقدار ندارد Render نشود.

---

# 48. Related Ads

Related Ads ابتدا با Query ساده و سریع:

same category

same city

exclude self

ACTIVE only

order by relevance/freshness

پیاده‌سازی شود.

Query باید محدود باشد.

مثلاً 6 یا 8 مورد.

در فاز اول Recommendation Engine نساز.

---

# 49. Search

Search داخلی ابتدا PostgreSQL-based باشد.

Elasticsearch نصب نکن.

Search حداقل روی:

title

description

business_name

category

city

انجام شود.

Persian normalization را اعمال کن.

اگر PostgreSQL Hosting اجازه دهد:

CREATE EXTENSION pg_trgm

فعال کن.

ولی پروژه نباید بدون pg_trgm از کار بیفتد.

Feature Detection / graceful fallback داشته باشد.

در صورت وجود pg_trgm:

TrigramSimilarity

GIN/GiST index مناسب

برای Search استفاده کن.

بدون آن Search ساده‌تر PostgreSQL اجرا شود.

---

# 50. Search UX

Search result باید:

Pagination

Category Filter

Location Filter

Price Filter

Sort

داشته باشد.

اما تمام Filter Combinationها نباید SEO Indexable شوند.

---

# 51. SEO Architecture

SEO یک Feature بعدی نیست.

از ابتدای پروژه جزو Architecture است.

تمام صفحات Public مهم باید SSR باشند.

محتوای اصلی نباید پس از API Request سمت Browser ظاهر شود.

Googlebot باید HTML اصلی را در Response اولیه دریافت کند.

---

# 52. SEO URL

URLها Human-readable باشند.

نمونه:

/

/tehran/

/tehran/industrial/

/tehran/industrial/cnc/

/ad/481432/frosh-dastgah-cnc/

Slug فارسی یا transliterated هر دو قابل قبول هستند، اما Policy ثابت و consistent باشد.

Ad ID/Code در URL باقی بماند تا تغییر Title URL identity را نشکند.

---

# 53. Canonical

تمام صفحات Indexable Canonical داشته باشند.

Ad detail:

canonical → خودش.

Category:

canonical → خودش.

Pagination:

هر صفحه Canonical خودش را داشته باشد.

Page 2 را به Page 1 canonical نکن.

Search/filter variants:

Canonical و noindex Policy درست اعمال شود.

از Canonicalهای متناقض خودداری کن.

---

# 54. Indexability Policy

یک Service/Utility مرکزی ایجاد کن.

مثلاً:

seo/policies.py

توابع:

is_indexable_ad()

is_indexable_category()

is_indexable_location()

get_robots_directive()

get_canonical_url()

Index:

Homepage

Active Ad

Valuable Categories

Valuable Location Landing Pages

Category + Location صفحات ارزشمند

Noindex:

Search

arbitrary filters

login

register

user dashboard

admin

tickets

payment pages

draft ads

pending ads

deleted ads

expired ads به‌صورت Default

Empty landing pages

Thin pages

---

# 55. Faceted Navigation

هر Query Parameter را Indexable نکن.

موارد:

sort

min_price

max_price

urgent

page_size

view

و Filterهای arbitrary:

noindex

باشند.

SEO Landing Page فقط وقتی ایجاد شود که Business/SEO Policy آن را صراحتاً مجاز کرده باشد.

از تولید میلیون‌ها URL کم‌ارزش جلوگیری کن.

---

# 56. Pagination SEO

Pagination واقعی Server Side داشته باش.

?page=2

?page=3

...

هر Page URL مستقل باشد.

لینک Next/Previous با:

<a href="...">

واقعی تولید شود.

Infinite Scroll اگر بعداً اضافه شد، Pagination crawlable همچنان باقی بماند.

---

# 57. Internal Linking

تمام لینک‌های اصلی باید HTML anchor واقعی باشند.

مثلاً:

Home
→ Category
→ Subcategory
→ City
→ Ad

صفحه Ad:

→ Category

→ Parent Category

→ City

→ Related Ads

→ Seller/Business در صورت وجود

از onclick-only navigation برای لینک‌های SEO استفاده نکن.

---

# 58. Breadcrumb

برای Category و Ad ایجاد کن.

مثلاً:

خانه

> صنعت

> ماشین‌آلات

> CNC

> آگهی

HTML Breadcrumb و JSON-LD BreadcrumbList هر دو ایجاد شوند.

---

# 59. Sitemap

Sitemap Index ایجاد کن.

مثلاً:

/sitemap.xml

و:

/sitemaps/ads-1.xml

/sitemaps/categories.xml

/sitemaps/locations.xml

فقط URLهایی وارد Sitemap شوند که:

indexable

canonical

HTTP 200

هستند.

Pending، Deleted، Search و Filter داخل Sitemap نروند.

Sitemap generation نباید برای سایت بزرگ در هر Request تمام Database را Scan کند.

در صورت نیاز Cache یا Scheduled generation استفاده کن.

---

# 60. robots.txt

robots.txt صحیح ایجاد کن.

Admin و private paths را Disallow کن.

اما صفحاتی که باید Google `noindex` آنها را ببیند صرفاً به‌خاطر noindex در robots.txt Block نکن.

Robots و meta robots را با هم اشتباه استفاده نکن.

---

# 61. Page Title

تمام صفحات Indexable Title Unique داشته باشند.

Ad:

{ad.title} در {city} | {site_name}

Category:

آگهی {category} در {city} | {site_name}

از keyword stuffing خودداری کن.

---

# 62. Meta Description

هر Ad Description خودکار ولی مناسب داشته باشد.

از اولین بخش meaningful توضیح آگهی استفاده کن.

Category/Location pages Description اختصاصی داشته باشند.

Meta Description Duplicate در مقیاس بالا تولید نکن.

---

# 63. H Tags

هر صفحه دقیقاً یک Main H1 منطقی داشته باشد.

Structure:

H1

H2

H3

از Heading فقط برای Styling استفاده نکن.

---

# 64. Structured Data

JSON-LD تولید کن.

Site:

Organization

Public pages:

BreadcrumbList

Adهای واقعی فقط در صورتی Product/Offer دریافت کنند که واقعاً داده و نوع محتوا با آن Schema سازگار باشد.

Employment:

JobPosting فقط برای آگهی شغلی معتبر.

Markup جعلی برای گرفتن Rich Result نساز.

Structured Data باید با محتوای قابل مشاهده صفحه یکسان باشد.

---

# 65. Open Graph

برای Ad:

og:title

og:description

og:url

og:image

og:type

اضافه کن.

Twitter Card metadata مناسب نیز اضافه شود.

---

# 66. 404 / 410

Unknown object:

404

Permanently deleted public listing در صورت Policy مناسب:

410 Gone

Soft-deleted objects نباید در Public Site نمایش داده شوند.

Redirect Loop ایجاد نکن.

---

# 67. Redirects

اگر Slug آگهی تغییر کرد ولی ID صحیح است، URL Canonical را پیدا کن و با 301 Redirect کن.

مثلاً:

/ad/481432/old-title/

→

301

/ad/481432/new-title/

---

# 68. Core Web Vitals Targets

Target رسمی داخلی پروژه:

LCP <= 2.0s

INP <= 150ms

CLS <= 0.05

این Targetها از آستانه Good عمومی سخت‌گیرانه‌تر هستند.

Performance باید با Mobile network و device متوسط بررسی شود.

---

# 69. Performance Principles

Public pages:

حداقل JavaScript.

هیچ SPA Runtime.

هیچ React Hydration.

هیچ dependency سنگین غیرضروری.

CSS کوچک.

تصاویر کوچک.

HTML Server Rendered.

Database Query بهینه.

Static Browser Cache.

Compression.

Lazy loading مناسب.

---

# 70. Query Budget

در Development Query count صفحات کلیدی را بررسی کن.

هدف تقریبی:

Homepage:

حدود 15 Query یا کمتر.

Listing:

حدود 10 Query یا کمتر در حالت عادی.

Ad Detail:

حدود 10 Query یا کمتر.

این Limit مطلق نیست ولی N+1 نباید وجود داشته باشد.

برای Relationها:

select_related()

prefetch_related()

استفاده کن.

---

# 71. Database Optimization

برای Queryهای بزرگ:

values()

values_list()

only()

defer()

وجود Index

Pagination

را در صورت مناسب استفاده کن.

بی‌دلیل تمام Relationها را Prefetch نکن.

از Query inside loop جلوگیری کن.

---

# 72. Cache

Redis در فاز اول اجباری نیست.

Cache abstraction خود Django استفاده شود.

روی Shared Hosting ابتدا:

FileBasedCache

یا DatabaseCache

قابل استفاده باشد.

Local-memory Cache فقط جایی استفاده شود که Multi-process inconsistency مشکل ایجاد نمی‌کند.

موارد مناسب Cache:

Homepage sections

Category tree

Country/province/city lists

Site settings

Category counts

Popular ads در صورت وجود

Cache invalidation معقول طراحی کن.

---

# 73. HTTP Cache

برای Static files:

Long Cache-Control.

File fingerprinting فعال باشد.

از ManifestStaticFilesStorage یا Storage معادل مناسب استفاده کن.

HTML dynamic private pages Cache عمومی نشوند.

---

# 74. Static Files

Production:

DEBUG=False

collectstatic اجرا شود.

Static توسط Web Server سرو شود.

Django View نباید static را در Production Serve کند.

اگر hosting routing محدود بود WhiteNoise فقط به‌عنوان fallback قابل استفاده است.

---

# 75. Media

Media توسط Web Server ارائه شود.

Accessهای Private مانند Permit documents در صورت حساس بودن مستقیم Public URL نداشته باشند.

برای Permit در صورت نیاز authenticated view یا protected location استفاده کن.

تصاویر عمومی آگهی Public باشند.

---

# 76. CSS

UI مدرن ولی سبک باشد.

از Node.js به‌عنوان Requirement Production اجتناب کن.

می‌توانی:

Custom CSS

یا build-time CSS

استفاده کنی.

اما خروجی Production باید CSS Static ساده باشد.

RTL کامل رعایت شود.

Mobile First طراحی کن.

---

# 77. JavaScript

هدف:

کمترین JavaScript ممکن.

از jQuery استفاده نکن.

از framework بزرگ استفاده نکن.

برای:

dependent province/city

image preview

modal

favorite در صورت اضافه شدن

form interactions

Vanilla JS یا HTMX کافی است.

Libraries را فقط در صفحاتی Load کن که استفاده می‌شوند.

---

# 78. Fonts

فونت فارسی را Local Host کن.

WOFF2 استفاده کن.

Font فایل‌های غیرضروری و weightهای متعدد اضافه نکن.

font-display مناسب تنظیم کن.

از Google Fonts خارجی در Critical Path استفاده نکن.

---

# 79. Icons

از SVG Icons استفاده کن.

Icon Font بزرگ مانند Font Awesome کامل را لود نکن مگر ضرورت واقعی وجود داشته باشد.

---

# 80. Security

Production حداقل:

DEBUG=False

SECRET_KEY secure

ALLOWED_HOSTS

HTTPS

SESSION_COOKIE_SECURE

CSRF_COOKIE_SECURE

SECURE_SSL_REDIRECT در صورت سازگاری hosting

CSRF Protection

XSS-safe templates

Upload Validation

Permission Control

Rate Limiting

Secure Password Hashing

در نظر گرفته شود.

هرگز user-generated HTML را mark_safe نکن.

---

# 81. Upload Security

Uploadها را فقط بر اساس Extension قبول نکن.

واقعی بودن Image decode شود.

حداکثر File Size تعریف کن.

حداکثر Pixel Count تعریف کن.

Filename کاربر مستقیماً به filesystem path تبدیل نشود.

Filename امن و random تولید کن.

EXIF metadata غیرضروری حذف شود.

---

# 82. Rate Limiting

حداقل برای:

Login

Register

Password reset

Report abuse

Ticket creation

Contact reveal در صورت وجود

Rate Limit طراحی کن.

Implementation باید با Shared Hosting سازگار باشد.

---

# 83. Logging

Logging production مناسب ایجاد کن.

Logهای:

application errors

payment events

admin status changes

SMS errors

security-relevant events

را ثبت کن.

Password، Secret یا payment credential را Log نکن.

---

# 84. Error Pages

Template فارسی و سبک برای:

400

403

404

500

ایجاد کن.

500 نباید Stack Trace نشان دهد.

---

# 85. Audit

عملیات حساس Admin Audit شوند.

حداقل:

Ad status changes

Payment manual edits

Tariff changes

Permit approval

Restore/delete

را بتوان Track کرد.

---

# 86. Cron / Management Commands

Management Commandهای لازم ایجاد کن.

مثلاً:

expire_ads

process_auto_ladders

send_expiry_notifications

cleanup_sessions

cleanup_temp_files

rebuild_search_data در صورت نیاز

generate_sitemaps در صورت نیاز

هر Command:

idempotent

safe

loggable

باشد.

---

# 87. Shared Hosting Constraint

تمام Architecture را با این فرض طراحی کن:

سرور Dedicated نداریم.

Docker در Production نداریم.

Redis ممکن است وجود نداشته باشد.

Root access نداریم.

System service جدید نمی‌توان نصب کرد.

تنها امکانات احتمالی:

Python Application

virtualenv

pip

Passenger/WSGI

PostgreSQL

Cron

Filesystem

SSH

هستند.

پس پروژه نباید برای اجرای پایه به Daemon اضافی نیاز داشته باشد.

---

# 88. Deployment Documentation

یک فایل کامل:

DEPLOYMENT.md

بساز.

شامل:

ایجاد Python App در cPanel

ساخت Virtualenv

Install dependencies

ساخت PostgreSQL Database

ایجاد Database User

Environment Variables

Migration

collectstatic

Create Superuser

Passenger configuration

Media/static paths

Cron commands

Restart application

Backup

Troubleshooting

باشد.

---

# 89. Requirements

یک dependency list کوچک و کنترل‌شده نگه دار.

هر Package باید دلیل مشخص داشته باشد.

Core احتمالی:

Django 5.2.x

psycopg

Pillow

در صورت نیاز:

django-environ

whitenoise

HTMX static asset

از dependencyهای بدون ضرورت خودداری کن.

نسخه‌ها را Lock/Pin منطقی کن.

---

# 90. README

README.md کامل بساز.

شامل:

Project overview

Architecture

Requirements

Development setup

Database setup

Migration

Fixtures

Test

Deployment

Management commands

Environment variables

Project structure

باشد.

---

# 91. Seed Data

برای Development Fixtures یا Management Commands ایجاد کن.

نمونه:

seed_demo

seed_categories

import_locations

برای کشور/استان/شهر امکان Import از CSV یا JSON فراهم کن.

Dataset واقعی شهرها را داخل Business Logic Hardcode نکن.

---

# 92. Admin Settings Import

برای دسته‌بندی‌ها و Locationها Import/Export CSV در صورت منطقی بودن فراهم کن.

این قابلیت باید امن و فقط در اختیار Administrator باشد.

---

# 93. Tests

Project بدون Test تحویل نده.

حداقل Testها:

Model tests

Form validation tests

Permission tests

Ad workflow tests

Expiration tests

Permit workflow tests

Forbidden word tests

Duplicate detection tests

Payment tests

Ticket ownership tests

Report tests

SEO tests

URL/canonical tests

Sitemap tests

Image processing tests

Upload security tests

---

# 94. Workflow Tests

حداقل سناریوی زیر تست شود:

User registers

→ creates ad

→ PENDING_APPROVAL

→ admin approves

→ ACTIVE

→ user edits

→ PENDING_APPROVAL

→ admin requests permit

→ NEEDS_PERMIT

→ user uploads permit

→ admin approves

→ ACTIVE

→ expires

→ EXPIRED

→ user purchases renewal

→ ACTIVE

---

# 95. SEO Tests

Test کن:

Active ad = index

Pending ad = noindex/not public

Deleted ad = not indexable

Search = noindex

Filter = noindex

Canonical درست

Pagination canonical درست

Sitemap فقط indexable URLs

Breadcrumb JSON-LD valid structure

Slug redirect 301

---

# 96. Image Tests

Test کن:

JPEG upload

PNG upload

WebP upload

invalid file

huge image

rotated EXIF image

original not retained

thumb generated

display generated

correct dimensions

reasonable output size

---

# 97. Performance Tests

حداقل در Development/Staging:

Django Debug Toolbar فقط Development.

N+1 Query بررسی شود.

Lighthouse/PageSpeed روی:

Homepage

Category

Search

Ad Detail

اجرا شود.

Third-party JS غیرضروری حذف شود.

---

# 98. Accessibility

حداقل:

label برای Formها

keyboard navigation

visible focus

alt تصاویر

button semantics

link semantics

color contrast مناسب

رعایت شود.

---

# 99. UI Design

ظاهر جدید باید:

حرفه‌ای

ساده

مدرن

فارسی

RTL

Mobile-first

باشد.

Reference pages صرفاً Requirement هستند.

ظاهر قدیمی آنها Clone نشود.

تمرکز UI:

خوانایی

اعتماد

سرعت

سادگی ثبت آگهی

سادگی Search

---

# 100. Search Page SEO

Search Result عمومی:

noindex, follow

باشد.

Query کاربر مستقیماً داخل meta title بدون escape قرار نگیرد.

Search term باید Escape شود.

---

# 101. Category Landing Page

Category page حداقل:

H1

Intro text

Subcategories

Ads

Pagination

Related locations

Breadcrumb

SEO metadata

داشته باشد.

صفحه خالی یا Thin نباید Indexable باشد.

---

# 102. Location Landing Page

مثلاً:

/tehran/

/tehran/industrial/

دارای:

Unique title

Description

Relevant ads

Categories

Breadcrumb

Internal links

باشد.

تمام Category × City combinations را خودکار Index نکن.

Indexability باید Policy-based باشد.

---

# 103. SEO Content Quality

برای گرفتن SEO هرگز متن بی‌ارزش خودکار یا keyword stuffing تولید نکن.

اگر SEO description اختصاصی وجود ندارد، Template کوتاه و طبیعی ایجاد کن.

صفحات بدون ارزش واقعی noindex باشند.

---

# 104. Seller Information

اگر business_name وجود دارد آن را نمایش بده.

در صورت توسعه آینده ساخت Business Profile ممکن باشد.

فعلاً Business Marketplace پیچیده نساز مگر Requirement موجود باشد.

---

# 105. Admin Search

Search پنل Admin باید efficient باشد.

از icontains روی 20 ستون بزرگ بدون Index و بدون محدودیت خودداری کن.

Search strategy مناسب طراحی کن.

کد آگهی و موبایل Exact/Prefix Search شوند.

Title از Search text استفاده کند.

---

# 106. Database Constraints

علاوه بر Form Validation، Constraintهای مهم Database ایجاد کن.

مثلاً:

positive prices

valid uniqueness

sort order constraints در صورت نیاز

Unique public code

Unique slugs در scope مناسب

Data integrity را فقط به UI واگذار نکن.

---

# 107. Transactions

عملیات مالی و Workflowهای چندمرحله‌ای مهم داخل:

transaction.atomic()

انجام شوند.

مثلاً:

successful payment

* service activation

* invoice update

باید Atomic باشد.

---

# 108. Concurrency

برای عملیات حساس مثل:

Payment verification

Ladder purchase

Renewal

از race condition جلوگیری کن.

در صورت نیاز:

select_for_update()

استفاده کن.

---

# 109. Time handling

از timezone.now()

استفاده کن.

از datetime.now() naive استفاده نکن.

همه Expirationها timezone-aware باشند.

---

# 110. Search Extension

اگر pg_trgm قابل فعال‌سازی بود:

Migration مناسب با TrigramExtension ایجاد کن.

اما Migration نباید روی Host بدون permission کل Deployment را نابود کند.

قبل از استفاده Production قابلیت هاست بررسی و fallback مستند شود.

Search پایه بدون Extension باید کار کند.

---

# 111. Database Connection

CONN_MAX_AGE را Configurable قرار بده.

مقدار مناسب با limit هاست تنظیم شود.

Connection pool خارجی اجباری نکن.

---

# 112. Production Check

قبل از تحویل:

python manage.py check --deploy

اجرا کن.

Warningهای قابل حل را برطرف کن.

---

# 113. Backup

در DEPLOYMENT.md روش Backup را توضیح بده.

Database:

pg_dump

Media:

backup media directory

مستند کن.

Restore procedure نیز بنویس.

---

# 114. Git

Repository تمیز باشد.

.gitignore مناسب برای:

.env

venv

**pycache**

media runtime

static collected files در صورت سیاست پروژه

IDE files

ایجاد کن.

Commitها در صورت کنترل agent منطقی و مرحله‌ای باشند.

---

# 115. Code Quality

کد باید:

Readable

Pythonic

DRY ولی نه over-abstracted

PEP8

دارای naming واضح

باشد.

برای business functionهای مهم type hint بنویس.

Functionهای بسیار بزرگ را تقسیم کن.

Viewهای 500 خطی نساز.

---

# 116. No Overengineering

از این موارد تا زمانی که واقعاً لازم نیست استفاده نکن:

Microservices

CQRS

Kafka

Event Sourcing

RabbitMQ

Celery

Redis mandatory

GraphQL

Elasticsearch

Docker production dependency

Kubernetes

React

API Gateway

هدف:

یک Django Monolith تمیز و سریع.

---

# 117. Future Compatibility

طراحی باید اجازه دهد بعداً بدون Rewrite کامل اضافه کنیم:

Redis/Valkey

Object Storage

CDN

Meilisearch/Elasticsearch

Celery Worker

Mobile API

Business Profiles

Favorites

Chat

Recommendation

اما هیچ‌کدام prerequisite نسخه اول نباشند.

---

# 118. خروجی‌های الزامی

در پایان باید این موارد وجود داشته باشد:

Working Django project

PostgreSQL migrations

Custom User

Public website

User panel

Admin management panel

Ad workflow

Category management

Location management

Forbidden words

Permit system

Payment architecture

Invoice system

Tariffs

Tickets

Reports

SMS abstraction

Cron commands

Image optimization

SEO architecture

Sitemap

robots.txt

Structured Data

Tests

README

DEPLOYMENT.md

.env.example

requirements

Seed/demo data

Production settings

---

# 119. مراحل اجرای پروژه

کار را مرحله‌ای انجام بده.

Phase 1:
Project bootstrap + config + PostgreSQL + Custom User.

Phase 2:
Category + Location models.

Phase 3:
Ad models + workflow + image pipeline.

Phase 4:
Public registration/authentication + ad submission.

Phase 5:
User dashboard.

Phase 6:
Admin dashboard + moderation.

Phase 7:
Tariffs + payments + invoices.

Phase 8:
Tickets + reports + forbidden words + permits.

Phase 9:
SMS + cron + expiration + ladder.

Phase 10:
Public homepage + category + search + ad detail.

Phase 11:
SEO implementation.

Phase 12:
Performance optimization.

Phase 13:
Security hardening.

Phase 14:
Tests.

Phase 15:
Production deployment documentation.

بعد از هر Phase:

migrations را اجرا کن.

tests را اجرا کن.

system check اجرا کن.

خطاها را قبل از رفتن به مرحله بعد برطرف کن.

---

# 120. نحوه کار Agent

قبل از کدنویسی:

Repository موجود را بررسی کن.

اگر پروژه‌ای وجود ندارد ساختار مناسب را ایجاد کن.

یک فایل:

IMPLEMENTATION_PLAN.md

بساز و Taskها را ثبت کن.

سپس شروع به پیاده‌سازی کن.

صرفاً Plan تحویل نده.

بعد از Plan فوراً وارد Implementation شو.

در حین اجرا:

Taskهای انجام‌شده را Mark کن.

اگر Bug پیدا شد همان موقع Fix کن.

اگر Test شکست خورد رها نکن.

اگر Migration مشکل داشت حل کن.

TODOهای حیاتی باقی نگذار.

Placeholderهای غیرضروری باقی نگذار.

---

# 121. عدم توقف غیرضروری

برای تصمیم‌های کوچک از کاربر سؤال نپرس.

از Requirementهای این سند استفاده کن.

اگر جزئیات جزئی مشخص نیست، یک انتخاب استاندارد، ساده و قابل تغییر انجام بده.

فقط زمانی سؤال کن که بدون اطلاعات کاربر انجام صحیح کار واقعاً ممکن نیست، مثلاً:

Payment Gateway credentials

SMS Provider credentials

Production domain

Hosting credentials

Brand/logo نهایی

تا قبل از دریافت آنها Fake/Development Adapter ایجاد کن تا پروژه قابل تست بماند.

---

# 122. Definition of Done

پروژه فقط زمانی Done محسوب می‌شود که:

یک کاربر بتواند ثبت‌نام کند.

آگهی ثبت کند.

تصویر Upload کند.

Original تصویر ذخیره نشود.

آگهی برای تأیید برود.

ادمین آن را تأیید کند.

آگهی Public شود.

SEO Metadata صحیح داشته باشد.

در Sitemap قرار بگیرد.

Search آن را پیدا کند.

کاربر بتواند آن را مدیریت کند.

آگهی Expire شود.

Renewal انجام شود.

Payment architecture کار کند.

Ticket کار کند.

Report abuse کار کند.

Permit workflow کار کند.

Admin workflows کار کنند.

Database PostgreSQL باشد.

Tests Pass شوند.

Production settings امن باشند.

collectstatic کار کند.

Deployment روی Python Hosting مستند و عملی باشد.

---

# 123. Performance Definition of Done

هیچ N+1 شناخته‌شده روی صفحات اصلی وجود نداشته باشد.

تصاویر Original ذخیره نشده باشند.

صفحه Home، Listing و Ad Detail بدون JavaScript هم محتوای اصلی را نمایش دهند.

HTML اولیه شامل محتوای SEO باشد.

CSS/JS غیرضروری حذف شده باشد.

Core Web Vitals برای معماری بهینه شده باشد.

صفحات عمومی Mobile-friendly باشند.

---

# 124. SEO Definition of Done

هر Active Ad:

Unique URL

Unique title

Meta description

Canonical

H1

Breadcrumb

Open Graph

Index policy

داشته باشد.

Category:

SEO URL

Pagination

Internal links

Canonical

Metadata

داشته باشد.

Search:

noindex

باشد.

Arbitrary filters:

noindex

باشند.

Sitemap فقط صفحات معتبر را داشته باشد.

Deleted/Pending content وارد Google Index نشود.

---

# 125. اولویت‌های تصمیم‌گیری

اگر بین دو راهکار مردد بودی، به ترتیب این اولویت‌ها را رعایت کن:

1. Correctness
2. Security
3. SEO
4. Page Speed
5. Simplicity
6. Shared Hosting Compatibility
7. Maintainability
8. Developer Convenience

هیچ قابلیت نمایشی نباید باعث خراب شدن SEO، Security یا Performance شود.

---

# 126. اصل نهایی

این پروژه نباید شبیه یک WordPress پر از Plugin یا یک SPA سنگین باشد.

هدف یک معماری ساده است:

Browser
↓
Django
↓
PostgreSQL
↓
Django Template
↓
Lightweight HTML

در Production:

Internet
↓
Web Server
↓
Passenger / WSGI
↓
Django
↓
PostgreSQL

و Static/Media تا حد ممکن مستقیماً توسط Web Server ارائه شوند.

سایت باید از لحاظ فنی توان رقابت با سایت‌های قدیمی آگهی را از طریق این موارد داشته باشد:

Server-Side Rendering واقعی

HTML سبک

تصاویر بسیار سبک

Database Query سریع

SEO Architecture اصولی

Internal Linking خوب

URL Structure صحیح

Indexation Control

Structured Data صحیح

Mobile-first UX

Minimal JavaScript

Core Web Vitals خوب

و معماری ساده و قابل نگهداری.

اکنون ابتدا Repository را بررسی کن، IMPLEMENTATION_PLAN.md را ایجاد کن و بلافاصله پیاده‌سازی Phase 1 را شروع کن. صرفاً توضیح نده که چه کاری باید انجام شود؛ پروژه را واقعاً بساز، Test کن و تا رسیدن به Definition of Done پیش برو.
