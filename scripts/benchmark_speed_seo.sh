#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-http://127.0.0.1:8000}"
OUT_DIR="${2:-/home/ubuntu/agahi/analysis/speed_seo_raw}"
mkdir -p "$OUT_DIR"

cat > "$OUT_DIR/urls.tsv" <<'URLS'
name	path
home	/
search	/search?q=%D8%AA%D8%B9%D9%85%DB%8C%D8%B1
category	/category/services
ad	/ad/DEMO123456/tamirat-lavazem-khanegi
robots	/robots.txt
sitemap_index	/sitemap.xml
sitemap_categories	/sitemaps/categories.xml
URLS

printf 'name,sample,http_code,size_download_bytes,time_starttransfer_s,time_total_s\n' > "$OUT_DIR/requests.csv"
tail -n +2 "$OUT_DIR/urls.tsv" | while IFS=$'\t' read -r name path; do
  curl -sS -D "$OUT_DIR/${name}.headers" -o "$OUT_DIR/${name}.body" "$BASE_URL$path"
  for sample in $(seq 1 10); do
    row="$(curl -sS -o /dev/null -w "%{http_code},%{size_download},%{time_starttransfer},%{time_total}" "$BASE_URL$path")"
    printf '%s,%s,%s\n' "$name" "$sample" "$row" >> "$OUT_DIR/requests.csv"
  done
done

{
  printf 'asset,path,bytes\n'
  find /home/ubuntu/agahi/public/assets -type f -printf '%f,%p,%s\n' | sort
} > "$OUT_DIR/assets.csv"

{
  printf 'metric,value\n'
  printf 'home_h1_count,%s\n' "$(grep -o '<h1' "$OUT_DIR/home.body" | wc -l)"
  printf 'home_css_links,%s\n' "$(grep -o 'assets/app.css' "$OUT_DIR/home.body" | wc -l)"
  printf 'home_javascript_tags,%s\n' "$(grep -oi '<script' "$OUT_DIR/home.body" | wc -l)"
  printf 'ad_h1_count,%s\n' "$(grep -o '<h1' "$OUT_DIR/ad.body" | wc -l)"
  printf 'ad_canonical_count,%s\n' "$(grep -oi 'rel="canonical"' "$OUT_DIR/ad.body" | wc -l)"
  printf 'ad_jsonld_count,%s\n' "$(grep -o 'application/ld+json' "$OUT_DIR/ad.body" | wc -l)"
  printf 'ad_og_title_count,%s\n' "$(grep -oi 'property="og:title"' "$OUT_DIR/ad.body" | wc -l)"
  printf 'search_noindex_count,%s\n' "$(grep -oi 'noindex' "$OUT_DIR/search.body" | wc -l)"
  printf 'robots_sitemap_count,%s\n' "$(grep -c 'Sitemap:' "$OUT_DIR/robots.body" || true)"
  printf 'sitemap_urlset_count,%s\n' "$(grep -c '<sitemap>' "$OUT_DIR/sitemap_index.body" || true)"
} > "$OUT_DIR/seo_checks.csv"

echo "Benchmark data written to $OUT_DIR"
