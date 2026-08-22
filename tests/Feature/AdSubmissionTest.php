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

class AdSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_an_ad_for_moderation(): void
    {
        $user = User::factory()->create();
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('user.ads.store'), [
            '_token' => 'test-token',
            'title' => 'تعمیر کولر گازی',
            'description' => 'خدمات تعمیر کولر گازی در تمام مناطق تهران با هماهنگی قبلی.',
            'category_id' => $category->id,
            'city_id' => $city->id,
            'mobile_1' => '۰۹۱۲۳۴۵۶۷۸۹',
            'price' => 0,
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('ads', [
            'user_id' => $user->id,
            'title' => 'تعمیر کولر گازی',
            'status' => 'pending_approval',
            'mobile_1' => '09123456789',
        ]);
        $this->assertDatabaseCount('ad_status_histories', 1);
    }
}
