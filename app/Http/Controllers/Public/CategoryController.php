<?php

declare(strict_types=1);
namespace App\Http\Controllers\Public;
use App\Domains\Seo\SeoPolicy;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
class CategoryController extends Controller {
    public function show(Category $category, SeoPolicy $seo): View {
        abort_unless($category->is_active,404);
        // PRD §45 - internal linking: category page links to subcategories
        // (existing) and to the busiest city×category landing pages (new).
        $citiesForCategory = Cache::remember('public.cat_cities.v1.'.$category->id, now()->addHour(), fn (): array => City::query()
            ->where('is_active', true)
            ->whereHas('ads', fn ($query) => $query->publiclyVisible()->where('category_id', $category->id))
            ->withCount(['ads' => fn ($query) => $query->publiclyVisible()->where('category_id', $category->id)])
            ->orderByDesc('ads_count')
            ->limit(12)
            ->get(['id', 'name', 'slug'])
            ->all());
        return view('public.categories.show',[
            'category'=>$category->load(['children'=>fn($q)=>$q->active()]),
            'ads'=>Ad::query()->publiclyVisible()->where('category_id',$category->id)->with(['city','images'])->withCount('links')->orderedForListing()->paginate(24),
            'chain'=>$seo->categoryChain($category),
            'citiesForCategory'=>collect($citiesForCategory),
        ]);
    }
}
