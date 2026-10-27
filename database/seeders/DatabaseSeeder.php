<?php

declare(strict_types=1);
namespace Database\Seeders;
use App\Domains\Accounts\Enums\UserRole;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Tariff;
use App\Models\User;
use App\Support\PersianNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // ads.code is a bigint column since 2026_09_08_000001 - codes MUST be
    // numeric (the model's creating hook asks AdCodeService for one, so no
    // explicit code is passed here). Idempotency is guaranteed by the slug.
    private const DEMO_SLUG = 'tamirat-lavazem-khanegi';

    public function run(): void
    {
        $iran = Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران', 'is_active' => true]);
        $tehran = Province::query()->firstOrCreate(['country_id' => $iran->id, 'slug' => 'tehran'], ['name' => 'تهران', 'is_active' => true]);
        $city = City::query()->firstOrCreate(['province_id' => $tehran->id, 'slug' => 'tehran'], ['name' => 'تهران', 'is_active' => true]);
        $services = Category::query()->firstOrCreate(['slug' => 'services'], ['title' => 'خدمات', 'is_active' => true]);
        $repair = Category::query()->firstOrCreate(['slug' => 'repair'], ['parent_id' => $services->id, 'title' => 'تعمیرات', 'is_active' => true]);
        $admin = User::query()->firstOrCreate(['mobile' => '09120000000'], ['email' => 'admin@example.test', 'first_name' => 'مدیر', 'last_name' => 'سامانه', 'password' => Hash::make('ChangeMe123!'), 'plaintext_password' => 'ChangeMe123!', 'role' => UserRole::SuperAdmin, 'is_active' => true, 'is_staff' => true]);
        Tariff::query()->firstOrCreate(['code' => 'FREE_30'], ['title' => 'آگهی رایگان ۳۰ روزه', 'price' => 0, 'service_type' => 'ad', 'duration_days' => 30, 'is_active' => true]);

        // Varied demo dataset (250 active ads + admin queue samples, all
        // tagged source='demo'). Re-runnable; never touches real rows.
        $this->call(DemoContentSeeder::class);

        if (Ad::query()->where('slug', self::DEMO_SLUG)->exists()) {
            return;
        }

        $title = 'تعمیرات تخصصی لوازم خانگی در تهران';
        $normalizedTitle = PersianNormalizer::text($title);
        $description = 'خدمات تعمیر و سرویس لوازم خانگی با هماهنگی تلفنی در شهر تهران.';
        $normalizedDescription = PersianNormalizer::text($description);

        Ad::query()->create([
            'slug' => self::DEMO_SLUG,
            'user_id' => $admin->id,
            'title' => $title,
            'normalized_title' => $normalizedTitle,
            'normalized_title_hash' => hash('sha256', $normalizedTitle),
            'description' => $description,
            'normalized_description' => $normalizedDescription,
            'normalized_description_hash' => hash('sha256', $normalizedDescription),
            'price' => null,
            'business_name' => 'خدمات نمونه',
            'country_id' => $iran->id,
            'province_id' => $tehran->id,
            'city_id' => $city->id,
            'mobile_1' => $admin->mobile,
            'category_id' => $repair->id,
            'source' => 'admin',
            'status' => AdStatus::Active,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
            'sort_at' => now(),
            'is_featured' => true,
        ]);
    }
}
