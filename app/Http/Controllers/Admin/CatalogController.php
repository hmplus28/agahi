<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\ForbiddenWord;
use App\Models\Province;
use App\Models\Tariff;
use App\Services\XlsxImportService;
use App\Support\PersianNormalizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogController extends Controller
{
    /** Types that can be bulk-imported from an XLSX file. */
    private const IMPORTABLE = ['countries', 'provinces', 'cities'];

    /** Map of catalog type → Eloquent model class. */
    private const MAP = [
        'categories'      => Category::class,
        'countries'       => Country::class,
        'provinces'       => Province::class,
        'cities'          => City::class,
        'tariffs'         => Tariff::class,
        'forbidden-words' => ForbiddenWord::class,
    ];

    private function model(string $type): string
    {
        abort_unless(isset(self::MAP[$type]), 404);
        return self::MAP[$type];
    }

    public function index(string $type): View
    {
        $model = $this->model($type);

        return view('admin.catalog.index', [
            'type'       => $type,
            'records'    => $model::query()->latest()->paginate(50),
            'parents'    => $type === 'categories' ? Category::query()->orderBy('title')->get(['id', 'title']) : collect(),
            'countries'  => $type === 'provinces' ? Country::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'provinces'  => $type === 'cities' ? Province::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'importable' => in_array($type, self::IMPORTABLE, true),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $model = $this->model($type);
        $model::query()->create($this->data($request, $type));
        $this->invalidate($type);

        return back()->with('success', 'رکورد جدید ثبت شد.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $model = $this->model($type);
        $record = $model::query()->findOrFail($id);
        $record->update($this->data($request, $type, $record));
        $this->invalidate($type);

        return back()->with('success', 'رکورد به‌روزرسانی شد.');
    }

    public function toggle(string $type, int $id): RedirectResponse
    {
        $model = $this->model($type);
        $record = $model::query()->findOrFail($id);
        $record->update(['is_active' => !$record->is_active]);
        $this->invalidate($type);

        return back()->with('success', 'وضعیت رکورد تغییر کرد.');
    }

    /**
     * Download the bundled sample XLSX so the admin can see the expected
     * columns before importing their own file.
     */
    public function sample(string $type): StreamedResponse
    {
        abort_unless(in_array($type, self::IMPORTABLE, true), 404);

        $filename = "{$type}.xlsx";
        $path = "samples/{$filename}";

        abort_unless(Storage::disk('local')->exists($path), 404, 'فایل نمونه یافت نشد.');

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Bulk-import location data from an uploaded XLSX file. Only the
     * geo types (countries / provinces / cities) are supported — other
     * types return 404.
     */
    public function import(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, self::IMPORTABLE, true), 404);

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        /** @var UploadedFile $file */
        $file = $data['file'];

        $result = app(XlsxImportService::class)->run($file, $type);

        $this->invalidate($type);

        $message = sprintf(
            'ورود اطلاعات کامل شد: %d رکورد جدید، %d به‌روزرسانی، %d رد شد.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
        );

        if (!empty($result['errors'])) {
            $message .= ' خطاها: ' . implode(' | ', array_slice($result['errors'], 0, 3));
        }

        return back()->with('success', $message);
    }

    private function invalidate(string $type): void
    {
        if ($type === 'categories') {
            Cache::forget('public.root_categories.v2');
            Cache::forget('seo.sitemap.categories.v2');
        }
        if ($type === 'forbidden-words') {
            Cache::forget('moderation.forbidden_words.v1');
        }
    }

    private function data(Request $request, string $type, ?Model $record = null): array
    {
        $base = [
            'is_active'   => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];

        $rules = match ($type) {
            'categories' => [...$base,
                'title'            => ['required', 'string', 'max:160'],
                'slug'             => ['required', 'string', 'max:190', 'unique:categories,slug,'.($record?->id ?? 'NULL')],
                'parent_id'        => ['nullable', 'exists:categories,id'],
                'description'      => ['nullable', 'string'],
                'seo_title'        => ['nullable', 'string', 'max:180'],
                'seo_description'  => ['nullable', 'string', 'max:320'],
            ],
            'countries' => [...$base,
                'name' => ['required', 'string', 'max:120'],
                'slug'  => ['required', 'string', 'max:160', 'unique:countries,slug,'.($record?->id ?? 'NULL')],
            ],
            'provinces' => [...$base,
                'country_id' => ['required', 'exists:countries,id'],
                'name'        => ['required', 'string', 'max:120'],
                'slug'         => ['required', 'string', 'max:160'],
            ],
            'cities' => [...$base,
                'province_id' => ['required', 'exists:provinces,id'],
                'name'         => ['required', 'string', 'max:120'],
                'slug'          => ['required', 'string', 'max:160'],
            ],
            'tariffs' => [...$base,
                'code'           => ['required', 'string', 'max:80', 'unique:tariffs,code,'.($record?->id ?? 'NULL')],
                'title'          => ['required', 'string', 'max:160'],
                'description'    => ['nullable', 'string'],
                'price'          => ['required', 'integer', 'min:0'],
                'service_type'   => ['required', 'in:ad,renewal,featured,colored,urgent,ladder,auto_ladder,extra_link,extra_image'],
                'duration_days'  => ['nullable', 'integer', 'min:1'],
            ],
            'forbidden-words' => [
                'word'      => ['required', 'string', 'max:190', 'unique:forbidden_words,word,'.($record?->id ?? 'NULL')],
                'is_active' => ['nullable', 'boolean'],
            ],
        };

        $data = $request->validate($rules);

        foreach (['title', 'name', 'word', 'description'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = PersianNormalizer::text($data[$field]);
            }
        }

        if ($type === 'forbidden-words') {
            $data['normalized_word'] = PersianNormalizer::text($data['word']);
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = $request->boolean('is_active');
        }
        if (isset($data['sort_order'])) {
            $data['sort_order'] = (int) $data['sort_order'];
        }

        return $data;
    }
}
