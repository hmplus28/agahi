<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Enums\AdStatus;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    private function createOneRealAd(): void
    {
        // Base catalog (country/province/city/category) that must survive
        // every seeder run untouched.
        $country = \App\Models\Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = \App\Models\Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = \App\Models\City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran', 'is_active' => true]);
        $category = \App\Models\Category::query()->create(['title' => 'دسته واقعی', 'slug' => 'real-cat', 'is_active' => true]);
        $user = \App\Models\User::query()->create([
            'mobile' => '09121110000',
            'email' => 'real-user@real.test',
            'first_name' => 'کاربر',
            'last_name' => 'واقعی',
            'password' => Hash::make('RealPass123!'),
            'role' => \App\Domains\Accounts\Enums\UserRole::User,
            'is_active' => true,
        ]);

        $title = 'آگهی واقعی که نباید حذف شود';
        \App\Models\Ad::query()->create([
            'slug' => 'real-ad-keep-me',
            'user_id' => $user->id,
            'title' => $title,
            'normalized_title' => $title,
            'normalized_title_hash' => hash('sha256', $title),
            'description' => 'این آگهی در جریان سید دمو باید حفظ شود.',
            'normalized_description' => 'توضیحات آگهی واقعی',
            'normalized_description_hash' => hash('sha256', 'توضیحات آگهی واقعی'),
            'mobile_1' => $user->mobile,
            'category_id' => $category->id,
            'country_id' => $country->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'source' => 'admin',
            'status' => AdStatus::Active,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }

    private function runSeeder(int $activeCount = 120): void
    {
        $seeder = new DemoContentSeeder;
        $seeder->activeCount = $activeCount;
        $seeder->run();
    }

    public function test_seeder_creates_varied_paginatable_dataset_without_touching_real_data(): void
    {
        $this->createOneRealAd();
        $this->runSeeder();

        // 120 active + 28 queue extras (10 pending + 8 expired + 5 draft +
        // 3 needs_permit + 2 inactive) + the 1 real ad.
        $this->assertDatabaseCount('ads', 120 + 28 + 1);

        // Demo users exist on the reserved 0930 range with stored plaintext.
        $this->assertDatabaseHas('users', ['mobile' => '09300000001', 'plaintext_password' => DemoContentSeeder::DEMO_PASSWORD]);
        $this->assertDatabaseHas('users', ['mobile' => '09300000008']);

        // The real (non-demo) ad survived the run.
        $this->assertDatabaseHas('ads', ['slug' => 'real-ad-keep-me', 'source' => 'admin']);

        // Queue ads exist for the admin panel.
        $this->assertDatabaseHas('ads', ['source' => 'demo', 'status' => AdStatus::PendingApproval->value, 'slug' => 'demo-pending-approval-1']);
        $this->assertDatabaseHas('ads', ['source' => 'demo', 'status' => AdStatus::Expired->value, 'slug' => 'demo-expired-1']);

        // Variety: some featured/urgent, some توافقی (NULL price), some priced.
        $this->assertDatabaseHas('ads', ['source' => 'demo', 'is_featured' => true]);
        $this->assertDatabaseHas('ads', ['source' => 'demo', 'is_urgent' => true]);
        $this->assertDatabaseHas('ads', ['source' => 'demo', 'price' => null]);
        $this->assertTrue(
            \App\Models\Ad::query()->where('source', 'demo')->whereNotNull('keywords')->exists(),
            'برخی آگهی‌های دمو باید کلمات کلیدی داشته باشند.'
        );

        // Home page 1: exactly 100 cards + a link to page 2.
        $page1 = $this->get('/');
        $page1->assertOk();
        $this->assertSame(100, substr_count((string) $page1->getContent(), 'class="card-image"'), 'صفحه اول باید دقیقاً ۱۰۰ کارت آگهی داشته باشد.');
        $this->assertStringContainsString('page=2', (string) $page1->getContent());

        // Home page 2: remaining 120 - 100 = 20 demo cards + 1 real ad.
        $page2 = $this->get('/?page=2');
        $page2->assertOk();
        $this->assertSame(21, substr_count((string) $page2->getContent(), 'class="card-image"'), 'صفحه دوم باید ۲۱ کارت آگهی داشته باشد.');
    }

    public function test_seeder_is_idempotent_on_re_run(): void
    {
        $this->createOneRealAd();
        $this->runSeeder();
        $this->assertDatabaseCount('ads', 120 + 28 + 1);

        // Second run must replace demo rows 1:1, not duplicate them.
        $this->runSeeder();
        $this->assertDatabaseCount('ads', 120 + 28 + 1);
        $this->assertSame(0, \App\Models\Ad::query()->where('slug', 'demo-ad-1')->count() - 1);
        $this->assertDatabaseHas('ads', ['slug' => 'real-ad-keep-me', 'source' => 'admin']);
    }

    public function test_flush_removes_only_demo_rows(): void
    {
        $this->createOneRealAd();
        $this->runSeeder();
        $this->assertDatabaseCount('ads', 120 + 28 + 1);

        (new DemoContentSeeder)->flushDemoData();

        $this->assertDatabaseCount('ads', 1);
        $this->assertDatabaseHas('ads', ['slug' => 'real-ad-keep-me', 'source' => 'admin']);
        $this->assertDatabaseMissing('ads', ['slug' => 'demo-ad-1']);
    }
}
