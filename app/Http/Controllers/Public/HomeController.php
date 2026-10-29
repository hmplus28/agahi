<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Support\HomeBanner;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
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

        // Home is a plain listing of every publicly visible ad - no separate
        // "featured" section. Featured/urgent ads still float to the top via
        // orderedForListing(); 100 cards per page, the rest paginates.
        $ads = Ad::query()->publiclyVisible()->with(['city', 'images'])->withCount('links')->orderedForListing()->paginate(100);
        $banner = HomeBanner::get();

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
            'provinces' => collect($provinces),
            'cities' => collect($cities),
        ]);
    }
}
