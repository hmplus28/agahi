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
        return response("User-agent: *\nDisallow: /admin\nDisallow: /user\nDisallow: /login\nDisallow: /register\nSitemap: ".route('sitemap.index')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemapIndex(SeoPolicy $seo): Response
    {
        $pages = Cache::remember('seo.sitemap.pages.v1', now()->addMinutes(15), fn (): int => max(1, (int) ceil(Ad::query()->publiclyVisible()->count() / 1000)));

        return response()->view('seo.sitemap-index', ['pages' => range(1, $pages)], 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    public function adsSitemap(int $page, SeoPolicy $seo): Response
    {
        abort_if($page < 1, 404);
        $ads = Ad::query()->publiclyVisible()->with(['city'])->orderedForListing()->forPage($page, 1000)->get();
        abort_if($ads->isEmpty() && $page > 1, 404);

        return response()->view('seo.ads-sitemap', compact('ads', 'seo'), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
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
