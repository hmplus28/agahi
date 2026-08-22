<?php

declare(strict_types=1);

namespace App\Domains\Seo;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use Illuminate\Support\Str;

final class SeoPolicy
{
    public function isIndexableAd(Ad $ad): bool { return $ad->status === AdStatus::Active && $ad->published_at !== null && $ad->deleted_at === null; }
    public function robotsForAd(Ad $ad): string { return $this->isIndexableAd($ad) ? 'index, follow' : 'noindex, follow'; }
    public function titleForAd(Ad $ad): string { return trim($ad->title.' در '.($ad->city?->name ?? 'ایران').' | '.config('app.name')); }
    public function descriptionForAd(Ad $ad): string { return Str::limit(trim(strip_tags($ad->description)), 155, '…'); }
    public function canonicalForAd(Ad $ad): string { return $ad->publicUrl(); }
    public function breadcrumbForAd(Ad $ad): array
    {
        $items = [['name' => 'خانه', 'url' => route('home')]];
        if ($ad->category) $items[] = ['name' => $ad->category->title, 'url' => route('categories.show', $ad->category)];
        $items[] = ['name' => $ad->title, 'url' => $ad->publicUrl()];
        return $items;
    }
}
