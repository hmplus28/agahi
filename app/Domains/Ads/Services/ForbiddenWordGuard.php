<?php

declare(strict_types=1);

namespace App\Domains\Ads\Services;

use App\Models\ForbiddenWord;
use App\Support\PersianNormalizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class ForbiddenWordGuard
{
    public function assertAllowed(array $values): void
    {
        $words = Cache::remember('moderation.forbidden_words.v1', now()->addMinutes(10), fn () => ForbiddenWord::query()->where('is_active', true)->pluck('normalized_word')->all());
        $haystack = PersianNormalizer::text(implode(' ', array_filter($values)));
        foreach ($words as $word) {
            if ($word !== '' && mb_stripos($haystack, $word) !== false) {
                throw ValidationException::withMessages(['title' => 'متن آگهی شامل عبارت غیرمجاز است.']);
            }
        }
    }
}
