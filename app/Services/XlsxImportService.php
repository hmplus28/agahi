<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Support\PersianNormalizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * ورود اطلاعات جغرافیایی (کشور/استان/شهر) از فایل اکسل.
 */
class XlsxImportService
{
    /**
     * @return array{created:int,updated:int,skipped:int,errors:array<int,string>}
     */
    public function run(UploadedFile $file, string $type): array
    {
        $path = $file->getRealPath();

        $reader = new XlsxReader($path);

        $rows = $reader->dataRows(true);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2; // سطر واقعی فایل (هدر در سطر ۱)

            try {
                $result = match ($type) {
                    'countries' => $this->importCountry($row),
                    'provinces' => $this->importProvince($row),
                    'cities' => $this->importCity($row),
                    default => throw new \InvalidArgumentException('نوع نامعتبر برای ورود اطلاعات.'),
                };
            } catch (\Throwable $e) {
                $errors[] = "سطر {$line}: ".$e->getMessage();
                $skipped++;
                continue;
            }

            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }
        }

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function col(array $row, int $index): string
    {
        $value = $row[$index] ?? '';
        if (is_numeric($value)) {
            $normalized = (string) $value;
        } else {
            $normalized = PersianNormalizer::text((string) $value);
        }

        return trim($normalized);
    }

    private function importCountry(array $row): string
    {
        $name = $this->col($row, 0);
        if ($name === '') {
            throw new \RuntimeException('نام کشور خالی است.');
        }

        $slug = $this->col($row, 1) ?: $this->slugify($name);

        $country = Country::query()->where('slug', $slug)->first();

        if ($country) {
            $changed = $country->name !== $name;
            $country->update(['name' => $name, 'is_active' => true]);

            return $changed ? 'updated' : 'skipped';
        }

        Country::query()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return 'created';
    }

    private function importProvince(array $row): string
    {
        $countryName = $this->col($row, 0);
        $name = $this->col($row, 1);

        if ($countryName === '' || $name === '') {
            throw new \RuntimeException('نام کشور یا استان خالی است.');
        }

        $country = Country::query()->where('name', $countryName)->orWhere('slug', $this->slugify($countryName))->first();
        if (! $country) {
            throw new \RuntimeException("کشور «{$countryName}» یافت نشد. ابتدا کشور را وارد کنید.");
        }

        $slug = $this->col($row, 2) ?: $this->slugify($name);

        $province = Province::query()
            ->where('country_id', $country->id)
            ->where(function ($q) use ($slug, $name) {
                $q->where('slug', $slug)->orWhere('name', $name);
            })
            ->first();

        if ($province) {
            $changed = $province->name !== $name || $province->slug !== $slug;
            $province->update(['name' => $name, 'slug' => $slug, 'is_active' => true]);

            return $changed ? 'updated' : 'skipped';
        }

        Province::query()->create([
            'country_id' => $country->id,
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return 'created';
    }

    private function importCity(array $row): string
    {
        $provinceName = $this->col($row, 0);
        $name = $this->col($row, 1);

        if ($provinceName === '' || $name === '') {
            throw new \RuntimeException('نام استان یا شهر خالی است.');
        }

        $province = Province::query()
            ->where(function ($q) use ($provinceName) {
                $q->where('name', $provinceName)->orWhere('slug', $this->slugify($provinceName));
            })
            ->first();
        if (! $province) {
            throw new \RuntimeException("استان «{$provinceName}» یافت نشد. ابتدا استان را وارد کنید.");
        }

        $slug = $this->col($row, 2) ?: $this->slugify($name);

        $city = City::query()
            ->where('province_id', $province->id)
            ->where(function ($q) use ($slug, $name) {
                $q->where('slug', $slug)->orWhere('name', $name);
            })
            ->first();

        if ($city) {
            $changed = $city->name !== $name || $city->slug !== $slug;
            $city->update(['name' => $name, 'slug' => $slug, 'is_active' => true]);

            return $changed ? 'updated' : 'skipped';
        }

        City::query()->create([
            'province_id' => $province->id,
            'name' => $name,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return 'created';
    }

    private function slugify(string $text): string
    {
        return Str::slug($text) ?: 'item-'.substr(md5($text), 0, 8);
    }
}
