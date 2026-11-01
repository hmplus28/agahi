<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Seo\SeoPolicy;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function robots(): Response
    {
        return response(
            "User-agent: *\n".
            "Disallow: /admin\n".
            "Disallow: /user\n".
            "Disallow: /login\n".
            "Disallow: /register\n".
            "Disallow: /password\n".
            "Disallow: /search?*\n".
            "\n".
            "Sitemap: ".route('sitemap.index')."\n",
            200,
            [
                'Content-Type'  => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'public, max-age=3600',
            ],
        );
    }

    public function sitemapIndex(SeoPolicy $seo): Response
    {
        $pages = Cache::remember('seo.sitemap.pages.v1', now()->addMinutes(15), fn (): int => max(1, (int) ceil(Ad::query()->publiclyVisible()->count() / 1000)));



        $staticPages = [
            ['loc' => route('home'),                'lastmod' => now()->toAtomString(), 'priority' => '1.0'],
            ['loc' => route('search'),              'lastmod' => now()->toAtomString(), 'priority' => '0.9'],
            ['loc' => route('about'),               'lastmod' => now()->toAtomString(), 'priority' => '0.6'],
            ['loc' => route('contact'),             'lastmod' => now()->toAtomString(), 'priority' => '0.6'],
            ['loc' => route('terms'),               'lastmod' => now()->toAtomString(), 'priority' => '0.5'],
            ['loc' => route('site-ads'),            'lastmod' => now()->toAtomString(), 'priority' => '0.8'],
            ['loc' => route('site-ads.pricing'),    'lastmod' => now()->toAtomString(), 'priority' => '0.7'],
            ['loc' => route('site-ads.rules'),       'lastmod' => now()->toAtomString(), 'priority' => '0.5'],
            ['loc' => route('site-ads.sites'),       'lastmod' => now()->toAtomString(), 'priority' => '0.7'],
            ['loc' => route('site-ads.samples'),    'lastmod' => now()->toAtomString(), 'priority' => '0.5'],
        ];

        return response()->view('seo.sitemap-index', [
            'pages'       => range(1, $pages),
            'staticPages' => $staticPages,
        ], 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    public function adsSitemap(int $page, SeoPolicy $seo): Response
    {
        abort_if($page < 1, 404);




        $ads = Ad::query()
            ->publiclyVisible()
            ->with(['city', 'adServices.tariff'])
            ->orderedForListing()
            ->forPage($page, 1000)
            ->get();

        abort_if($ads->isEmpty() && $page > 1, 404);

        return response()->view('seo.ads-sitemap', compact('ads', 'seo'), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    public function categoriesSitemap(): Response
    {

        $categories = Cache::remember('seo.sitemap.categories.v2', now()->addMinutes(15), function (): array {
            return Category::query()->active()->orderBy('id')->get(['slug', 'updated_at'])->map(fn (Category $category): array => [
                'slug' => $category->slug,
                'lastmod' => $category->updated_at->toAtomString(),
            ])->all();
        });

        return response()->view('seo.categories-sitemap', compact('categories'), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    /**
     * PRD §44 - locations sitemap: city pages (+ whitelisted city×category
     * landing pages from PRD §42) that are real, indexable HTTP 200 pages.
     */
    public function locationsSitemap(SeoPolicy $seo): Response
    {
        $payload = Cache::remember('seo.sitemap.locations.v1', now()->addMinutes(15), function (): array {
            $cities = City::query()
                ->where('is_active', true)
                ->whereHas('ads', fn ($query) => $query->publiclyVisible())
                ->orderBy('id')
                ->get(['id', 'slug'])
                ->map(fn (City $city): array => [
                    'loc'     => route('city.show', $city->slug),
                    'lastmod' => now()->toAtomString(),
                ])->all();

            $whitelist = collect(preg_split('/[\n\r,]+/', (string) \App\Models\SiteSetting::get('seo.landing_whitelist', '')) ?: [])
                ->map(fn (string $line): string => trim(mb_strtolower(trim($line, "/ \t\n\r")), " /"))
                ->filter()
                ->values();

            $landings = [];
            if ($whitelist->isNotEmpty()) {
                foreach ($whitelist as $combo) {
                    [$citySlug, $categorySlug] = array_pad(explode('/', $combo, 2), 2, null);
                    if (!$citySlug || !$categorySlug) continue;
                    $city = City::query()->where('slug', $citySlug)->where('is_active', true)->first();
                    $category = Category::query()->active()->where('slug', $categorySlug)->first();
                    if (!$city || !$category) continue;
                    $hasAds = Ad::query()->publiclyVisible()->where('city_id', $city->id)->where('category_id', $category->id)->exists();
                    if (!$hasAds) continue;
                    $landings[] = ['loc' => route('city.category', ['citySlug' => $city->slug, 'categorySlug' => $category->slug]), 'lastmod' => now()->toAtomString()];
                }
            }

            return ['cities' => $cities, 'landings' => $landings];
        });

        return response()->view('seo.locations-sitemap', [
            'cities'   => $payload['cities'],
            'landings' => $payload['landings'],
        ], 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
