<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Accounts\Enums\UserRole;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\PendingAd;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 22 - frontend polish regression tests:
 *  - single styled pagination bar (no nested wrappers, Persian digits)
 *  - prices rendered in Persian digits everywhere on cards
 *  - home hero/banner no longer wider than the ad grid (CSS moved out of the view)
 *  - admin "failed submissions" page (previously orphan) renders WITH the toolbar
 */
class FrontendPolishTest extends TestCase
{
    use RefreshDatabase;

    private function createAd(array $overrides = []): Ad
    {
        $country = Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = Province::query()->firstOrCreate(['slug' => 'tehran'], ['country_id' => $country->id, 'name' => 'تهران']);
        $city = City::query()->firstOrCreate(['slug' => 'tehran'], ['province_id' => $province->id, 'name' => 'تهران', 'is_active' => true]);
        $category = Category::query()->firstOrCreate(['slug' => 'test-cat'], ['title' => 'تست', 'is_active' => true]);
        $user = User::factory()->create();

        $title = 'آگهی آزمایشی پولیش '.$overrides['slug_suffix'];

        return Ad::query()->create(array_merge([
            'slug' => 'polish-test-'.$overrides['slug_suffix'],
            'user_id' => $user->id,
            'title' => $title,
            'normalized_title' => $title,
            'normalized_title_hash' => hash('sha256', $title),
            'description' => 'توضیحات آزمایشی.',
            'normalized_description' => 'توضیحات آزمایشی.',
            'normalized_description_hash' => hash('sha256', 'توضیحات آزمایشی.'),
            'mobile_1' => $user->mobile,
            'price' => 1234567,
            'category_id' => $category->id,
            'country_id' => $country->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'source' => 'admin',
            'status' => AdStatus::Active,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ], $overrides));
    }

    public function test_home_pagination_is_a_single_styled_bar_with_persian_digits(): void
    {
        for ($i = 1; $i <= 105; $i++) {
            $this->createAd(['slug_suffix' => (string) $i]);
        }

        $html = (string) $this->get('/')->assertOk()->getContent();

        // Exactly ONE pagination nav (the old view nested two).
        $this->assertSame(1, substr_count($html, 'class="pagination-wrap"'), 'صفحه‌بندی نباید تو در تو باشد.');
        $this->assertStringContainsString('pg-btn pg-active', $html);
        $this->assertStringContainsString('قبلی', $html);
        $this->assertStringContainsString('بعدی', $html);
        // Latin page numbers must not leak into the pagination labels.
        $this->assertStringContainsString('صفحهٔ ۲', $html);
        $this->assertStringNotContainsString('صفحهٔ 2', $html);
        // Previous button is disabled on page 1.
        $this->assertStringContainsString('pg-disabled', $html);
    }

    public function test_ad_card_price_is_rendered_in_persian_digits(): void
    {
        $this->createAd(['slug_suffix' => '1', 'price' => 1234567]);

        $html = (string) $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('۱,۲۳۴,۵۶۷ ریال', $html, 'قیمت باید با ارقام فارسی نمایش داده شود.');
        $this->assertStringNotContainsString('1,234,567 ریال', $html);
    }

    public function test_admin_failed_submissions_page_renders_with_toolbar(): void
    {
        PendingAd::query()->create([
            'token' => 'token-abc-123',
            'ad_data' => ['title' => 'آگهی ناتمام مهمان', 'mobile_1' => '09351112233', 'city_id' => null],
            'status' => 'pending',
            'created_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);

        $staff = User::factory()->create(['role' => UserRole::Moderator, 'is_staff' => true]);

        $html = (string) $this->actingAs($staff)->get(route('admin.pending-ads.index'))->assertOk()->getContent();

        $this->assertStringContainsString('admin-toolbar', $html, 'کارت‌های مدیریت باید در این بخش هم نمایش داده شوند.');
        $this->assertStringContainsString('آگهی‌های ناموفق', $html);
        $this->assertStringContainsString('آگهی ناتمام مهمان', $html);
        $this->assertStringContainsString('09351112233', $html);

        // The toolbar links to this section from every admin page.
        $this->assertStringContainsString(route('admin.pending-ads.index'), $html);

        // Status filter keeps working.
        $this->actingAs($staff)
            ->get(route('admin.pending-ads.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('موردی برای نمایش وجود ندارد.');
    }
}
