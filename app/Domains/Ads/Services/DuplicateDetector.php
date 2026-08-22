<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Models\Ad;
use App\Support\PersianNormalizer;

final class DuplicateDetector
{
    /** @return array{title:string,description:string,title_hash:string,description_hash:string} */
    public function fingerprints(string $title, string $description): array
    {
        $normalizedTitle = PersianNormalizer::text($title);
        $normalizedDescription = PersianNormalizer::text($description);
        return ['title' => $normalizedTitle, 'description' => $normalizedDescription, 'title_hash' => hash('sha256', $normalizedTitle), 'description_hash' => hash('sha256', $normalizedDescription)];
    }

    public function exists(array $fingerprints, ?int $exceptAdId = null): bool
    {
        return Ad::query()->whereNull('deleted_at')->when($exceptAdId, fn ($query) => $query->whereKeyNot($exceptAdId))->where('normalized_title_hash', $fingerprints['title_hash'])->where('normalized_description_hash', $fingerprints['description_hash'])->exists();
    }
}
