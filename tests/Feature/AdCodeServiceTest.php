<?php

declare(strict_types=1);
namespace Tests\Feature;
use App\Domains\Ads\Services\AdCodeService;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_codes_start_from_700000_and_increase_monotonically(): void
    {
        $first = AdCodeService::next();
        $second = AdCodeService::next();

        $this->assertSame(700000, $first);
        $this->assertSame(700001, $second);
        $this->assertSame(700001, (int) json_decode((string) DB::table('site_settings')->where('key', 'ad_code_counter')->value('value'), true));
    }

    public function test_ads_created_without_an_explicit_code_get_a_numeric_code(): void
    {
        $user = User::factory()->create();
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        $ad = Ad::query()->create(['slug' => 'auto-code-test', 'user_id' => $user->id, 'title' => 'آگهی بدون کد', 'normalized_title' => 'آگهی بدون کد', 'normalized_title_hash' => hash('sha256', 'آگهی بدون کد'), 'description' => 'توضیحات آزمایشی برای کد خودکار.', 'normalized_description' => 'توضیحات آزمایشی برای کد خودکار.', 'normalized_description_hash' => hash('sha256', 'توضیحات آزمایشی برای کد خودکار.'), 'mobile_1' => $user->mobile, 'category_id' => $category->id, 'city_id' => $city->id]);

        $code = (string) $ad->fresh()->code;
        $this->assertMatchesRegularExpression('/^\d+$/', $code);
        $this->assertGreaterThanOrEqual(AdCodeService::FIRST_CODE, (int) $code);
    }

    public function test_database_seeder_runs_clean_and_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $ads = Ad::query()->where('slug', 'tamirat-lavazem-khanegi')->get();
        $this->assertCount(1, $ads);
        $this->assertMatchesRegularExpression('/^\d+$/', (string) $ads->first()->code);
        $this->assertDatabaseHas('users', ['mobile' => '09120000000']);
    }
}
