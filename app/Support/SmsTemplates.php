<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

final class SmsTemplates
{
    private const KEY = 'sms.templates';

    public static function all(): array
    {
        $templates = SiteSetting::get(self::KEY, []);
        return is_array($templates) ? array_values($templates) : [];
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $template) {
            if (($template['id'] ?? null) === $id) return $template;
        }

        return null;
    }

    public static function save(string $label, string $text, ?string $id = null): string
    {
        $templates = self::all();
        $id ??= Str::slug(Str::random(8));
        $templates = array_values(array_filter($templates, fn (array $t): bool => ($t['id'] ?? null) !== $id));
        $templates[] = ['id' => $id, 'label' => $label, 'text' => $text];
        SiteSetting::put(self::KEY, $templates);

        return $id;
    }

    public static function delete(string $id): void
    {
        SiteSetting::put(self::KEY, array_values(array_filter(
            self::all(),
            fn (array $t): bool => ($t['id'] ?? null) !== $id,
        )));
    }
}
