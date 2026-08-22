<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdSubmissionService;
use App\Domains\Billing\PaymentService;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedHostingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_renewal_reactivates_an_expired_ad_without_a_queue_worker(): void
    {
        [$user, $ad] = $this->makeAd(AdStatus::Expired);
        $tariff = Tariff::query()->create([
            'code' => 'FREE_RENEWAL',
            'title' => 'تمدید رایگان',
            'price' => 0,
            'service_type' => 'renewal',
            'duration_days' => 30,
            'is_active' => true,
        ]);

        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($user, $ad, $tariff);
        $result = $service->begin($invoice, $user);

        $this->assertSame('successful', $result['payment']->status);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'active']);
        $this->assertDatabaseHas('ad_services', ['ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'status' => 'active']);
    }

    public function test_editing_an_active_ad_sends_it_back_for_moderation(): void
    {
        [$user, $ad] = $this->makeAd(AdStatus::Active);
        $service = app(AdSubmissionService::class);

        $service->update($ad, $user, [
            'title' => 'عنوان ویرایش‌شده',
            'description' => 'توضیحات ویرایش‌شده که برای تأیید دوباره ارسال می‌شود.',
            'category_id' => $ad->category_id,
            'city_id' => $ad->city_id,
            'mobile_1' => $user->mobile,
            'price' => null,
        ], []);

        $this->assertDatabaseHas('ads', ['id' => $ad->id, 'status' => 'pending_approval', 'title' => 'عنوان ویرایش‌شده']);
    }

    public function test_shared_hosting_defaults_do_not_require_redis_or_a_worker(): void
    {
        $this->assertNotSame('redis', config('queue.default'));
        $this->assertNotSame('redis', config('cache.default'));
        $this->assertNotSame('redis', config('session.driver'));
        $this->assertStringContainsString('CACHE_STORE=database', file_get_contents(base_path('.env.example')));
        $this->assertStringContainsString('SESSION_DRIVER=database', file_get_contents(base_path('.env.example')));
        $this->assertStringContainsString('QUEUE_CONNECTION=sync', file_get_contents(base_path('.env.example')));
    }

    /** @return array{User, Ad} */
    private function makeAd(AdStatus $status): array
    {
        $user = User::factory()->create();
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);
        $ad = Ad::query()->create([
            'code' => 'HOST'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'slug' => 'hosting-test',
            'user_id' => $user->id,
            'title' => 'آگهی تست هاست',
            'normalized_title' => 'آگهی تست هاست',
            'normalized_title_hash' => hash('sha256', 'آگهی تست هاست'),
            'description' => 'توضیح آزمایشی برای تست فرایند تمدید.',
            'normalized_description' => 'توضیح آزمایشی برای تست فرایند تمدید.',
            'normalized_description_hash' => hash('sha256', 'توضیح آزمایشی برای تست فرایند تمدید.'),
            'mobile_1' => $user->mobile,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'status' => $status,
            'published_at' => now()->subDays(31),
            'expires_at' => now()->subDay(),
        ]);

        return [$user, $ad];
    }
}
