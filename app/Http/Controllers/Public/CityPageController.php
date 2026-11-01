<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domains\Seo\SeoPolicy;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use Illuminate\View\View;

/**
 * PRD §38 URL architecture - crawlable city/category hierarchy:
 *   /tehran/                  city listing
 *   /tehran/industrial/       city × root category
 *   /tehran/industrial/cnc/   city × sub category
 * (deep ad URLs like /tehran/industrial/cnc/x/ad/481432 live in
 *  Public\AdController@deepAd and 301 to the canonical /ad/{code}/{slug}.)
 */
final class CityPageController extends Controller
{
    public function city(SeoPolicy $seo, string $citySlug): View
    {
        $city = City::query()->where('slug', $citySlug)->where('is_active', true)->first();
        abort_if($city === null, 404);

        $ads = Ad::query()
            ->publiclyVisible()
            ->where('city_id', $city->id)
            ->with(['category', 'images'])
            ->withCount('links')
            ->orderedForListing()
            ->paginate(24)
            ->withQueryString();

        $rootCategories = Category::query()->active()->whereNull('parent_id')->orderBy('sort_order')->get(['id', 'title', 'slug']);

        return view('public.city', [
            'city'           => $city,
            'ads'            => $ads,
            'rootCategories' => $rootCategories,
            'robots'         => $ads->total() > 0 ? 'index, follow' : 'noindex, follow',
            'seo'            => $seo,
        ]);
    }

    public function category(SeoPolicy $seo, string $citySlug, string $categorySlug): View
    {
        return $this->listing($seo, $citySlug, $categorySlug, null);
    }

    public function subCategory(SeoPolicy $seo, string $citySlug, string $categorySlug, string $subSlug): View
    {
        return $this->listing($seo, $citySlug, $categorySlug, $subSlug);
    }

    private function listing(SeoPolicy $seo, string $citySlug, string $categorySlug, ?string $subSlug): View
    {
        $city = City::query()->where('slug', $citySlug)->where('is_active', true)->first();
        abort_if($city === null, 404);

        $chain = Category::query()->active()->where('slug', $categorySlug)->first();
        abort_if($chain === null, 404);

        $category = $chain;
        if ($subSlug !== null) {
            $category = Category::query()->active()->where('slug', $subSlug)->where('parent_id', $chain->id)->first();
            abort_if($category === null, 404);
        }

        $ads = Ad::query()
            ->publiclyVisible()
            ->where('city_id', $city->id)
            ->where('category_id', $category->id)
            ->with(['images'])
            ->withCount('links')
            ->orderedForListing()
            ->paginate(24)
            ->withQueryString();

        $siblings = Category::query()->active()
            ->where('parent_id', $category->parent_id)
            ->orderBy('sort_order')
            ->get(['id', 'title', 'slug']);

        return view('public.city-category', [
            'city'       => $city,
            'category'   => $category,
            'chain'      => $seo->categoryChain($category),
            'siblings'   => $siblings,
            'ads'        => $ads,
            'robots'     => $seo->robotsForCityCategory($city, $category, $ads->total() > 0),
            'seo'        => $seo,
        ]);
    }
}
