#!/usr/bin/env bash
#
# Build the two upload packages for cPanel shared hosting:
#   vendor.zip  -> upload to ~/laravel/            (DEPLOYMENT.md section 8)
#   build.zip   -> upload to public_html/ AND ~/laravel/public/  (section 9)
#
# Run this locally after pulling the latest code, then upload the two
# zip files and extract them (delete the previous copy first).

set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> composer install --no-dev"
rm -rf vendor vendor.zip
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> zip vendor"
zip -rq vendor.zip vendor
echo "    vendor.zip ready ($(du -h vendor.zip | cut -f1))"

if command -v npm >/dev/null 2>&1; then
    echo "==> npm ci && npm run build"
    npm ci --no-audit --no-fund
    npm run build

    echo "==> zip public/build"
    rm -f build.zip
    (cd public && zip -rq ../build.zip build)
    echo "    build.zip ready ($(du -h build.zip | cut -f1))"
else
    echo "==> npm not found; skipping build. public/build is committed in the"
    echo "    repo, so package it manually if needed:"
    echo "    (cd public && zip -rq ../build.zip build)"
fi

cat <<'EOF'

Done. Upload instructions (DEPLOYMENT.md):
  1. vendor.zip -> extract in  ~/laravel/
  2. build.zip  -> extract in  ~/public_html/  AND  ~/laravel/public/
  3. delete the zip files afterwards
  4. verify the CSS hash served by the site matches public/build/manifest.json
EOF
