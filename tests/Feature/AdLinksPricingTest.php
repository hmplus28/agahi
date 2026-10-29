<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\AdLink;
use App\Models\Category;
use App\Models\City;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Links posted with an ad are persisted and count toward the per-link
 * surcharge in displayPrice() (admin-configurable pricing.* settings).
 * Empty link slots must be dropped so they never add a fee.
 */
class AdLinksPricingTest extends TestCase
{
    use RefreshDatabase;

    private function adContext(): array
    {
        $country = \App\Models\Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = \App\Models\Province::query()->firstOrCreate(['slug' => 'tehran'], ['country_id' => $country->id, 'name' => 'تهران']);
        $city = City::query()->firstOrCreate(['slug' => 'tehran'], ['province_id' => $province->id, 'name' => 'تهران', 'is_active' => true]);
        $category = Category::query()->firstOrCreate(['slug' => 'test-cat'], ['title' => 'تست', 'is_active' => true]);

        return [$city, $category];
    }

    public function test_guest_submission_persists_filled_links_and_drops_empty_slots(): void
    {
        [$city, $category] = $this->adContext();

        $payload = [
            'title'       => 'آگهی با لینک',
            'description' => 'توضیحات آزمایشی برای بررسی ذخیرهٔ لینک‌ها.',
            'category_id' => $category->id,
            'city_id'     => $city->id,
            'price'       => 100000,
            'mobile_1'    => '09351112233',
            'links'       => [
                ['type' => 'site', 'url' => 'https://example.com'],
                ['type' => 'telegram', 'url' => ''],
                ['type' => 'instagram', 'url' => 'https://instagram.com/example'],
            ],
        ];

        $this->post(route('guest.ad.store'), $payload)->assertRedirect(route('home'));

        $ad = Ad::query()->where('title', 'آگهی با لینک')->firstOrFail();
        $this->assertSame(2, $ad->allLinks()->count());
        $this->assertSame(2, $ad->links()->count());
        $this->assertSame('https://example.com', $ad->links->first()->url);
    }

    public function test_display_price_adds_configurable_per_link_surcharge(): void
    {
        [$city, $category] = $this->adContext();

        SiteSetting::put('pricing.price_per_link', 10);
        SiteSetting::put('pricing.price_per_extra_image', 20);

        $ad = Ad::query()->create([
            'title'       => 'آگهی هزینه لینک',
            'slug'        => 'ad-link-fee-test',
            'description' => 'توضیحات آزمایشی.',
            'normalized_title'           => 'آگهی هزینه لینک',
            'normalized_description'     => 'توضیحات آزمایشی.',
            'normalized_title_hash'      => hash('sha256', 'آگهی هزینه لینک'),
            'normalized_description_hash'=> hash('sha256', 'توضیحات آزمایشی.'),
            'price'       => 100000,
            'category_id' => $category->id,
            'city_id'     => $city->id,
            'mobile_1'    => '09351112233',
            'status'      => \App\Domains\Ads\Enums\AdStatus::Active,
            'published_at'=> now(),
            'source'      => 'guest',
        ]);

        $ad->allLinks()->createMany([
            ['type' => 'site', 'url' => 'https://a.example', 'sort_order' => 0, 'is_active' => true],
            ['type' => 'site', 'url' => 'https://b.example', 'sort_order' => 1, 'is_active' => true],
            ['type' => 'site', 'url' => 'https://c.example', 'sort_order' => 2, 'is_active' => false],
        ]);

        // 3 links total, only 2 active -> 2 × 10 = 20; no images -> no image fee.
        $this->assertSame(100020, $ad->displayPrice());

        // The card grid path uses withCount('links') and must not lazy-load.
        $listed = Ad::query()->publiclyVisible()->withCount('links')->find($ad->id);
        $this->assertSame(100020, $listed->displayPrice());
    }
}
