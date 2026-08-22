<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        /** @var array<int,array{title:string,slug:string,children:array<int,array{title:string,slug:string}>}> $categories */
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

        $featured = Ad::query()->publiclyVisible()->with(['city', 'images'])->where('is_featured', true)->orderedForListing()->limit(8)->get();
        $latest = Ad::query()->publiclyVisible()->with(['city', 'images'])->orderedForListing()->limit(12)->get();

        return view('public.home', compact('categories', 'featured', 'latest'));
    }
}
