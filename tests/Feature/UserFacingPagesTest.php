<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFacingPagesTest extends TestCase
{
    use RefreshDatabase;

    private function seedGeo(): array
    {
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        return compact('country', 'province', 'city', 'category');
    }

    public function test_homepage_renders_with_search(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('home-hero', false)
            ->assertSee('home-search-section', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="min_price"', false)
            ->assertSee('name="max_price"', false)
            ->assertSee('جست‌وجو', false);
    }

    public function test_homepage_has_header_with_brand_phone_city_dropdown(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('site-header', false)
            ->assertSee('header-top', false)
            ->assertSee('header-bottom', false)
            ->assertSee('brand-mark', false)
            ->assertSee('header-phone', false)
            ->assertSee('city-picker', false)
            ->assertSee('header-dropdown', false)
            ->assertSee('۰۲۱-۰۰۰۰۰۰۰۰', false);
    }

    public function test_homepage_has_post_ad_button_for_guests(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('post-ad-button', false)
            ->assertSee('login-button', false)
            ->assertSee('ثبت آگهی', false)
            ->assertSee('ورود', false)
            ->assertSee(route('guest.ad.create'), false)
            ->assertSee(route('login'), false);
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ورود', false)
            ->assertSee('password/forgot', false);
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('ثبت‌نام', false)
            ->assertSee('name="mobile"', false);
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('بازیابی رمز', false)
            ->assertSee('پیامک', false)
            ->assertSee('name="mobile"', false);
    }

    public function test_guest_ad_create_renders(): void
    {
        $this->get(route('guest.ad.create'))
            ->assertOk()
            ->assertSee('ثبت آگهی', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('name="city_id"', false)
            ->assertSee('name="mobile_1"', false)
            ->assertSee('name="hamrah_1"', false)
            ->assertSee('name="sobit_1"', false);
    }

    public function test_contact_page_renders(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('تماس با ما', false);
    }

    public function test_about_page_renders(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('درباره ما', false);
    }

    public function test_terms_page_renders(): void
    {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('قوانین', false);
    }

    public function test_search_page_renders(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('جست‌وجو', false);
    }

    public function test_user_create_ad_renders(): void
    {
        $data = $this->seedGeo();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('user.ads.create'))
            ->assertOk()
            ->assertSee('ثبت آگهی', false)
            ->assertSee('name="title"', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('name="keywords_json"', false)
            ->assertSee('name="hamrah_1"', false)
            ->assertSee('name="sobit_1"', false);
    }

    public function test_user_dashboard_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('user.dashboard'))
            ->assertOk();
    }

    public function test_user_can_submit_ad(): void
    {
        $data = $this->seedGeo();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession(['_token' => 'test'])->post(route('user.ads.store'), [
            '_token' => 'test',
            'title' => 'تست آگهی',
            'description' => 'توضیحات تستی برای آگهی با طول کافی.',
            'category_id' => $data['category']->id,
            'city_id' => $data['city']->id,
            'mobile_1' => '۰۹۱۲۳۴۵۶۷۸۹',
            'price' => 0,
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'تست آگهی',
            'status' => 'pending_approval',
        ]);
    }

    public function test_guest_can_submit_ad(): void
    {
        $data = $this->seedGeo();

        $response = $this->post(route('guest.ad.create'), [
            'title' => 'آگهی مهمان',
            'description' => 'توضیحات آگهی مهمان با طول مناسب و کافی.',
            'category_id' => $data['category']->id,
            'city_id' => $data['city']->id,
            'mobile_1' => '۰۹۱۲۱۱۱۲۲۳۳',
            'price' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', [
            'title' => 'آگهی مهمان',
            'status' => 'pending_approval',
        ]);
    }

    public function test_header_dropdown_has_about_and_contact_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('about'), false)
            ->assertSee(route('contact'), false)
            ->assertSee('درباره ما', false)
            ->assertSee('تماس با ما', false);
    }

    public function test_footer_has_required_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('site-footer', false)
            ->assertSee('search', false)
            ->assertSee('terms', false)
            ->assertSee('about', false)
            ->assertSee('contact', false);
    }

    public function test_expired_ad_shows_expired_banner(): void
    {
        $data = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $user->ads()->create([
            'title' => 'آگهی منقضی',
            'slug' => 'agahi-monaghezi',
            'description' => 'توضیحات آگهی منقضی شده.',
            'category_id' => $data['category']->id,
            'city_id' => $data['city']->id,
            'status' => 'active',
            'published_at' => now()->subDays(60),
            'expires_at' => now()->subDays(30),
            'price' => 0,
            'mobile_1' => '09123456789',
            'normalized_title' => 'test',
            'normalized_description' => 'test',
            'normalized_title_hash' => md5('test'),
            'normalized_description_hash' => md5('test'),
        ]);

        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertSee('expired-banner', false);
    }
}
