<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Support\HomeBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {

        $categories = Cache::remember('public.root_categories.v2', now()->addHour(), function (): array {
            return Category::query()
                ->active()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->active()->select(['id', 'parent_id', 'title', 'slug', 'sort_order'])])
                ->orderBy('sort_order')
                ->get(['id', 'title', 'slug', 'sort_order'])
                ->map(fn (Category $category): array => [
                    'title' => $category->title,
                    'slug' => $category->slug,
                    'children' => $category->children->map(fn (Category $child): array => [
                        'title' => $child->title,
                        'slug' => $child->slug,
                    ])->values()->all(),
                ])
                ->values()
                ->all();
        });

        // Home is a plain listing of publicly visible ads - no separate
        // "featured" section. Featured/urgent ads still float to the top via
        // orderedForListing(); 100 cards per page, the rest paginates.
        // پیش‌فرض صفحهٔ اصلی فقط آگهی‌های تهران است؛ با ?all=1 آگهی‌های همهٔ شهرها نمایش داده می‌شود.
        $tehranOnly = ! $request->boolean('all');
        $adsQuery = Ad::query()->publiclyVisible()->with(['city', 'images'])->withCount('links');
        if ($tehranOnly) {
            $adsQuery->whereHas('city', fn ($q) => $q->where('slug', 'tehran'));
        }
        $ads = $adsQuery->orderedForListing()->paginate(100)->withQueryString();
        $banner = HomeBanner::get();

        // PRD §32 - separate "special ads" section on the homepage.
        // Cached 5 minutes (PRD §58 HOMEPAGE SECTIONS 1-5min).
        $featuredAds = Cache::remember('public.featured_ads.v1', now()->addMinutes(5), fn (): array => Ad::query()
            ->publiclyVisible()
            ->where('is_featured', true)
            ->with(['city', 'images'])
            ->withCount('links')
            ->orderedForListing()
            ->limit(8)
            ->get()
            ->all());

        // PRD §45 - internal linking: home links to the busiest city pages.
        $topCities = Cache::remember('public.top_cities.v2', now()->addHour(), fn (): array => \App\Models\City::query()
            ->where('is_active', true)
            ->whereHas('ads', fn ($query) => $query->publiclyVisible())
            ->withCount(['ads' => fn ($query) => $query->publiclyVisible()])
            ->orderByDesc('ads_count')
            ->limit(12)
            ->get(['id', 'name', 'slug'])
            ->all());

        $totalActive = Cache::remember('public.total_active_ads.v1', now()->addMinutes(10), fn (): int => Ad::query()->publiclyVisible()->count());

        $allCategories = Category::query()->active()->select(['id', 'parent_id', 'title'])->orderBy('title')->get();

        // Province/city data for the home search card popup filter.
        // Cached as plain arrays (not Eloquent models) so unserialize() can
        // never hit __PHP_Incomplete_Class after code changes or deploys.
        $provinces = Cache::remember('public.geo.provinces.v1', now()->addHour(), fn (): array => \App\Models\Province::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'country_id'])
            ->map(fn ($p): array => ['id' => $p->id, 'name' => $p->name, 'country_id' => $p->country_id])
            ->all());
        $cities = Cache::remember('public.geo.cities.v1', now()->addHour(), fn (): array => \App\Models\City::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'province_id'])
            ->map(fn ($c): array => ['id' => $c->id, 'name' => $c->name, 'province_id' => $c->province_id])
            ->all());

        return view('public.home', [
            'categories' => $categories,
            'ads' => $ads,
            'banner' => $banner,
            'allCategories' => $allCategories,
            'totalActive' => $totalActive,
            'tehranOnly'  => $tehranOnly,
            'provinces' => collect($provinces),
            'cities' => collect($cities),
            'featuredAds' => collect($featuredAds),
            'topCities' => collect($topCities),
        ]);
    }
}
