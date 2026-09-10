#!/usr/bin/env bash
# ──────────────────────────────────────────
#  سامانه آگهی —  اسکریپت راه‌اندازی
#  usage:  ./start.sh              (پورت 9000)
#          ./start.sh --port=8080  (پورت دلخواه)
# ──────────────────────────────────────────
set -euo pipefail

PORT="${1:-9000}"
PORT="${PORT#--port=}"

cd "$(dirname "$0")"

# ── PHP ─────────────────────────────────────
PHP_BIN="php8.3"
if ! command -v "$PHP_BIN" &>/dev/null; then
  PHP_BIN="php"
fi

echo "»  بررسی PHP …"
$PHP_BIN -v | head -1

# ── Composer ────────────────────────────────
if [ ! -f vendor/autoload.php ]; then
  echo "»  composer install …"
  composer install --prefer-dist --no-interaction
fi

# ── Frontend build ──────────────────────────
echo "»  npm install && npm run build …"
npm install --ignore-scripts --silent
npm run build --silent

# ── Migrations ──────────────────────────────
echo "»  بررسی migration …"
$PHP_BIN artisan migrate --force

# ── Clear & optimise ────────────────────────
echo "»  پاک‌سازی cache …"
$PHP_BIN artisan config:clear
$PHP_BIN artisan view:clear
$PHP_BIN artisan route:clear

# ── Kill old PHP servers on this port ───────
echo "»  متوقف کردن سرورهای قبلی …"
pkill -f "php.*-S.*0\.0\.0\.0:$PORT" 2>/dev/null || true
sleep 1

# ── Start server (detached) ─────────────────
echo "──────────────────────────────────────────"
echo "  سامانه آگهی روی پورت $PORT آماده است:"
echo "  →  http://localhost:$PORT"
echo "──────────────────────────────────────────"
setsid nohup bash -c "PHP_CLI_SERVER_WORKERS=8 exec $PHP_BIN -S 0.0.0.0:$PORT -t public server.php" > /tmp/php_server.log 2>&1 &
disown
sleep 2

# Verify server is running
if curl -s -o /dev/null -w '' --max-time 3 "http://127.0.0.1:$PORT" 2>/dev/null; then
  echo "  ✔  سرور با موفقیت بالا آمد."
else
  echo "  ✖  سرور بالا نیامد. لاگ: /tmp/php_server.log"
  exit 1
fi
