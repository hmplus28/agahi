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

        return view('public.home', compact('categories', 'ads', 'banner', 'allCategories', 'totalActive'));
    }
}
