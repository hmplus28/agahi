# Audit Report — 2026-09-10

## 1. Performance / Lighthouse

| Page | Performance | Accessibility | SEO |
|------|-------------|---------------|-----|
| home (`/`)            | **96** | **100** | 100 |
| search (`/search?q=…`) | **98** | **100** | 69* |
| category (`/category/services`) | **97** | **100** | 69* |
| ad (`/ad/...`)         | **96** | **100** | 100 |

\* Search and category pages score 69 on SEO because they are served with
`<meta name="robots" content="noindex, follow">`. This is **intentional** and
correct — filtered search pages should not be indexed by Google.

### Core Web Vitals (home page, throttled 4G)

| Metric | Value |
|---|---|
| FCP (First Contentful Paint) | 2.0 s |
| LCP (Largest Contentful Paint) | 2.1 s |
| Speed Index | 2.0 s |
| TBT (Total Blocking Time) | 8 ms |
| CLS (Cumulative Layout Shift) | 0.009 |

All four pages pass Core Web Vitals comfortably under the "Good" thresholds
(LCP < 2.5s, CLS < 0.1, INP < 200ms).

### Raw HTTP timings (10 samples each, no throttle)

| Endpoint | Avg size | Avg TTFB | Avg total | P95 total |
|---|---|---|---|---|
| home            | 41 KB | 98 ms  | 99 ms  | 101 ms |
| search          | 39 KB | 113 ms | 115 ms | 131 ms |
| category        | 36 KB | 99 ms  | 100 ms | 102 ms |
| ad              | 39 KB | 102 ms | 104 ms | 104 ms |
| robots.txt      | 127 B | 77 ms  | 78 ms  | 79 ms  |
| sitemap.xml     | 268 B | 80 ms  | 81 ms  | 82 ms  |
| categories.xml  | 319 B | 85 ms  | 86 ms  | 91 ms  |

All endpoints respond in <120ms on the dev server.

---

## 2. SEO Audit

| Check | Status |
|---|---|
| Document has `<title>` | ✅ |
| Document has `<meta name="description">` | ✅ |
| `rel="canonical"` on ad pages | ✅ |
| Open Graph (`og:title`, `og:description`, `og:image`) | ✅ |
| JSON-LD structured data on ad pages | ✅ (2 nodes: BreadcrumbList + product context) |
| `robots.txt` valid, references sitemap | ✅ |
| Sitemap index valid, lists categories + ad pages | ✅ |
| `hreflang` valid | ✅ |
| Image `alt` attributes present | ✅ |
| Crawlable anchors | ✅ |
| Search/category pages `noindex,follow` | ✅ (intentional) |
| robots.txt cache TTL | ✅ 1 hour (public, max-age=3600) |
| sitemap.xml cache TTL | ✅ 15 minutes (public, max-age=900) |
| sitemap categories cache TTL | ✅ 15 minutes (public, max-age=900) |

---

## 3. Security Audit

### 3.1 Security headers (added via `SecurityHeaders` middleware)

| Header | Value |
|---|---|
| X-Content-Type-Options | nosniff |
| X-Frame-Options | SAMEORIGIN |
| Referrer-Policy | strict-origin-when-cross-origin |
| Permissions-Policy | camera=(), microphone=(), geolocation=(), interest-cohort=() |
| X-XSS-Protection | 1; mode=block |
| Content-Security-Policy | strict default-src + unsafe-inline for scripts/styles (cdn.jsdelivr.net whitelisted) |
| Strict-Transport-Security | only on HTTPS requests (max-age=1y, includeSubDomains, preload) |
| X-Powered-By | **removed** (was leaking PHP 8.4.24) |

### 3.2 Sensitive file exposure

| Path | Result |
|---|---|
| `/.env` | 404 ✅ |
| `/composer.json` | 404 ✅ |
| `/package.json` | 404 ✅ |
| `/artisan` | 404 ✅ |
| `/server.php` | 404 ✅ |
| `/phpunit.xml` | 404 ✅ |
| `/database.sqlite` | 404 ✅ |

### 3.3 CSRF protection

- `POST /login` without CSRF token → 419 ✅
- `POST /user/tickets` without CSRF → 419 ✅
- `POST /user/payments/callback/*` is **whitelisted** (Sadad PSP POSTs
  without CSRF), but is protected by HMAC signature validation
  (`PaymentService::computeSignature` + `hash_equals`).

### 3.4 Authentication & authorization

- All `/admin/*` routes redirect to `/login` when not authenticated → 302 ✅
- All `/user/*` routes require auth, redirect to `/login` when not → 302 ✅
- IDOR test: `GET /user/ads/1/edit` without auth → 302 to login ✅
- The `staff` middleware enforces `is_staff=true` for admin routes ✅

### 3.5 SQL injection

- `/search?q=test' OR 1=1--` → HTTP 200 (no error leak, no data dump)
- All queries use Eloquent + parameterized prepared statements.

### 3.6 XSS

- `/search?q=<script>alert(1)</script>` → script tag is HTML-escaped
  by Blade's `{{ }}` (no unescaped output). ✅

### 3.7 Payment callback security (Sadad PSP)

- `POST /user/payments/callback/S-FAKE?signature=forged` (auth'd) → **403** ✅
- `POST /user/payments/callback/S-FAKE` (no signature) → **403** ✅
- Valid signatures computed via `hash_equals()` (constant-time comparison)
  to prevent timing attacks.

### 3.8 Cache TTLs on SEO files

| File | Cache-Control |
|---|---|
| `/robots.txt` | `public, max-age=3600` (1 hour) |
| `/sitemap.xml` | `public, max-age=900` (15 min) |
| `/sitemaps/categories.xml` | `public, max-age=900` (15 min) |

---

## 4. Improvements applied in this audit

### 4.1 Performance / rendering
- Switched the layout-provinces cache from serializing Eloquent Collections
  (which broke under PHP dev server because of `__PHP_Incomplete_Class`)
  to plain arrays. This **fixed the homepage 500 errors** that were
  silently killing the previous Lighthouse runs.
- Updated `layouts/app.blade.php` to consume the cached provinces as
  plain arrays (using `$p['id']` instead of `$p->id`).

### 4.2 Security
- Created `App\Http\Middleware\SecurityHeaders` and registered it in
  `bootstrap/app.php`. Adds the seven security headers above to every
  response (HTML, robots.txt, sitemap.xml).
- Strips the `X-Powered-By` header that PHP adds by default.
- Only adds HSTS when the request came over HTTPS (so dev servers aren't
  locked in).
- CSP is conservative enough to not break any existing inline scripts
  while blocking classic XSS vectors (object-src 'none',
  frame-ancestors 'self', form-action 'self').

### 4.3 SEO
- `robots.txt` now sends `Cache-Control: public, max-age=3600` so Google's
  crawler doesn't re-fetch it for every URL it discovers.
- All sitemaps were already properly cached.

### 4.4 Accessibility
- Added explicit touch-target sizing rules for `.login-button` and
  `.link-button` (min 40×40 px) — passes WCAG 2.5.5.
- Bumped footer link colors from `#647180` (4.49:1 contrast on dark
  background) to `#d1d5db` (9.46:1 contrast) — passes WCAG AAA.
- Bumped footer nav container colors to inherit a passing contrast.
- Result: Accessibility score went from **93 → 100** on all pages.

---

## 5. Files modified

| File | Change |
|---|---|
| `app/Http/Middleware/SecurityHeaders.php` | **NEW** — security header middleware |
| `bootstrap/app.php` | Register SecurityHeaders middleware |
| `app/Providers/AppServiceProvider.php` | Cache plain arrays instead of Eloquent Collections |
| `app/Http/Controllers/SeoController.php` | Add Cache-Control header to robots.txt |
| `resources/views/layouts/app.blade.php` | Footer a11y CSS + plain-array access for layoutProvinces |
| `public/assets/app.css` | Same a11y CSS appended for Vite-built environments |
| `analysis/audits_2026_09_10/` | Raw Lighthouse JSON/HTML + benchmark CSVs |

## 6. Test suite

- All **97 tests pass** (317 assertions) after the changes.
- The fixes are non-breaking; existing functionality is unchanged.
