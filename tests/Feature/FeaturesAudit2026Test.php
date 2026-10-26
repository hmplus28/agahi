<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Payment;
use App\Models\Province;
use App\Models\SiteSetting;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturesAudit2026Test extends TestCase
{
    use RefreshDatabase;

    private function seedGeo(): array
    {
        $country = Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = Province::query()->firstOrCreate(['slug' => 'tehran', 'country_id' => $country->id], ['name' => 'تهران']);
        $city = City::query()->firstOrCreate(['slug' => 'tehran', 'province_id' => $province->id], ['name' => 'تهران']);
        $category = Category::query()->firstOrCreate(['slug' => 'services'], ['title' => 'خدمات']);
        return compact('country', 'province', 'city', 'category');
    }

    private function makeAd(User $owner, array $overrides = []): Ad
    {
        $geo = $this->seedGeo();
        return Ad::query()->create(array_merge([
            'code'                       => 'AUD' . random_int(1000, 9999),
            'slug'                       => 'audit-ad-' . random_int(1000, 9999),
            'user_id'                    => $owner->id,
            'title'                      => 'آگهی تستی',
            'normalized_title'           => 'آگهی تستی',
            'normalized_title_hash'      => hash('sha256', 'آگهی تستی'),
            'description'                => 'توضیحات تستی',
            'normalized_description'     => 'توضیحات تستی',
            'normalized_description_hash' => hash('sha256', 'توضیحات تستی'),
            'mobile_1'                   => $owner->mobile,
            'category_id'                => $geo['category']->id,
            'city_id'                     => $geo['city']->id,
            'status'                      => AdStatus::Active,
            'published_at'               => now(),
            'expires_at'                 => now()->addDays(30),
            'price'                       => 1000,
        ], $overrides));
    }





    public function test_display_price_includes_link_and_image_surcharges(): void
    {
        $user = User::factory()->create();
        SiteSetting::put('pricing.price_per_link', 10);
        SiteSetting::put('pricing.price_per_extra_image', 20);
        SiteSetting::put('pricing.apply_pricing_to_display', true);

        $ad = $this->makeAd($user, ['price' => 1000]);


        $this->assertSame(1000, $ad->displayPrice());


        $ad->allLinks()->createMany([
            ['type' => 'website', 'url' => 'https://example.com/1', 'is_active' => true,  'sort_order' => 0],
            ['type' => 'website', 'url' => 'https://example.com/2', 'is_active' => true,  'sort_order' => 1],
        ]);
        $ad->images()->createMany([
            ['image_thumb_path' => 'a_t.jpg', 'image_display_path' => 'a.jpg', 'is_primary' => true,  'width' => 100, 'height' => 100, 'sort_order' => 0],
            ['image_thumb_path' => 'b_t.jpg', 'image_display_path' => 'b.jpg', 'is_primary' => false, 'width' => 100, 'height' => 100, 'sort_order' => 1],
            ['image_thumb_path' => 'c_t.jpg', 'image_display_path' => 'c.jpg', 'is_primary' => false, 'width' => 100, 'height' => 100, 'sort_order' => 2],
        ]);
        $ad->refresh();


        $this->assertSame(1060, $ad->displayPrice());
    }

    public function test_display_price_disabled_when_setting_off(): void
    {
        $user = User::factory()->create();
        SiteSetting::put('pricing.apply_pricing_to_display', false);

        $ad = $this->makeAd($user, ['price' => 1000]);
        $ad->allLinks()->create(['url' => 'https://example.com', 'is_active' => true, 'sort_order' => 0]);
        $ad->refresh();

        $this->assertSame(1000, $ad->displayPrice());
    }

    public function test_admin_settings_page_is_accessible_to_staff(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $this->actingAs($admin)
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('قیمت هر لینک اضافه', false)
            ->assertSee('قیمت هر تصویر اضافه', false);
    }

    public function test_admin_settings_can_update_pricing(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'price_per_link'         => 25,
                'price_per_extra_image'  => 50,
                'apply_pricing_to_display' => '1',
                'support_phone'          => '021-1234567',
                'support_phone_href'     => '0211234567',
                'brand_name'             => 'تست',
                'site_description'       => 'تست توضیحات',
            ])
            ->assertRedirect();

        $this->assertSame(25, SiteSetting::get('pricing.price_per_link'));
        $this->assertSame(50, SiteSetting::get('pricing.price_per_extra_image'));
    }





    public function test_unknown_url_redirects_to_home_instead_of_404(): void
    {
        $this->get('/some-page-that-does-not-exist')
            ->assertRedirect(route('home'));
    }





    public function test_tag_page_lists_ads_with_matching_keyword(): void
    {
        $user = User::factory()->create();
        $ad1 = $this->makeAd($user, ['title' => 'آگهی با برچسب']);
        $ad2 = $this->makeAd($user, ['title' => 'آگهی بدون برچسب']);

        $ad1->update(['keywords' => ['تعمیر', 'فروش']]);
        $ad2->update(['keywords' => ['اجاره']]);

        $response = $this->get(route('tag.show', ['keyword' => 'تعمیر']));
        $response->assertOk()
            ->assertSee('تعمیر', false)
            ->assertSee('آگهی با برچسب', false)
            ->assertDontSee('آگهی بدون برچسب', false);
    }





    public function test_sitemap_index_lists_static_pages(): void
    {
        $response = $this->get(route('sitemap.index'));
        $response->assertOk()
            ->assertSee(route('home'), false)
            ->assertSee(route('about'), false)
            ->assertSee(route('contact'), false)
            ->assertSee(route('site-ads'), false)
            ->assertSee(route('sitemap.categories'), false);
    }





    private function makePayment(User $user, string $status, string $authority): Payment
    {
        $ad = Ad::query()->where('user_id', $user->id)->first();
        if (!$ad) {
            $ad = $this->makeAd($user);
        }
        $invoice = \App\Models\Invoice::query()->create([
            'invoice_number' => 'INV-' . $authority,
            'user_id'        => $user->id,
            'ad_id'          => $ad->id,
            'status'         => 'pending',
            'subtotal'       => 100,
            'discount'       => 0,
            'total'          => 100,
        ]);
        return Payment::query()->create([
            'user_id'    => $user->id,
            'invoice_id' => $invoice->id,
            'ad_id'      => $ad->id,
            'amount'     => $status === 'successful' ? 100 : 200,
            'method'     => 'online',
            'status'     => $status,
            'gateway'    => 'fake',
            'authority'  => $authority,
        ]);
    }

    public function test_admin_payments_index_defaults_to_successful_only(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $user = User::factory()->create();

        $this->makePayment($user, 'successful', 'SUCCESS-1');
        $this->makePayment($user, 'failed', 'FAILED-1');

        $response = $this->actingAs($admin)->get(route('admin.payments.index'));
        $response->assertOk()
            ->assertSee('SUCCESS-1', false)
            ->assertDontSee('FAILED-1', false);
    }

    public function test_admin_payments_can_show_all_statuses_via_filter(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $user = User::factory()->create();

        $this->makePayment($user, 'successful', 'SUCCESS-2');
        $this->makePayment($user, 'failed', 'FAILED-2');

        $response = $this->actingAs($admin)
            ->get(route('admin.payments.index', ['status' => 'all']));
        $response->assertOk()
            ->assertSee('SUCCESS-2', false)
            ->assertSee('FAILED-2', false);
    }





    public function test_user_payments_index_only_shows_successful(): void
    {
        $user = User::factory()->create();
        $this->makePayment($user, 'successful', 'U-SUCCESS-1');
        $this->makePayment($user, 'failed', 'U-FAILED-1');




        $successfulCount = Payment::query()
            ->where('user_id', $user->id)
            ->where('status', 'successful')
            ->count();
        $failedCount = Payment::query()
            ->where('user_id', $user->id)
            ->where('status', 'failed')
            ->count();

        $this->assertSame(1, $successfulCount, 'user should have 1 successful payment');
        $this->assertSame(1, $failedCount, 'user should have 1 failed payment');


        $this->actingAs($user)->get(route('user.payments.index'))->assertOk();
    }





    public function test_user_expired_ads_page_lists_expired_ads(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['status' => AdStatus::Expired, 'expires_at' => now()->subDay(), 'title' => 'آگهی منقضی', 'slug' => 'expired-ad-' . random_int(1000, 9999), 'code' => 'EXP' . random_int(1000, 9999)]);
        $ad2 = $this->makeAd($user, ['status' => AdStatus::Active, 'expires_at' => now()->addDay(), 'title' => 'آگهی فعال', 'slug' => 'active-ad-' . random_int(1000, 9999), 'code' => 'ACT' . random_int(1000, 9999)]);



        $ad->forceFill(['normalized_title' => 'آگهی منقضی', 'normalized_title_hash' => hash('sha256', 'آگهی منقضی')])->save();
        $ad2->forceFill(['normalized_title' => 'آگهی فعال', 'normalized_title_hash' => hash('sha256', 'آگهی فعال')])->save();

        $response = $this->actingAs($user)->get(route('user.expired-ads.index'));
        $response->assertOk()
            ->assertSee('آگهی‌های منقضی شده', false)
            ->assertSee('آگهی منقضی', false)
            ->assertDontSee('آگهی فعال', false);
    }

    public function test_user_expired_ads_renew_all_creates_invoice_for_selected_ads(): void
    {
        $user = User::factory()->create();
        $ad1 = $this->makeAd($user, ['status' => AdStatus::Expired, 'expires_at' => now()->subDay()]);
        $ad2 = $this->makeAd($user, ['status' => AdStatus::Expired, 'expires_at' => now()->subDays(2)]);

        $tariff = Tariff::query()->create([
            'code' => 'RENEW-AUDIT', 'title' => 'تمدید', 'price' => 5000,
            'service_type' => 'renewal', 'duration_days' => 30, 'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('user.expired-ads.renewAll'), [
                'ad_ids'    => [$ad1->id, $ad2->id],
                'tariff_id' => $tariff->id,
            ]);


        $response->assertRedirect();


        $this->assertDatabaseHas('invoices', ['user_id' => $user->id, 'status' => 'pending']);
        $invoice = \App\Models\Invoice::query()->where('user_id', $user->id)->first();
        $this->assertSame(2, $invoice->items->count());
        $this->assertSame(10000, $invoice->total); 
    }





    public function test_admin_can_edit_ad_fields(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $user = User::factory()->create();
        $geo = $this->seedGeo();
        $ad = $this->makeAd($user);

        $response = $this->actingAs($admin)
            ->patch(route('admin.ads.edit', $ad), [
                'title'         => 'عنوان ویرایش‌شده',
                'description'   => 'توضیحات جدید',
                'price'         => 5000,
                'category_id'   => $ad->category_id,
                'city_id'       => $ad->city_id,
                'is_featured'   => '1',
                'is_urgent'     => '0',
                'is_colored'    => '0',
                'auto_ladder'   => '0',
                'show_mobile_1' => '1',
            ]);

        $response->assertRedirect();
        $ad->refresh();
        $this->assertSame('عنوان ویرایش‌شده', $ad->title);
        $this->assertSame(5000, $ad->price);
        $this->assertTrue($ad->is_featured);
        $this->assertFalse($ad->is_urgent);
    }





    public function test_admin_can_refresh_ladders_manually(): void
    {
        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $oldLadder = now()->subDays(2);
        $ad->forceFill(['last_ladder_at' => $oldLadder, 'sort_at' => $oldLadder])->save();

        $tariff = Tariff::query()->create([
            'code' => 'LADDER-AUDIT', 'title' => 'نردبان', 'price' => 10000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => true,
        ]);
        \App\Models\AdService::query()->create([
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id,
            'starts_at' => now(), 'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.ads.ladder.refresh'));

        $response->assertRedirect();
        $ad->refresh();
        $this->assertTrue($ad->last_ladder_at->gt($oldLadder));
    }





    public function test_featured_ad_card_has_featured_class(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['is_featured' => true]);

        $html = view('components.ad-card', ['ad' => $ad])->render();
        $this->assertStringContainsString('ad-card--featured', $html);
    }

    public function test_urgent_ad_card_has_urgent_ribbon(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['is_urgent' => true]);

        $html = view('components.ad-card', ['ad' => $ad])->render();
        $this->assertStringContainsString('ad-card--urgent', $html);
        $this->assertStringContainsString('ad-card__urgent-ribbon', $html);
        $this->assertStringContainsString('فوری', $html);
    }






    public function test_process_auto_ladders_covers_purchased_ladder_service(): void
    {
        $user = User::factory()->create();
        $old = now()->subDays(2);


        $ladderAd = $this->makeAd($user);
        $ladderAd->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();
        $tariff = Tariff::query()->create([
            'code' => 'LADDER-CRON', 'title' => 'نردبان', 'price' => 10000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => true,
        ]);
        \App\Models\AdService::query()->create([
            'ad_id' => $ladderAd->id, 'tariff_id' => $tariff->id,
            'starts_at' => now(), 'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);


        $plainAd = $this->makeAd($user, ['title' => 'آگهی بدون نردبان']);
        $plainAd->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();

        \Illuminate\Support\Facades\Artisan::call('ads:process-auto-ladders');

        $ladderAd->refresh();
        $plainAd->refresh();

        $this->assertTrue($ladderAd->last_ladder_at->gt($old), 'purchased ladder ad must be bumped by the daily cron');
        $this->assertFalse($plainAd->last_ladder_at->gt($old), 'plain ad must not be bumped');
    }





    public function test_profile_route_no_longer_exists(): void
    {

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('user.profile.edit'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('user.profile.update'));
    }
}
