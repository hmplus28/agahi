#!/usr/bin/env bash
# ═══════════════════════════════════════════════════
#  سامانه آگهی — اسکریپت استقرار روی cPanel
# ═══════════════════════════════════════════════════
# نحوه استفاده:
#   ۱. پروژه رو در مسیر /home/ACCOUNT/apps/agahi آپلود کن
#   ۲. فایل .env.production.example را کپی کن به .env
#   ۳. اطلاعات دیتابیس و APP_URL را در .env تنظیم کن
#   ۴. این اسکریپت رو اجرا کن:
#        bash deploy.sh
# ═══════════════════════════════════════════════════
set -euo pipefail

cd "$(dirname "$0")"

echo "══════════════════════════════════════════════════"
echo "  استقرار سامانه آگهی روی cPanel"
echo "══════════════════════════════════════════════════"

# ── ۱. بررسی .env ─────────────────────────────────
if [ ! -f .env ]; then
  echo "❌  فایل .env یافت نشد!"
  echo "   ابتدا .env.production.example را به .env کپی و تنظیم کنید:"
  echo "     cp .env.production.example .env"
  echo "     nano .env"
  exit 1
fi

echo "✅  .env موجود است"

# ── ۲. نصب composer (بدون dev) ─────────────────────
echo ""
echo "»  نصب composer dependencies (production) …"
/usr/local/bin/php composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader 2>&1
echo "✅  composer install done"

# ── ۳. تولید APP_KEY ───────────────────────────────
if grep -q 'APP_KEY=$' .env 2>/dev/null; then
  echo ""
  echo "»  تولید APP_KEY …"
  /usr/local/bin/php artisan key:generate --force
  echo "✅  APP_KEY generated"
fi

# ── ۴. لینک storage ────────────────────────────────
echo ""
echo "»  ایجاد symlink storage …"
/usr/local/bin/php artisan storage:link --force 2>/dev/null || true
echo "✅  storage linked"

# ── ۵. migration ───────────────────────────────────
echo ""
echo "»  اجرای migrationها …"
/usr/local/bin/php artisan migrate --force
echo "✅  migrations done"

# ── ۶. پاک‌سازی کش و optimize ──────────────────────
echo ""
echo "»  کش‌سازی و optimize …"
/usr/local/bin/php artisan optimize:clear
/usr/local/bin/php artisan config:cache
/usr/local/bin/php artisan route:cache
/usr/local/bin/php artisan view:cache
echo "✅  cache & optimize done"

# ── ۷. تنظیم مجوزها ────────────────────────────────
echo ""
echo "»  تنظیم مجوزهای فایل …"
chmod -R 775 storage bootstrap/cache
echo "✅  permissions done"

# ── ۸. بررسی نهایی ─────────────────────────────────
echo ""
echo "══════════════════════════════════════════════════"
echo "  ✅  استقرار با موفقیت انجام شد!"
echo ""
echo "  مراحل بعدی:"
echo "  ۱. در cPanel، Document Root دامنه را به مسیر زیر تنظیم کن:"
echo "     /home/ACCOUNT/apps/agahi/public"
echo ""
echo "  ۲. یک Cron Job هر دقیقه تنظیم کن:"
echo "     * * * * * cd /home/ACCOUNT/apps/agahi && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "  ۳. از فعال بودن HTTPS مطمئن شو"
echo "══════════════════════════════════════════════════"