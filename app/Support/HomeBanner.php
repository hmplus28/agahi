<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class HomeBanner
{
    public const SETTING_KEY = 'home.banner';
    private const CACHE_KEY = 'public.home_banner.v1';

    public static function get(): ?array
    {
        $banner = Cache::remember(self::CACHE_KEY, now()->addHour(), function (): ?array {
            $setting = SiteSetting::get(self::SETTING_KEY);
            if (!is_array($setting) || !isset($setting['type'], $setting['path']) || ($setting['is_active'] ?? false) === false) return null;
            $disk = Storage::disk('public');
            if (!$disk->exists($setting['path'])) return null;
            if ($setting['type'] === 'html') {
                $html = $disk->get($setting['path']);

                return ['type' => 'html', 'html' => is_string($html) && $html !== '' ? $html : null, 'url' => null];
            }

            return ['type' => 'image', 'url' => '/storage/'.$setting['path'], 'html' => null];
        });

        return is_array($banner) ? $banner : null;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
