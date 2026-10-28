<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Accounts\Enums\UserRole;
use App\Models\Ad;
use App\Models\AdService;
use App\Models\Category;
use App\Models\City;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Task 23 - paid options at ad submission:
 *  - the full tariff catalog (free + paid options) is seeded
 *  - guest submission applies FREE options instantly and parks PAID ones
 *    as pending rows for the moderation team
 *  - user submission collects paid options into ONE invoice and activates
 *    them automatically after the (fake) gateway settles the payment
 *  - admins can manually activate pending services from the ads table
 */
class PaidAdOptionsTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $this->seed(\Database\Seeders\TariffCatalogSeeder::class);
    }

    private function adContext(): array
    {
        $country = \App\Models\Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = \App\Models\Province::query()->firstOrCreate(['slug' => 'tehran'], ['country_id' => $country->id, 'name' => 'تهران']);
        $city = City::query()->firstOrCreate(['slug' => 'tehran'], ['province_id' => $province->id, 'name' => 'تهران', 'is_active' => true]);
        $category = Category::query()->firstOrCreate(['slug' => 'test-cat'], ['title' => 'تست', 'is_active' => true]);

        return [$city, $category];
    }

    private function guestPayload(int $cityId, int $categoryId, array $services = []): array
    {
        return [
            'title'       => 'آگهی آزمایشی با اپشن پولی',
            'description' => 'توضیحات آزمایشی برای بررسی اپشن‌های پولی هنگام ثبت آگهی.',
            'category_id' => $categoryId,
            'city_id'     => $cityId,
            'mobile_1'    => '09351112233',
            'full_name'   => 'کاربر مهمان',
            'services'    => $services,
        ];
    }

    public function test_guest_free_options_apply_instantly_and_paid_options_stay_pending(): void
    {
        $this->seedCatalog();
        [$city, $category] = $this->adContext();

        $free = Tariff::query()->where('code', 'FREE_30')->firstOrFail();
        $featured = Tariff::query()->where('code', 'FEATURED')->firstOrFail();

        $response = $this->post(route('guest.ad.store'), $this->guestPayload($city->id, $category->id, [$free->id, $featured->id]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', fn (string $message) => str_contains($message, 'خدمات پولی'));

        $ad = Ad::query()->where('source', 'guest')->where('mobile_1', '09351112233')->firstOrFail();

        // FREE option: active row + ad expiry extended by the "ad" service type.
        $freeService = AdService::query()->where('ad_id', $ad->id)->where('tariff_id', $free->id)->firstOrFail();
        $this->assertSame('active', $freeService->status);
        $this->assertNotNull($ad->refresh()->expires_at);

        // PAID option: parked as pending, no featured flag yet.
        $paidService = AdService::query()->where('ad_id', $ad->id)->where('tariff_id', $featured->id)->firstOrFail();
        $this->assertSame('pending', $paidService->status);
        $this->assertFalse((bool) $ad->is_featured);
    }

    public function test_user_paid_options_create_one_invoice_and_activate_after_settlement(): void
    {
        $this->seedCatalog();
        [$city, $category] = $this->adContext();

        $user = User::factory()->create();
        $free = Tariff::query()->where('code', 'FREE_30')->firstOrFail();
        $urgent = Tariff::query()->where('code', 'URGENT')->firstOrFail();
        $featured = Tariff::query()->where('code', 'FEATURED')->firstOrFail();

        $response = $this->actingAs($user)->post(route('user.ads.store'), [
            'title'       => 'آگهی کاربر با خدمات ویژه و فوری',
            'description' => 'توضیحات آزمایشی برای جریان پرداخت چند آیتمی.',
            'category_id' => $category->id,
            'city_id'     => $city->id,
            'mobile_1'    => '09354445566',
            'services'    => [$free->id, $urgent->id, $featured->id],
        ]);

        // Redirected into the payment flow (fake gateway → signed callback).
        $response->assertRedirect();
        $callbackUrl = $response->headers->get('Location');

        // ONE multi-item invoice (2 paid items), then settle it via the callback.
        $invoice = Invoice::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame(2, $invoice->items()->count());
        $this->assertSame($urgent->price + $featured->price, (int) $invoice->total);
        $this->assertSame('pending', $invoice->status);

        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $ad = Ad::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertTrue((bool) $ad->refresh()->is_featured, 'خدمت ویژه باید پس از تسویه فعال شود.');
        $this->assertTrue((bool) $ad->is_urgent, 'خدمت فوری باید پس از تسویه فعال شود.');

        $this->assertSame('paid', $invoice->refresh()->status);
        $this->assertSame(3, AdService::query()->where('ad_id', $ad->id)->where('status', 'active')->count());
        $this->assertSame('successful', Payment::query()->where('invoice_id', $invoice->id)->firstOrFail()->status);
    }

    public function test_admin_can_activate_a_pending_service_from_the_ads_table(): void
    {
        $this->seedCatalog();
        [$city, $category] = $this->adContext();

        $user = User::factory()->create();
        $ad = Ad::query()->create([
            'slug'       => 'pending-service-ad',
            'user_id'    => $user->id,
            'title'      => 'آگهی در انتظار فعال‌سازی خدمت',
            'normalized_title' => 'آگهی در انتظار فعال‌سازی خدمت',
            'normalized_title_hash' => hash('sha256', 'آگهی در انتظار فعال‌سازی خدمت'),
            'description' => 'توضیحات آزمایشی.',
            'normalized_description' => 'توضیحات آزمایشی.',
            'normalized_description_hash' => hash('sha256', 'توضیحات آزمایشی.'),
            'mobile_1'   => '09357778899',
            'category_id' => $category->id,
            'city_id'    => $city->id,
            'source'     => 'guest',
            'status'     => \App\Domains\Ads\Enums\AdStatus::PendingApproval,
            'sort_at'    => now(),
        ]);

        $featured = Tariff::query()->where('code', 'FEATURED')->firstOrFail();
        $service = app(\App\Domains\Ads\Services\AdServiceActivator::class)->attach($ad, $featured, 'pending');
        $this->assertFalse((bool) $ad->refresh()->is_featured);

        $staff = User::factory()->create(['role' => UserRole::Moderator, 'is_staff' => true]);

        $this->actingAs($staff)
            ->post(route('admin.ads.services.activate', ['ad' => $ad->id, 'service' => $service->id]))
            ->assertRedirect();

        $this->assertSame('active', $service->refresh()->status);
        $this->assertTrue((bool) $ad->refresh()->is_featured, 'باید فلگ ویژه پس از فعال‌سازی دستی اعمال شود.');
    }

    public function test_user_create_form_offers_the_full_paid_catalog(): void
    {
        $this->seedCatalog();
        $user = User::factory()->create();

        $html = (string) $this->actingAs($user)->get(route('user.ads.create'))->assertOk()->getContent();

        $this->assertStringContainsString('بسته آگهی / انتخاب خدمات', $html);
        $this->assertStringContainsString('name="services[]"', $html);
        $this->assertStringContainsString('فوری (درج عبارت فوری)', $html);
        $this->assertStringContainsString('نردبان اتوماتیک ۳۶۵ روزه (روزانه یکبار)', $html);
        // Prices must be Persian digits (toman = rial / 10).
        $this->assertStringContainsString('۲۰۰ تومان', $html);
        $this->assertStringNotContainsString('>100 تومان', $html);
    }
}
