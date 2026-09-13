<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Seo\SeoPolicy;
use App\Models\Ad;
use App\Models\Category;
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

        // Static "always indexable" pages — listed in a dedicated
        // sitemap entry so Google sees them in the index.
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

        // Load ads with the ladder service so we can use last_ladder_at
        // as the lastmod for ladder ads — that way Google sees fresh
        // timestamps every time the daily cron bumps them.
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
        /** @var array<int,array{slug:string,lastmod:string}> $categories */
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
}
