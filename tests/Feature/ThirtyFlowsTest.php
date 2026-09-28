<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Province;
use App\Models\SiteSetting;
use App\Models\Tariff;
use App\Models\User;
use App\Domains\Accounts\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ThirtyFlowsTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;



    private function seedGeo(): array
    {
        $country = Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = Province::query()->firstOrCreate(['slug' => 'tehran', 'country_id' => $country->id], ['name' => 'تهران']);
        $city = City::query()->firstOrCreate(['slug' => 'tehran', 'province_id' => $province->id], ['name' => 'تهران']);
        $category = Category::query()->firstOrCreate(['slug' => 'services'], ['title' => 'خدمات']);

        return compact('country', 'province', 'city', 'category');
    }

    private function uniqueTitle(string $base): string
    {
        return $base.' '.(++self::$seq);
    }

    private function makeAd(User $owner, array $overrides = []): Ad
    {
        $geo = $this->seedGeo();
        $title = $overrides['title'] ?? $this->uniqueTitle('آگهی تستی');

        return Ad::query()->create(array_merge([
            'code'                       => 'FLW'.(++self::$seq).random_int(100, 999),
            'slug'                       => 'flow-ad-'.(++self::$seq).'-'.random_int(100, 999),
            'user_id'                    => $owner->id,
            'title'                      => $title,
            'normalized_title'           => $title,
            'normalized_title_hash'      => hash('sha256', $title),
            'description'                => 'توضیحات تستی برای فلوی تست.',
            'normalized_description'     => 'توضیحات تستی برای فلوی تست.',
            'normalized_description_hash' => hash('sha256', 'توضیحات تستی برای فلوی تست.'),
            'mobile_1'                   => $owner->mobile,
            'category_id'                => $geo['category']->id,
            'city_id'                    => $geo['city']->id,
            'status'                     => AdStatus::Active,
            'published_at'               => now(),
            'expires_at'                 => now()->addDays(30),
            'price'                      => 1000,
        ], $overrides));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_staff' => true, 'role' => UserRole::SuperAdmin]);
    }

    private function makePayment(User $user, string $status, string $authority): Payment
    {
        $ad = Ad::query()->where('user_id', $user->id)->first() ?? $this->makeAd($user);
        $invoice = Invoice::query()->create([



            'invoice_number' => 'INV-'.(++self::$seq).'-'.random_int(1000, 9999),
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


            'reference_id' => 'REF-'.$authority,
        ]);
    }



    public function test_flow_01_homepage_renders_search_and_post_ad(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-hero', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="min_price"', false)
            ->assertSee('ثبت آگهی', false);
    }

    public function test_flow_02_search_keyword_matches_and_excludes(): void
    {
        $user = User::factory()->create();
        $this->makeAd($user, ['title' => 'تعمیر کولر گازی']);
        $this->makeAd($user, ['title' => 'اجاره سالن اجتماعات']);

        $this->get(route('search', ['q' => 'کولر']))
            ->assertOk()
            ->assertSee('تعمیر کولر گازی', false)
            ->assertDontSee('اجاره سالن اجتماعات', false);
    }

    public function test_flow_03_search_filters_by_city(): void
    {
        $user = User::factory()->create();
        $geo = $this->seedGeo();
        $this->makeAd($user, ['title' => 'دفتر کاری شیراز']);
        $otherCountry = Country::query()->create(['name' => 'ایران۲', 'slug' => 'iran-2']);
        $otherProvince = Province::query()->create(['country_id' => $otherCountry->id, 'name' => 'فارس', 'slug' => 'fars']);
        $shiraz = City::query()->create(['province_id' => $otherProvince->id, 'name' => 'شیراز', 'slug' => 'shiraz']);
        $inShiraz = $this->makeAd($user, ['title' => 'مغازه در شیراز', 'city_id' => $shiraz->id]);

        $this->get(route('search', ['city' => $shiraz->id]))
            ->assertOk()
            ->assertSee('مغازه در شیراز', false)
            ->assertDontSee('دفتر کاری شیراز', false);
    }

    public function test_flow_04_search_filters_by_price_range(): void
    {
        $user = User::factory()->create();
        $cheap = $this->makeAd($user, ['title' => 'گوشی ارزان', 'price' => 500]);
        $dear = $this->makeAd($user, ['title' => 'لپتاپ گران', 'price' => 50000]);

        $this->get(route('search', ['min_price' => 1000, 'max_price' => 100000]))
            ->assertOk()
            ->assertSee('لپتاپ گران', false)
            ->assertDontSee('گوشی ارزان', false);
    }

    public function test_flow_05_category_page_lists_its_ads(): void
    {
        $user = User::factory()->create();
        $geo = $this->seedGeo();
        $this->makeAd($user, ['category_id' => $geo['category']->id, 'title' => 'آگهی خدماتی']);

        $this->get(route('categories.show', $geo['category']))
            ->assertOk()
            ->assertSee('آگهی خدماتی', false);
    }

    public function test_flow_06_ad_detail_renders_for_guest(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی عمومی']);

        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertSee('آگهی عمومی', false)
            ->assertSee('breadcrumb', false);
    }

    public function test_flow_07_featured_ad_pinned_first_with_special_style(): void
    {
        $user = User::factory()->create();
        $featured = $this->makeAd($user, ['title' => 'آگهی ویژه', 'is_featured' => true, 'sort_at' => now()->subDay()]);
        $plain = $this->makeAd($user, ['title' => 'آگهی عادی', 'sort_at' => now()]);

        $html = view('components.ad-card', ['ad' => $featured])->render();
        $this->assertStringContainsString('ad-card--featured', $html);
        $this->assertStringContainsString('badge-featured', $html);


        $ordered = Ad::query()->publiclyVisible()->orderedForListing()->pluck('id')->all();
        $this->assertSame($featured->id, $ordered[0], 'featured ad must be pinned first');
    }

    public function test_flow_08_urgent_ribbon_on_card_and_detail(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی فوری', 'is_urgent' => true]);

        $html = view('components.ad-card', ['ad' => $ad])->render();
        $this->assertStringContainsString('ad-card__urgent-ribbon', $html);
        $this->assertStringContainsString('فوری', $html);

        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertSee('ad-detail__urgent-ribbon', false);
    }

    public function test_flow_09_tag_page_lists_ads_with_keyword(): void
    {
        $user = User::factory()->create();
        $with = $this->makeAd($user, ['title' => 'آگهی برچسب‌دار']);
        $with->update(['keywords' => ['نقاشی', 'ساخت']]);
        $without = $this->makeAd($user, ['title' => 'آگهی بی‌برچسب']);
        $without->update(['keywords' => ['اجاره']]);

        $this->get(route('tag.show', ['keyword' => 'نقاشی']))
            ->assertOk()
            ->assertSee('آگهی برچسب‌دار', false)
            ->assertDontSee('آگهی بی‌برچسب', false);
    }

    public function test_flow_10_tag_page_normalizes_arabic_letters(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی کلید فارسی']);

        $ad->update(['keywords' => ['کلید']]);

        $this->get(route('tag.show', ['keyword' => rawurlencode('كليد')]))
            ->assertOk()
            ->assertSee('آگهی کلید فارسی', false);
    }



    public function test_flow_11_robots_txt_served(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent', false)
            ->assertSee('Sitemap', false);
    }

    public function test_flow_12_sitemap_index_links_ads_sitemap(): void
    {
        $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertSee('/sitemaps/ads-1.xml', false)
            ->assertSee(route('home'), false);
    }

    public function test_flow_13_ads_sitemap_lists_only_public_ads(): void
    {
        $user = User::factory()->create();
        $active = $this->makeAd($user, ['title' => 'آگهی فعال سایت‌مپ']);
        $this->makeAd($user, ['title' => 'پیش‌نویس سایت‌مپ', 'status' => AdStatus::Draft]);

        $this->get(route('sitemap.ads', ['page' => 1]))
            ->assertOk()
            ->assertSee($active->publicUrl(), false)
            ->assertDontSee('پیش‌نویس', false);
    }

    public function test_flow_14_unknown_url_redirects_to_home(): void
    {
        $this->get('/this/page/does/not/exist')->assertRedirect(route('home'));
    }

    public function test_flow_15_static_pages_all_render(): void
    {
        foreach (['about', 'contact', 'terms', 'site-ads', 'site-ads.pricing', 'site-ads.rules'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }



    public function test_flow_16_guest_sees_guest_form_but_user_form_requires_login(): void
    {
        $this->get(route('guest.ad.create'))->assertOk();
        $this->get(route('user.ads.create'))->assertRedirect(route('login'));
    }

    public function test_flow_17_user_creates_ad_and_it_lands_in_pending(): void
    {
        $user = User::factory()->create();
        $geo = $this->seedGeo();

        $response = $this->actingAs($user)->post(route('user.ads.store'), [
            'title'       => 'آگهی جدید کاربر',
            'description' => 'توضیحات کامل آگهی جدید کاربر برای تست فلوی ثبت.',
            'price'       => 2500,
            'category_id' => $geo['category']->id,
            'city_id'     => $geo['city']->id,
            'mobile_1'    => $user->mobile,
            'full_name'   => 'کاربر تست',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $ad = Ad::query()->where('user_id', $user->id)->where('title', 'آگهی جدید کاربر')->first();
        $this->assertNotNull($ad, 'ad must be created');
        $this->assertSame(AdStatus::PendingApproval, $ad->status);
    }

    public function test_flow_18_each_link_adds_configured_price(): void
    {
        $user = User::factory()->create();
        SiteSetting::put('pricing.price_per_link', 10);
        SiteSetting::put('pricing.apply_pricing_to_display', true);
        $ad = $this->makeAd($user, ['price' => 1000]);
        $ad->allLinks()->create(['type' => 'website', 'url' => 'https://example.com', 'is_active' => true, 'sort_order' => 0]);
        $ad->refresh();


        $this->assertSame(1010, $ad->displayPrice());
    }

    public function test_flow_19_extra_images_add_configured_coefficient(): void
    {
        $user = User::factory()->create();
        SiteSetting::put('pricing.price_per_extra_image', 20);
        SiteSetting::put('pricing.apply_pricing_to_display', true);
        $ad = $this->makeAd($user, ['price' => 1000]);
        $ad->images()->createMany([
            ['image_thumb_path' => 'a_t.jpg', 'image_display_path' => 'a.jpg', 'is_primary' => true, 'width' => 100, 'height' => 100, 'sort_order' => 0],
            ['image_thumb_path' => 'b_t.jpg', 'image_display_path' => 'b.jpg', 'is_primary' => false, 'width' => 100, 'height' => 100, 'sort_order' => 1],
            ['image_thumb_path' => 'c_t.jpg', 'image_display_path' => 'c.jpg', 'is_primary' => false, 'width' => 100, 'height' => 100, 'sort_order' => 2],
        ]);
        $ad->refresh();


        $this->assertSame(1040, $ad->displayPrice());
    }

    public function test_flow_20_user_payments_page_shows_only_successful(): void
    {
        $user = User::factory()->create();
        $this->makePayment($user, 'successful', 'FLOW-SUCCESS-1');
        $this->makePayment($user, 'failed', 'FLOW-FAILED-1');

        $this->actingAs($user)->get(route('user.payments.index'))
            ->assertOk()
            ->assertSee('FLOW-SUCCESS-1', false)
            ->assertDontSee('FLOW-FAILED-1', false);
    }

    public function test_flow_21_user_tickets_page_lists_only_own_tickets(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $mine = \App\Models\Ticket::query()->create(['user_id' => $user->id, 'subject' => 'تیکت مال خودم', 'priority' => 'normal']);
        \App\Models\Ticket::query()->create(['user_id' => $other->id, 'subject' => 'تیکت دیگران', 'priority' => 'normal']);

        $this->actingAs($user)->get(route('user.tickets.index'))
            ->assertOk()
            ->assertSee('تیکت مال خودم', false)
            ->assertDontSee('تیکت دیگران', false);
    }



    public function test_flow_22_expired_banner_owner_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی منقضی بنردار', 'expires_at' => now()->subDay()]);


        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertDontSee('این آگهی منقضی شده است');


        $this->actingAs($other)->get($ad->publicUrl())
            ->assertOk()
            ->assertDontSee('این آگهی منقضی شده است');


        $this->actingAs($user)->get($ad->publicUrl())
            ->assertOk()
            ->assertSee('expired-banner', false)
            ->assertSee(route('user.expired-ads.index'), false);
    }

    public function test_flow_23_expired_ads_page_and_bulk_renew(): void
    {
        $user = User::factory()->create();
        $ad1 = $this->makeAd($user, ['title' => 'منقضی یک', 'status' => AdStatus::Expired, 'expires_at' => now()->subDay()]);
        $ad2 = $this->makeAd($user, ['title' => 'منقضی دو', 'status' => AdStatus::Expired, 'expires_at' => now()->subDays(2)]);

        $tariff = Tariff::query()->create([
            'code' => 'RENEW-FLOW', 'title' => 'تمدید', 'price' => 3000,
            'service_type' => 'renewal', 'duration_days' => 30, 'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('user.expired-ads.index'))
            ->assertOk()
            ->assertSee('منقضی یک', false)
            ->assertSee('منقضی دو', false);

        $this->actingAs($user)
            ->post(route('user.expired-ads.renewAll'), ['ad_ids' => [$ad1->id, $ad2->id], 'tariff_id' => $tariff->id])
            ->assertRedirect();

        $invoice = Invoice::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertSame(2, $invoice->items->count());
        $this->assertSame(6000, $invoice->total); 
    }

    public function test_flow_24_expiry_sms_contains_direct_expired_ads_link(): void
    {
        $sent = [];
        $this->mock(\App\Domains\Notifications\Contracts\SmsProvider::class, function ($mock) use (&$sent) {
            $mock->shouldReceive('send')->andReturnUsing(function (string $mobile, string $message) use (&$sent) {
                $sent[] = [$mobile, $message];
                return ['provider_id' => 'mock', 'response' => 'ok'];
            });
        });

        $user = User::factory()->create();
        $this->makeAd($user, ['title' => 'منقضی پیامکی', 'status' => AdStatus::Expired, 'expires_at' => now()->subDay()]);

        \Illuminate\Support\Facades\Artisan::call('ads:send-expiry-notifications');

        $this->assertNotEmpty($sent, 'expiry SMS must be sent');
        [$mobile, $message] = $sent[0];
        $this->assertSame($user->mobile, $mobile);
        $this->assertStringContainsString(route('user.expired-ads.index'), $message, 'SMS must link directly to the expired-ads page');
    }

    public function test_flow_25_daily_cron_bumps_purchased_ladder_ads(): void
    {
        $user = User::factory()->create();
        $old = now()->subDays(2);

        $ladderAd = $this->makeAd($user, ['title' => 'نردبان خریداری‌شده']);
        $ladderAd->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();
        $tariff = Tariff::query()->create([
            'code' => 'LADDER-FLOW', 'title' => 'نردبان', 'price' => 10000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => true,
        ]);
        \App\Models\AdService::query()->create([
            'ad_id' => $ladderAd->id, 'tariff_id' => $tariff->id,
            'starts_at' => now(), 'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        $plainAd = $this->makeAd($user, ['title' => 'بدون نردبان']);
        $plainAd->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();

        \Illuminate\Support\Facades\Artisan::call('ads:process-auto-ladders');

        $ladderAd->refresh();
        $plainAd->refresh();
        $this->assertTrue($ladderAd->last_ladder_at->gt($old), 'purchased ladder must be bumped daily');
        $this->assertFalse($plainAd->last_ladder_at->gt($old), 'plain ad must not be bumped');
    }



    public function test_flow_26_admin_area_requires_staff(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $plain = User::factory()->create();
        $this->actingAs($plain)->get(route('admin.dashboard'))->assertForbidden();

        $this->actingAs($this->makeAdmin())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_flow_27_admin_ad_list_has_edit_button_and_edit_works(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی قابل ویرایش']);
        $admin = $this->makeAdmin();


        $this->actingAs($admin)->get(route('admin.ads.index'))
            ->assertOk()
            ->assertSee(route('admin.ads.edit', $ad), false);


        $this->actingAs($admin)
            ->patch(route('admin.ads.edit', $ad), [
                'title'         => 'آگهی ویرایش‌شده',
                'description'   => 'توضیحات ویرایش‌شده',
                'price'         => 7777,
                'category_id'   => $ad->category_id,
                'city_id'       => $ad->city_id,
                'is_featured'   => '1',
                'is_urgent'     => '0',
                'is_colored'    => '0',
                'auto_ladder'   => '0',
                'show_mobile_1' => '1',
            ])
            ->assertRedirect();

        $ad->refresh();
        $this->assertSame(7777, $ad->price);
        $this->assertTrue($ad->is_featured);
    }

    public function test_flow_28_admin_status_transition_and_filter(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی در انتظار', 'status' => AdStatus::PendingApproval]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.ads.transition', $ad), ['status' => AdStatus::Active->value])
            ->assertRedirect();
        $ad->refresh();
        $this->assertSame(AdStatus::Active, $ad->status);


        $this->actingAs($admin)->get(route('admin.ads.index', ['status' => AdStatus::Active->value]))
            ->assertOk()
            ->assertSee('آگهی در انتظار', false);
    }

    public function test_flow_29_admin_permits_show_images_and_settings_pricing_works(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'آگهی نیازمند مجوز', 'status' => AdStatus::NeedsPermit]);
        \App\Models\AdPermit::query()->create([
            'ad_id' => $ad->id,
            'permit_number' => 'P-'.random_int(10000, 99999),
            'image_path' => 'permits/test.jpg',
            'status' => 'pending',
        ]);

        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get(route('admin.permits.index'))
            ->assertOk()
            ->assertSee('permits/test.jpg', false);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'price_per_link'           => 15,
                'price_per_extra_image'    => 30,
                'apply_pricing_to_display' => '1',
                'support_phone'            => '021-1111111',
                'support_phone_href'       => '0211111111',
                'brand_name'               => 'آگهی',
                'site_description'         => 'تست',
            ])
            ->assertRedirect();

        $this->assertSame(15, SiteSetting::get('pricing.price_per_link'));
        $this->assertSame(30, SiteSetting::get('pricing.price_per_extra_image'));
    }

    public function test_flow_30_admin_payments_default_successful_only(): void
    {
        $user = User::factory()->create();
        $this->makePayment($user, 'successful', 'ADMIN-SUCCESS-1');
        $this->makePayment($user, 'failed', 'ADMIN-FAILED-1');
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('REF-ADMIN-SUCCESS-1', false)
            ->assertDontSee('REF-ADMIN-FAILED-1', false); 


        $this->actingAs($admin)->get(route('admin.payments.index', ['status' => 'all']))
            ->assertOk()
            ->assertSee('REF-ADMIN-SUCCESS-1', false)
            ->assertSee('REF-ADMIN-FAILED-1', false);
    }

    public function test_flow_31_admin_manual_ladder_refresh_button(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['title' => 'نردبان دستی']);
        $old = now()->subDays(2);
        $ad->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();
        $tariff = Tariff::query()->create([
            'code' => 'LADDER-MANUAL', 'title' => 'نردبان', 'price' => 10000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => true,
        ]);
        \App\Models\AdService::query()->create([
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id,
            'starts_at' => now(), 'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.ads.ladder.refresh'))
            ->assertRedirect();

        $ad->refresh();
        $this->assertTrue($ad->last_ladder_at->gt($old), 'manual refresh must bump ladder without cron');
    }

    public function test_flow_32_profile_routes_are_gone(): void
    {
        $this->assertFalse(Route::has('user.profile.edit'));
        $this->assertFalse(Route::has('user.profile.update'));
        $this->get('/user/profile')->assertRedirect(route('home'));
    }

    public function test_flow_33_sticky_admin_toolbar_css_is_shipped(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('.admin-toolbar { position: sticky', $css);
        $this->assertStringContainsString('z-index: 50', $css);
    }

    public function test_flow_34_deploy_script_builds_frontend_assets(): void
    {
        $deploy = file_get_contents(base_path('deploy.sh'));
        $this->assertMatchesRegularExpression('/npm\s+ci|npm\s+install/', $deploy, 'deploy.sh must install npm dependencies');
        $this->assertStringContainsString('npm run build', $deploy, 'deploy.sh must build Vite assets');
    }
}
