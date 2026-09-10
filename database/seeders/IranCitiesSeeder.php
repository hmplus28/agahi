<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder کامل شهرهای ایران بر اساس آخرین تقسیمات کشوری رسمی.
 * منبع داده: sajaddp/list-of-cities-in-Iran (بر اساس مرکز آمار ایران)
 *
 * php artisan db:seed --class=IranCitiesSeeder
 */
class IranCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $provincesData = json_decode(
            (string) file_get_contents(database_path('data/iran-provinces.json')), true
        );
        $citiesData = json_decode(
            (string) file_get_contents(database_path('data/iran-cities.json')), true
        );

        if (! is_array($provincesData) || ! is_array($citiesData)) {
            $this->command?->error('فایل‌های داده شهرها یافت نشدند: database/data/iran-{provinces,cities}.json');

            return;
        }

        $iran = Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران', 'is_active' => true]);

        // ── استان‌ها ─────────────────────────────────
        $provinceMap = []; // dataset id => local id
        foreach ($provincesData as $p) {
            $province = Province::query()->updateOrCreate(
                ['name' => $p['name']],
                ['country_id' => $iran->id, 'slug' => $this->uniqueSlug('provinces', $p['name'], $p['slug'] ?? null), 'is_active' => true]
            );
            $provinceMap[$p['id']] = $province->id;
        }

        // ── شهرها ────────────────────────────────────
        $validCityKeys = [];
        $sort = [];
        $now = now();
        $rows = [];

        // نام پایه هر استان برای شناسایی ناحیه‌های شماره‌دار (مثل «مشهد ۱»)
        $baseNamesByProvince = [];
        foreach ($citiesData as $c) {
            $localId = $provinceMap[$c['province_id']] ?? null;
            if ($localId !== null) {
                $baseNamesByProvince[$localId][$this->normalizeBase(trim($c['name']))] = true;
            }
        }

        foreach ($citiesData as $c) {
            $localProvinceId = $provinceMap[$c['province_id']] ?? null;
            if ($localProvinceId === null) {
                continue;
            }

            $name = trim($c['name']);

            // حذف نواحی/مناطق شهری شماره‌دار مثل «اراک 1» یا «اسلام شهر2»
            if (preg_match('/^(.*?)[\s\x{200c}]*[0-9۰-۹]+$/u', $name, $m) && isset($baseNamesByProvince[$localProvinceId][$this->normalizeBase($m[1])])) {
                continue;
            }

            $key = $localProvinceId.'|'.$name;

            if (isset($validCityKeys[$key])) {
                continue; // حذف تکراری‌ها درون یک استان
            }
            $validCityKeys[$key] = true;
            $sort[$localProvinceId] = ($sort[$localProvinceId] ?? 0) + 1;

            $existing = City::query()
                ->where('province_id', $localProvinceId)
                ->where('name', $name)
                ->first();

            if ($existing) {
                $existing->update(['is_active' => true, 'slug' => $existing->slug ?: $this->citySlug($name)]);
                continue;
            }

            $rows[] = [
                'province_id' => $localProvinceId,
                'name' => $name,
                'slug' => $this->citySlug($name),
                'is_active' => true,
                'sort_order' => $sort[$localProvinceId],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('cities')->insertOrIgnore($chunk);
        }

        // ── پاک‌سازی: غیرفعال کردن داده‌های قدیمی خارج از لیست رسمی ──
        $stale = City::query()
            ->whereNotIn(DB::raw("CONCAT(province_id,'|',name)"), array_keys($validCityKeys))
            ->whereDoesntHave('ads')
            ->delete();

        Province::query()
            ->whereNotIn('id', array_values($provinceMap))
            ->whereDoesntHave('cities')
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('ads')->whereColumn('ads.province_id', 'provinces.id'))
            ->delete();

        $this->command?->info('استان‌ها: '.Province::count().' | شهرها: '.City::count().($stale ? " | موارد قدیمی حذف‌شده: $stale" : ''));
    }

    private function normalizeBase(string $name): string
    {
        return str_replace([' ', "\u{200c}", 'ي', 'ك'], ['', ' ', 'ی', 'ک'], trim($name));
    }

    private function uniqueSlug(string $table, string $name, ?string $fallback): string
    {
        $base = $this->citySlug($name);

        return $base !== '' ? $base : ($fallback ?? 'item-'.md5($table.$name));
    }

    private function citySlug(string $name): string
    {
        $map = [
            'آ' => 'a', 'أ' => 'a', 'ا' => 'a', 'ب' => 'b', 'پ' => 'p', 'ت' => 't', 'ث' => 's',
            'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'z', 'ر' => 'r',
            'ز' => 'z', 'ژ' => 'zh', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'z', 'ط' => 't',
            'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'gh', 'ک' => 'k', 'ك' => 'k',
            'گ' => 'g', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'و' => 'v', 'ؤ' => 'v', 'ه' => 'h',
            'ی' => 'y', 'ي' => 'y', 'ئ' => 'y', 'ة' => 'h', 'ء' => '',
        ];

        $slug = strtr($name, $map);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'city-'.substr(md5($name), 0, 8);
    }
}
