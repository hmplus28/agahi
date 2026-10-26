#!/usr/bin/env bash
# ──────────────────────────────────────────────────────
#  سامانه آگهی — راه‌اندازی خودکار + تونل Cloudflare
#  usage:  ./scripts/tunnel.sh            (پورت 9000)
#          ./scripts/tunnel.sh 8080       (پورت دلخواه)
# ──────────────────────────────────────────────────────
set -euo pipefail

PORT="${1:-9000}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG_DIR="$ROOT/storage/logs"
APP_LOG="$LOG_DIR/app-server.log"
CF_LOG="$LOG_DIR/cloudflared.log"

mkdir -p "$LOG_DIR"
cd "$ROOT"

if ! command -v cloudflared &>/dev/null; then
  echo "✗  cloudflared نصب نیست." >&2
  exit 1
fi

# ── ۱) بالا آوردن اپ روی پورت مقصد ────────────────────
if ! curl -s -o /dev/null --noproxy '*' "http://localhost:$PORT"; then
  echo "»  سرور روی پورت $PORT در حال اجرا نیست — راه‌اندازی …"
  if [ -f start.sh ]; then
    nohup bash start.sh "$PORT" >"$APP_LOG" 2>&1 &
  else
    PHP_BIN="php8.3"; command -v "$PHP_BIN" &>/dev/null || PHP_BIN="php"
    PHP_CLI_SERVER_WORKERS=8 nohup "$PHP_BIN" -S "0.0.0.0:$PORT" -t public server.php >"$APP_LOG" 2>&1 &
  fi
  # منتظر آماده شدن سرور
  for _ in $(seq 1 30); do
    [ "$(curl -s -o /dev/null -w '%{http_code}' --noproxy '*' "http://localhost:$PORT")" != "000" ] && break
    sleep 1
  done
else
  echo "✓  سرور از قبل روی پورت $PORT اجراست."
fi

# ── ۲) بستن تونل قبلی همین پروژه ──────────────────────
pkill -f "cloudflared tunnel.*127.0.0.1:$PORT" 2>/dev/null || true
pkill -f "cloudflared tunnel.*localhost:$PORT" 2>/dev/null || true
sleep 2

# ── ۳) ساخت تونل جدید (http2 چون QUIC/UDP ممکن است فیلتر باشد) ──
rm -f "$CF_LOG"
# حذف متغیرهای پروکسی — پروکسی محلی باعث ناپایداری تونل می‌شود
env -u HTTP_PROXY -u HTTPS_PROXY -u http_proxy -u https_proxy -u ALL_PROXY -u all_proxy \
  setsid cloudflared tunnel --url "http://127.0.0.1:$PORT" --protocol http2 --ha-connections 4 \
  --logfile "$CF_LOG" </dev/null >/dev/null 2>&1 &
echo "»  در حال ساخت تونل Cloudflare …"

URL=""
for _ in $(seq 1 40); do
  URL=$(grep -aoE 'https://[a-z0-9-]+\.trycloudflare\.com' "$CF_LOG" 2>/dev/null | head -1 || true)
  [ -n "$URL" ] && break
  sleep 1
done

if [ -z "$URL" ]; then
  echo "✗  ساخت تونل ناموفق بود. لاگ: $CF_LOG" >&2
  exit 1
fi

HOST="${URL#https://}"

# ── ۴) افزودن لینک به فهرست مجاز (.env) ───────────────
update_env() {  # update_env <key> <value>
  local key="$1" val="$2"
  if grep -qE "^${key}=" .env 2>/dev/null; then
    sed -i "s|^${key}=.*|${key}=${val}|" .env
  else
    printf '%s=%s\n' "$key" "$val" >> .env
  fi
}

if [ -f .env ]; then
  update_env APP_URL "$URL"
  update_env SANCTUM_STATEFUL_DOMAINS "$HOST"
  update_env SESSION_DOMAIN ".trycloudflare.com"
  PHP_BIN="php8.3"; command -v "$PHP_BIN" &>/dev/null || PHP_BIN="php"
  "$PHP_BIN" artisan config:clear -q 2>/dev/null || true
  echo "✓  دامنه به فهرست مجاز (.env) اضافه شد."
fi

# ذخیره آخرین لینک برای مراجعه بعدی
echo "$URL" > "$LOG_DIR/tunnel-url.txt"

echo "──────────────────────────────────────────"
echo "  ✓  سامانه آگهی آماده است:"
echo "     محلی :  http://localhost:$PORT"
echo "     عمومی:  $URL"
echo "──────────────────────────────────────────"
