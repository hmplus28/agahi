<?php

declare(strict_types=1);

namespace App\Domains\Seo;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

final class SeoPolicy
{
    public function isIndexableAd(Ad $ad): bool { return $ad->status === AdStatus::Active && $ad->published_at !== null && $ad->deleted_at === null; }
    public function robotsForAd(Ad $ad): string { return $this->isIndexableAd($ad) ? 'index, follow' : 'noindex, follow'; }
    public function titleForAd(Ad $ad): string { return trim($ad->title.' در '.($ad->city?->name ?? 'ایران').' | '.config('app.name')); }
    public function descriptionForAd(Ad $ad): string { return Str::limit(trim(strip_tags($ad->description)), 155, '…'); }
    public function canonicalForAd(Ad $ad): string { return $ad->publicUrl(); }

    /** Full ancestor chain of a category, root first, self last. */
    public function categoryChain(Category $category): array
    {
        $chain = [];
        $node = $category;
        $guard = 0;
        while ($node !== null && $guard++ < 10) {
            array_unshift($chain, $node);
            $node = $node->parent_id ? $node->parent : null;
        }
        return $chain;
    }

    /** PRD §50: خانه > صنعت > ماشین‌آلات > CNC > عنوان آگهی */
    public function breadcrumbForAd(Ad $ad): array
    {
        $items = [['name' => 'خانه', 'url' => route('home')]];
        if ($ad->category) {
            foreach ($this->categoryChain($ad->category) as $node) {
                $items[] = ['name' => $node->title, 'url' => route('categories.show', $node)];
            }
        }
        $items[] = ['name' => $ad->title, 'url' => $ad->publicUrl()];
        return $items;
    }

    /**
     * PRD §42 faceted-navigation whitelist: a city×category landing page is
     * only indexable when the admin whitelist is empty (sane default: every
     * combo that actually has ads) or the combo is explicitly listed in the
     * «seo.landing_whitelist» setting (one "city-slug/category-slug" per line).
     */
    public function robotsForCityCategory(City $city, Category $category, bool $hasAds): string
    {
        $raw = (string) SiteSetting::get('seo.landing_whitelist', '');
        $whitelist = collect(preg_split('/[\n\r,]+/', $raw) ?: [])
            ->map(fn (string $line): string => trim(mb_strtolower(trim($line, "/ \t\n\r")), " /"))
            ->filter()
            ->values();

        if ($whitelist->isNotEmpty()) {
            $key = mb_strtolower($city->slug.'/'.$category->slug);
            return $whitelist->contains($key) && $hasAds ? 'index, follow' : 'noindex, follow';
        }

        return $hasAds ? 'index, follow' : 'noindex, follow';
    }
}
