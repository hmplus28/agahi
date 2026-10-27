<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Accounts\Enums\UserRole;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Support\PersianNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a rich, varied demo dataset for exercising the public site and the
 * admin panel - WITHOUT touching real data:
 *
 *  - Every demo ad is tagged with source='demo' (indexed column), so a
 *    re-run (or `php artisan demo:seed --flush`) wipes ONLY demo rows.
 *  - Demo users live on the 0930xxxxxxx mobile range / @demo.test emails
 *    and their plaintext passwords are stored (admin credential viewer).
 *  - Active ads default to 250 => homepage (100/page) paginates to 3 pages.
 *  - A fixed sprinkle of pending/expired/draft/needs-permit/inactive ads
 *    exercises the admin queues and status filters.
 *  - Flags (featured/urgent/colored/auto-ladder), prices (incl. توافقی),
 *    dates spread over ~60 days, all seeded cities and the whole category
 *    tree rotate deterministically, so listings look varied but stay
 *    stable enough for automated tests.
 */
class DemoContentSeeder extends Seeder
{
    public const DEMO_SOURCE = 'demo';

    public const DEMO_PASSWORD = 'Demo1234!';

    /** Number of publicly visible (active) ads the seeder creates. */
    public int $activeCount = 250;

    /** Fixed extra ads per non-active status (admin queue test data). */
    private const EXTRAS = [
        AdStatus::PendingApproval->value => 10,
        AdStatus::Expired->value => 8,
        AdStatus::Draft->value => 5,
        AdStatus::NeedsPermit->value => 3,
        AdStatus::Inactive->value => 2,
    ];

    private const DEMO_USERS = [
        ['first_name' => 'علی', 'last_name' => 'رضایی', 'gender_label' => 'فروشگاه علی'],
        ['first_name' => 'سارا', 'last_name' => 'محمدی', 'gender_label' => 'گالری سارا'],
        ['first_name' => 'حسین', 'last_name' => 'کریمی', 'gender_label' => 'تعمیرگاه حسین'],
        ['first_name' => 'مریم', 'last_name' => 'احمدی', 'gender_label' => 'بوتیک مریم'],
        ['first_name' => 'رضا', 'last_name' => 'موسوی', 'gender_label' => 'املاک رضا'],
        ['first_name' => 'نگار', 'last_name' => 'حسینی', 'gender_label' => 'استودیو نگار'],
        ['first_name' => 'امیر', 'last_name' => 'قاسمی', 'gender_label' => 'دیجی‌مارکت امیر'],
        ['first_name' => 'فاطمه', 'last_name' => 'نوری', 'gender_label' => 'آموزشگاه فاطمه'],
    ];

    /**
     * Realistic Persian subjects keyed by a coarse domain group that is
     * detected from category titles (NOT slugs - Str::slug mangles Persian,
     * so category slugs are unreliable for matching).
     */
    private const SUBJECTS = [
        'realestate' => [
            'آپارتمان ۸۵ متری نوساز', 'آپارتمان ۱۰۰ متری با پارکینگ', 'ویلا دوبلکس ۲۵۰ متری', 'ویلا ساحلی با استخر',
            'زمین کلنگی ۲۰۰ متری', 'مغازه دونیست ۴۰ متری', 'دفتر کار اداری مفروش', 'سوئیت مبله روزانه',
            'آپارتمان ۷۵ متری قرضی', 'انبار ۳۰۰ متری با رمپ',
        ],
        'vehicle' => [
            'پژو ۲۰۷ اتوماتیک پانوراما', 'پراید ۱۳۱ دوگانه‌سوز', 'سمند LX شخصی‌سازی', 'کیا سراتو ۲۰۲۱ فول',
            'هیوندای النترا بی‌رنگ', 'موتور هوندا ۱۲۵', 'وانت نیسان زامیاد', 'تیبا هاچبک مدل ۹۶',
            'شاسی‌بلند جک S5', 'دنا پلاس توربو اتوماتیک',
        ],
        'digital' => [
            'گوشی موبایل اپل iPhone 14', 'گوشی سامسونگ گلکسی S23', 'لپ‌تاپ ایسوس VivoBook', 'لپ‌تاپ لنوو IdeaPad',
            'مانیتور گیمینگ ۲۴ اینچ', 'تبلت آیپد ایر', 'کنسول پلی‌استیشن ۵', 'دوربین کانن EOS',
            'هدفون بی‌سیم سونی', 'ساعت هوشمند شیائومی',
        ],
        'service' => [
            'تعمیر تخصصی لباسشویی در محل', 'خدمات نقاشی ساختمان', 'کارواش سیار نانو', 'شوروم و تعمیرات موبایل',
            'خدمات بسته‌بندی اسباب‌کشی', 'آموزش خصوصی زبان انگلیسی', 'تعمیرات تخصصی یخچال و فریزر', 'چاپ و طراحی بنر',
        ],
        'job' => [
            'استخدام کارشناس فروش', 'استخدام ادمین اینستاگرام', 'استخدام برنامه‌نویس ارشد PHP', 'استخدام منشی فروشگاه',
            'استخدام حسابدار آشنا با هلو', 'استخدام پیک موتوری',
        ],
        'generic' => [
            'مبل راحتی هفت‌نفره', 'فرش ماشینی ۵۰۰ شانه', 'یخچال ساید بای ساید', 'ماشین لباسشویی اسنوا',
            'دوچرخه کوهستان سایز ۲۷.۵', 'تردمیل خانگی تاشو', 'گاز رومیزی ۵ شعله', 'کمد دیواری MDF',
            'توپ والیبال میکاسا', 'گیتار کلاسیک آلمبرتو',
        ],
    ];

    private const SUFFIXES = [
        'فوری', 'در حد نو', 'کاملاً سالم', 'با گارانتی', 'تحویل در محل', 'قیمت مناسب',
        'بدون رنگ', 'مدل جدید', 'یک ماه کارکرد', 'بدون خط و خش', 'فول آپشن', 'شارژ سالم',
    ];

    private const PRICE_RANGES = [
        'realestate' => [2_000_000_000, 25_000_000_000],
        'vehicle' => [300_000_000, 2_500_000_000],
        'digital' => [5_000_000, 120_000_000],
        'service' => [200_000, 20_000_000],
        'job' => [30_000_000, 90_000_000],
        'generic' => [500_000, 80_000_000],
    ];

    public function run(): void
    {
        // Make the seeder self-sufficient on a fresh database: it needs
        // categories and cities to attach ads to. Both are idempotent.
        $this->call(CategorySeeder::class);
        $this->call(IranCitiesSeeder::class);

        DB::transaction(function (): void {
            $this->flushDemoData();
            $users = $this->ensureDemoUsers();
            [$leaves, $cities] = $this->ensureCatalog();

            $created = $this->createActiveAds($users, $leaves, $cities);
            $extraCounts = $this->createQueueAds($users, $leaves, $cities);

            $this->command?->info(sprintf(
                'دیتای دمو ساخته شد: %d آگهی فعال + %d آگهی صف‌های مدیریتی (%s) + %d کاربر نمونه.',
                $created,
                array_sum($extraCounts),
                implode('/', $extraCounts),
                $users->count(),
            ));
        });
    }

    /**
     * Deletes ONLY demo-tagged rows (ads with source='demo'). Child rows
     * (images, links, histories, ...) are removed explicitly so the purge
     * works even on databases where FK cascading is disabled. Non-demo ads,
     * users, invoices and settings are never touched.
     */
    public function flushDemoData(): void
    {
        $ids = Ad::query()->where('source', self::DEMO_SOURCE)->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        foreach (['ad_status_histories', 'ad_images', 'ad_links', 'ad_services', 'ad_reports', 'ad_expiry_reminders'] as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->whereIn('ad_id', $ids)->delete();
            }
        }

        Ad::query()->where('source', self::DEMO_SOURCE)->delete();
    }

    private function ensureDemoUsers(): Collection
    {
        $users = collect();

        foreach (self::DEMO_USERS as $index => $person) {
            $users->push(User::query()->firstOrCreate(
                ['mobile' => sprintf('093000000%02d', $index + 1)],
                [
                    'email' => sprintf('demo-user%d@demo.test', $index + 1),
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'password' => Hash::make(self::DEMO_PASSWORD),
                    'plaintext_password' => self::DEMO_PASSWORD,
                    'role' => UserRole::User,
                    'is_active' => true,
                ],
            ));
        }

        return $users;
    }

    /**
     * @return array{0: Collection<int, Category>, 1: Collection<int, City>}
     */
    private function ensureCatalog(): array
    {
        // Prefer leaf categories (most specific); fall back to roots when a
        // minimal catalog has no children yet.
        $leaves = Category::query()->active()
            ->whereDoesntHave('children')
            ->orderBy('id')->get();
        if ($leaves->isEmpty()) {
            $leaves = Category::query()->active()->orderBy('id')->get();
        }

        $cities = City::query()->where('is_active', true)->with('province.country')->orderBy('id')->get();

        return [$leaves, $cities];
    }

    private function createActiveAds(Collection $users, Collection $leaves, Collection $cities): int
    {
        for ($i = 1; $i <= $this->activeCount; $i++) {
            $leaf = $leaves->values()[$i % $leaves->count()];
            $city = $cities->values()[$i % $cities->count()];
            $owner = $users->values()[$i % $users->count()];
            $group = $this->groupForCategory($leaf);

            $publishedAt = now()->subDays($i % 60)->subHours(($i * 7) % 24)->subMinutes(($i * 13) % 60);
            $this->createAd([
                'owner' => $owner,
                'leaf' => $leaf,
                'city' => $city,
                'group' => $group,
                'slug' => sprintf('demo-ad-%d', $i),
                'status' => AdStatus::Active,
                'published_at' => $publishedAt,
                'expires_at' => $publishedAt->copy()->addDays(30 + ($i % 61)),
                'sort_at' => $publishedAt,
                'price' => $this->priceFor($group, $i),
                'is_featured' => $i % 17 === 0,
                'is_urgent' => $i % 23 === 0,
                'is_colored' => $i % 29 === 0,
                'auto_ladder' => $i % 41 === 0,
                'views_count' => ($i * 37) % 900,
                'with_keywords' => $i % 3 === 0,
                'with_business' => $i % 4 === 0,
                'with_address' => $i % 3 === 0,
                'with_email' => $i % 5 === 0,
                'hide_mobile' => $i % 10 === 0,
                'long_description' => $i % 6 === 0,
            ]);
        }

        return min($this->activeCount, $this->activeCount);
    }

    /** @return array<string, int> status => created count */
    private function createQueueAds(Collection $users, Collection $leaves, Collection $cities): array
    {
        $counts = [];

        if ($this->activeCount < 20) {
            return $counts;
        }

        $i = 0;
        foreach (self::EXTRAS as $statusValue => $amount) {
            $status = AdStatus::from($statusValue);
            for ($n = 1; $n <= $amount; $n++) {
                $i++;
                $leaf = $leaves->values()[$i % $leaves->count()];
                $city = $cities->values()[$i % $cities->count()];
                $owner = $users->values()[$i % $users->count()];

                $publishedAt = match ($status) {
                    AdStatus::Expired => now()->subDays(50)->subHours($i),
                    AdStatus::Draft, AdStatus::PendingApproval, AdStatus::NeedsPermit => null,
                    default => now()->subDays(10 + $i),
                };

                $this->createAd([
                    'owner' => $owner,
                    'leaf' => $leaf,
                    'city' => $city,
                    'group' => $this->groupForCategory($leaf),
                    'slug' => sprintf('demo-%s-%d', str_replace('_', '-', $statusValue), $n),
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'expires_at' => match ($status) {
                        AdStatus::Expired => now()->subDays(5),
                        AdStatus::Draft, AdStatus::PendingApproval, AdStatus::NeedsPermit => null,
                        default => now()->addDays(20),
                    },
                    'sort_at' => $publishedAt,
                    'price' => $this->priceFor($this->groupForCategory($leaf), $i + 500),
                    'is_featured' => false,
                    'is_urgent' => false,
                    'is_colored' => false,
                    'auto_ladder' => false,
                    'views_count' => $i * 3,
                    'with_keywords' => false,
                    'with_business' => false,
                    'with_address' => false,
                    'with_email' => false,
                    'hide_mobile' => false,
                    'long_description' => false,
                ]);
            }
            $counts[$statusValue] = $amount;
        }

        return $counts;
    }

    /**
     * @param array<string, mixed> $spec
     */
    private function createAd(array $spec): void
    {
        /** @var User $owner */
        $owner = $spec['owner'];
        /** @var Category $leaf */
        $leaf = $spec['leaf'];
        /** @var City $city */
        $city = $spec['city'];
        /** @var AdStatus $status */
        $status = $spec['status'];

        $subject = $this->pick(self::SUBJECTS[$spec['group']], (int) ($spec['views_count'] ?? 0));
        $suffix = $this->pick(self::SUFFIXES, $spec['views_count'] + 7);
        $title = sprintf('%s %s - %s', $subject, $suffix, $city->name);
        $description = $this->buildDescription($title, $city->name, (bool) $spec['long_description']);

        $ad = new Ad;
        $ad->slug = $spec['slug'];
        $ad->user_id = $owner->id;
        $ad->title = $title;
        $ad->normalized_title = PersianNormalizer::text($title);
        $ad->normalized_title_hash = hash('sha256', $ad->normalized_title);
        $ad->description = $description;
        $ad->normalized_description = PersianNormalizer::text($description);
        $ad->normalized_description_hash = hash('sha256', $ad->normalized_description);
        $ad->price = $spec['price'];
        $ad->full_name = trim($owner->first_name.' '.$owner->last_name);
        $ad->business_name = $spec['with_business'] ? $this->businessNameFor($owner) : null;
        $ad->country_id = $city->province?->country_id;
        $ad->province_id = $city->province_id;
        $ad->city_id = $city->id;
        $ad->address = $spec['with_address'] ? sprintf('خیابان نمونه، کوچه %d، %s', ($spec['views_count'] % 90) + 1, $city->name) : null;
        $ad->mobile_1 = $owner->mobile;
        $ad->show_mobile_1 = ! $spec['hide_mobile'];
        $ad->email = $spec['with_email'] ? $owner->email : null;
        $ad->keywords = $spec['with_keywords']
            ? [$leaf->title, $city->name, 'خرید', 'فروش']
            : null;
        $ad->category_id = $leaf->id;
        $ad->source = self::DEMO_SOURCE;
        $ad->submit_ip = '127.0.0.1';
        $ad->status = $status;
        $ad->published_at = $spec['published_at'];
        $ad->expires_at = $spec['expires_at'];
        $ad->sort_at = $spec['sort_at'];
        $ad->views_count = (int) $spec['views_count'];
        $ad->is_featured = (bool) $spec['is_featured'];
        $ad->is_urgent = (bool) $spec['is_urgent'];
        $ad->is_colored = (bool) $spec['is_colored'];
        $ad->auto_ladder = (bool) $spec['auto_ladder'];
        $ad->save();
    }

    /**
     * Maps a category to one of the coarse domains used by the pools,
     * by looking at category titles of the leaf and its ancestors.
     */
    private function groupForCategory(Category $leaf): string
    {
        $titles = [$leaf->title];
        $parent = $leaf->parent;
        while ($parent !== null) {
            $titles[] = $parent->title;
            $parent = $parent->parent;
        }
        $haystack = implode(' ', $titles);

        return match (true) {
            str_contains($haystack, 'املاک') => 'realestate',
            str_contains($haystack, 'نقلیه') || str_contains($haystack, 'خودرو') || str_contains($haystack, 'موتور') => 'vehicle',
            str_contains($haystack, 'دیجیتال') || str_contains($haystack, 'موبایل') || str_contains($haystack, 'لپ') || str_contains($haystack, 'کامپیوتر') || str_contains($haystack, 'کنسول') => 'digital',
            str_contains($haystack, 'استخدام') || str_contains($haystack, 'کسب‌وکار') => 'job',
            str_contains($haystack, 'خدمات') || str_contains($haystack, 'آرایشگاه') || str_contains($haystack, 'پزشکی') || str_contains($haystack, 'آموزش') => 'service',
            default => 'generic',
        };
    }

    /** ~25% of ads have no price => «توافقی». */
    private function priceFor(string $group, int $seed): ?int
    {
        if ($seed % 4 === 0) {
            return null;
        }

        [$min, $max] = self::PRICE_RANGES[$group] ?? self::PRICE_RANGES['generic'];
        $span = $max - $min;

        return $min + ($seed * 7919) % $span;
    }

    private function buildDescription(string $title, string $cityName, bool $long): string
    {
        $base = sprintf('%s در %s موجود است. وضعیت کالا/خدمت کاملاً سالم و آماده تحویل می‌باشد.', $title, $cityName);

        if (! $long) {
            return $base.' برای هماهنگی و دریافت اطلاعات بیشتر با شماره اعلام‌شده تماس بگیرید.';
        }

        return $base."\n"
            .'امکان مشاهده حضوری و تست در محل وجود دارد و تحویل همان روز انجام می‌شود.'
            ."\n".'در صورت نیاز به مشاوره رایگان، پیش از تماس، ساعت کاری را در نظر بگیرید: شنبه تا پنجشنبه ۹ تا ۱۹.'
            ."\n".'قیمت اعلام‌شده نهایی است و برای خریدهای عمده تخفیف ویژه در نظر گرفته می‌شود.';
    }

    private function businessNameFor(User $owner): string
    {
        foreach (self::DEMO_USERS as $person) {
            if ($person['first_name'] === $owner->first_name && $person['last_name'] === $owner->last_name) {
                return $person['gender_label'];
            }
        }

        return 'کسب‌وکار نمونه دمو';
    }

    /** Deterministic pseudo-random pick (keeps tests stable, content varied). */
    private function pick(array $pool, int $seed): string
    {
        return $pool[abs($seed) % count($pool)];
    }
}
