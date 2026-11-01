#!/bin/sh
# بکاپ روزانه دیتابیس PostgreSQL از طریق pg_dump خود کانتینر db
# (نسخهٔ pg_dump همیشه با سرور هم‌خوان است - PRD §63 «Backup Database»)
#
# نصب روی سرور (crontab -e یا پنل aaPanel):
#   30 2 * * * /www/wwwroot/agahi/scripts/backup-db.sh >> /tmp/agahi-backup.log 2>&1
#
# بازیابی دستی:
#   gunzip لازم نیست؛ فایل فرمت custom است:
#   docker compose exec -T db pg_restore -U agahi -d agahi --clean < FILE.dump

set -eu
cd "$(dirname "$0")/.."

KEEP="${KEEP:-14}"
STAMP="$(date +%Y%m%d-%H%M)"
DIR="storage/backups"
mkdir -p "$DIR"

docker compose exec -T db pg_dump \
    -U "${POSTGRES_USER:-agahi}" \
    --format=custom \
    --no-owner \
    "${POSTGRES_DB:-agahi}" > "${DIR}/agahi-${STAMP}.dump"

echo "بکاپ ساخته شد: ${DIR}/agahi-${STAMP}.dump ($(du -h "${DIR}/agahi-${STAMP}.dump" | cut -f1))"

# حذف بکاپ‌های قدیمی‌تر از KEEP نسخه
ls -1t "${DIR}"/agahi-*.dump 2>/dev/null | tail -n +"$((KEEP + 1))" | while IFS= read -r old; do
    rm -f "$old"
    echo "حذف بکاپ قدیمی: $old"
done
