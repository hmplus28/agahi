<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'خدمات' => [
                'پزشکی و زیبایی' => ['آرایشی و بهداشتی', 'کلینیک زیبایی', 'پوست و مو', 'دندانپزشکی'],
                'تعمیرات' => ['لوازم خانگی', 'موبایل و لپ‌تاپ', 'خودرو', 'کولر و یخچال'],
                'آرایشگاه و سالن زیبایی' => ['آرایشگاه زنانه', 'آرایشگاه مردانه', 'سالن ناخن', 'میکاپ'],
                'سالم و تناسب‌اندام' => ['باشگاه بدنسازی', 'مربی خصوصی', 'فیزیوتراپی', 'ماساژ'],
            ],
            'املاک' => [
                'فروش مسکونی' => ['آپارتمان', 'ویلا', 'زمین', 'کلنگی'],
                'اجاره مسکونی' => ['آپارتمان', 'ویلا', 'اتاق و سوئیت', 'دفتر کار'],
                'فروش تجاری' => ['مغازه', 'دفتر کار', 'انبار', 'سوله'],
                'اجاره تجاری' => ['مغازه', 'دفتر کار', 'انبار', 'سوله'],
            ],
            'وسایل نقلیه' => [
                'خودرو سواری' => ['سدان', 'شاسی‌بلند', 'وانت', 'هیبریدی و برقی'],
                'موتورسیکلت' => ['اسکوتر', 'موتور شهری', 'موتور آفرود', 'کروز'],
                'قطعات و لوازم خودرو' => ['لاستیک و رینگ', 'باتری', 'لوازم یدکی', 'روغن و فیلتر'],
            ],
            'لوازم خانگی و صنعتی' => [
                'لوازم آشپزخانه' => ['یخچال و فریزر', 'گاز و اجاق', 'ماشین لباسشویی', 'ماشین ظرفشویی'],
                'صوتی و تصویری' => ['تلویزیون', 'سیستم صوتی', 'دوربین مداربسته', 'پروژکتور'],
                'صنعتی' => ['ماشین‌آلات', 'ابزارآلات', 'تجهیزات اداری', 'مواد اولیه'],
            ],
            'کیف، کفش و پوشاک' => [
                'مردانه' => ['بافتنی', 'کت و شلوار', 'پیراهن', 'تی‌شرت و پولو'],
                'زنانه' => ['مانتو', 'شال و روسری', 'پیراهن', 'تی‌شرت و بلوز'],
                'بچگانه' => ['پسرانه', 'دخترانه', 'نوزاد', 'کفش'],
                'کیف و کفش' => ['کیف زنانه', 'کیف مردانه', 'کفش مردانه', 'کفش زنانه'],
            ],
            'دیجیتال' => [
                'موبایل' => ['گوشی موبایل', 'تبلت', 'لوازم جانبی', 'قطعات'],
                'کامپیوتر و لپ‌تاپ' => ['لپ‌تاپ', 'کامپیوتر', 'مانیتور', 'قطعات'],
                'کنسول و بازی' => ['کنسول', 'بازی', 'لوازم جانبی'],
                'عکاسی' => ['دوربین', 'لنز', 'لوازم جانبی'],
            ],
            'آموزش و کتاب' => [
                'کتاب' => ['کتاب ادبیات', 'کتاب علمی', 'کتاب کودک', 'کتاب دانشگاهی'],
                'آموزش' => ['آموزش زبان', 'آموزش موسیقی', 'آموزش هنر', 'آموزش ورزشی'],
                'ابزار آموزشی' => ['لوازم تحریر', 'ماشین حساب', 'تخته وایت‌بورد'],
            ],
            'ورزش و سرگرمی' => [
                'تجهیزات ورزشی' => ['لوازم بدنسازی', 'توپ و راکت', 'کفش ورزشی', 'لباس ورزشی'],
                'موسیقی' => ['ساز گیتار', 'ساز سنتی', 'پخش و میکسر', 'کیبورد'],
                'ابزار باغبانی' => ['گیاه', 'بذر', 'ابزار کاشت'],
            ],
            'استخدام و کسب‌وکار' => [
                'استخدام' => ['فروش و بازاریابی', 'خدمات', 'اداری', 'تخصصی'],
                'خدمات کسب‌وکار' => ['طراحی وب', 'مشاوره', 'تبلیغات', 'حسابداری'],
            ],
        ];

        foreach ($tree as $group => $collections) {
            $gSlug = $this->uniqueSlug(Str::slug($group));
            $g = Category::query()->updateOrCreate(
                ['slug' => $gSlug],
                ['title' => $group, 'is_active' => true, 'sort_order' => 0]
            );

            foreach ($collections as $collection => $subs) {
                $cSlug = $this->uniqueSlug($gSlug . '-' . Str::slug($collection));
                $c = Category::query()->updateOrCreate(
                    ['slug' => $cSlug],
                    ['parent_id' => $g->id, 'title' => $collection, 'is_active' => true, 'sort_order' => 0]
                );

                foreach ($subs as $sub) {
                    $sSlug = $this->uniqueSlug($cSlug . '-' . Str::slug($sub));
                    Category::query()->updateOrCreate(
                        ['slug' => $sSlug],
                        ['parent_id' => $c->id, 'title' => $sub, 'is_active' => true, 'sort_order' => 0]
                    );
                }
            }
        }

        // Older DatabaseSeeder versions created a second «خدمات» root
        // (slug "services") alongside this seeder's root — merge duplicates
        // so the homepage category grid never shows the same title twice.
        $this->dedupeRootCategories();

        // Public pages cache the root-category grid; refresh it.
        Cache::forget('public.root_categories.v2');
    }

    /**
     * Merge root categories that share the same title. The root with the
     * most children wins; the thinner duplicates donate their ads and
     * children, then are deleted. Safe against restrictOnDelete on
     * ads.category_id (ads are re-pointed first) and nullOnDelete on
     * categories.parent_id (children are moved first).
     */
    private function dedupeRootCategories(): void
    {
        $roots = Category::query()->whereNull('parent_id')->orderBy('id')->get();
        $groups = [];

        foreach ($roots as $root) {
            $key = trim((string) $root->title);
            $groups[$key][] = $root;
        }

        foreach ($groups as $group) {
            if (count($group) < 2) {
                continue;
            }
            // Canonical: most children; ties broken by lowest id.
            $canonical = $group[0];
            foreach ($group as $root) {
                if ($root->children()->count() > $canonical->children()->count()) {
                    $canonical = $root;
                }
            }
            foreach ($group as $root) {
                if ($root->is($canonical)) {
                    continue;
                }
                $this->mergeRootInto($root, $canonical);
            }
        }
    }

    private function mergeRootInto(Category $duplicate, Category $canonical): void
    {
        // Ads attached to the duplicate root itself.
        Ad::query()->where('category_id', $duplicate->id)->update(['category_id' => $canonical->id]);

        foreach ($duplicate->children()->get() as $dupChild) {
            $twin = Category::query()
                ->where('parent_id', $canonical->id)
                ->where('title', $dupChild->title)
                ->first();

            if ($twin === null || $dupChild->children()->exists()) {
                // No same-title twin, or subtree too deep to merge: move it.
                $dupChild->update(['parent_id' => $canonical->id]);
                continue;
            }

            // Same-title leaf twin exists: donate the ads, then remove.
            Ad::query()->where('category_id', $dupChild->id)->update(['category_id' => $twin->id]);
            $dupChild->delete();
        }

        $duplicate->delete();
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 1;
        while (Category::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . ($i++);
        }
        return $slug;
    }
}
