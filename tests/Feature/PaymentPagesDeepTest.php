<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Models\Ad;
use App\Models\AdService;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPagesDeepTest extends TestCase
{
    use RefreshDatabase;

    private function seedGeo(): array
    {
        $country = \App\Models\Country::query()->firstOrCreate(['slug' => 'iran'], ['name' => 'ایران']);
        $province = \App\Models\Province::query()->firstOrCreate(['slug' => 'tehran', 'country_id' => $country->id], ['name' => 'تهران']);
        $city = \App\Models\City::query()->firstOrCreate(['slug' => 'tehran', 'province_id' => $province->id], ['name' => 'تهران']);
        $category = \App\Models\Category::query()->firstOrCreate(['slug' => 'services-pay'], ['title' => 'خدمات پرداخت']);

        return compact('country', 'province', 'city', 'category');
    }

    private function makeAd(User $owner, array $overrides = []): Ad
    {
        $geo = $this->seedGeo();
        $n = random_int(100000, 999999);
        $title = $overrides['title'] ?? ('آگهی پرداخت '.$n);

        return Ad::query()->create(array_merge([
            'code'                       => 'PAY'.$n,
            'slug'                       => 'pay-ad-'.$n,
            'user_id'                    => $owner->id,
            'title'                      => $title,
            'normalized_title'           => $title,
            'normalized_title_hash'      => hash('sha256', $title),
            'description'                => 'توضیحات آگهی برای فلوی پرداخت.',
            'normalized_description'     => 'توضیحات آگهی برای فلوی پرداخت.',
            'normalized_description_hash' => hash('sha256', 'توضیحات آگهی برای فلوی پرداخت.'),
            'mobile_1'                   => $owner->mobile,
            'category_id'                => $geo['category']->id,
            'city_id'                    => $geo['city']->id,
            'status'                     => AdStatus::Active,
            'published_at'               => now(),
            'expires_at'                 => now()->addDays(30),
            'price'                      => 1000,
        ], $overrides));
    }

    private function makeTariff(string $type, int $price = 50000): Tariff
    {
        $n = random_int(1000, 9999);

        return Tariff::query()->create([
            'code'          => strtoupper($type).'-DP-'.$n,
            'title'         => 'تعرفه '.ucfirst($type).' تست',
            'price'         => $price,
            'service_type'  => $type,
            'duration_days' => $type === 'renewal' ? 30 : 30,
            'is_active'     => true,
        ]);
    }

    private function purchase(User $user, Ad $ad, Tariff $tariff): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id'     => $ad->id,
            'tariff_id' => $tariff->id,
        ]);
    }



    public function test_payments_page_renders_purchase_form_tariffs_and_own_ads(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $this->actingAs($user)->get(route('user.payments.index'))
            ->assertOk()
            ->assertSee(route('user.payments.purchase'), false)
            ->assertSee((string) $ad->id, false)
            ->assertSee($tariff->title, false);
    }

    public function test_payments_page_hides_inactive_tariffs(): void
    {
        $user = User::factory()->create();
        $this->makeAd($user);
        $dead = Tariff::query()->create([
            'code' => 'DEAD-'.random_int(1000, 9999), 'title' => 'تعرفهٔ غیرفعال مخفی', 'price' => 1000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => false,
        ]);

        $this->actingAs($user)->get(route('user.payments.index'))
            ->assertOk()
            ->assertDontSee('تعرفهٔ غیرفعال مخفی', false);
    }

    public function test_inactive_tariff_purchase_fails_validation_not_500(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $dead = Tariff::query()->create([
            'code' => 'DEAD2-'.random_int(1000, 9999), 'title' => 'تعرفهٔ مرده', 'price' => 1000,
            'service_type' => 'ladder', 'duration_days' => 30, 'is_active' => false,
        ]);

        $response = $this->purchase($user, $ad, $dead);
        $response->assertSessionHasErrors(['tariff_id']);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_purchase_for_another_users_ad_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $ad = $this->makeAd($owner);
        $tariff = $this->makeTariff('ladder');

        $this->purchase($attacker, $ad, $tariff)->assertForbidden();
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_guest_cannot_reach_purchase_or_callback(): void
    {
        $this->post(route('user.payments.purchase'), ['ad_id' => 1, 'tariff_id' => 1])
            ->assertRedirect(route('login'));
        $this->get(route('user.payments.callback', ['authority' => 'X']))->assertRedirect(route('login'));
    }



    public function test_ladder_purchase_round_trip_settles_and_bumps_ladder(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $old = now()->subDays(2);
        $ad->forceFill(['last_ladder_at' => $old, 'sort_at' => $old])->save();
        $tariff = $this->makeTariff('ladder');

        $response = $this->purchase($user, $ad, $tariff);
        $response->assertRedirect();


        $callbackUrl = $response->headers->get('Location');
        $this->assertStringContainsString('signature=', $callbackUrl, 'callback redirect must carry the HMAC signature');

        $this->get($callbackUrl)
            ->assertRedirect(route('user.payments.index'));

        $payment = Payment::query()->where('ad_id', $ad->id)->sole();
        $this->assertSame('successful', $payment->status);
        $this->assertDatabaseHas('invoices', ['id' => $payment->invoice_id, 'status' => 'paid']);
        $this->assertSame(1, AdService::query()->where('ad_id', $ad->id)->where('tariff_id', $tariff->id)->count());

        $ad->refresh();
        $this->assertTrue($ad->last_ladder_at->gt($old), 'ladder purchase must bump ladder timestamps');
    }

    public function test_featured_purchase_round_trip_marks_ad_featured(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('featured');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $ad->refresh();
        $this->assertTrue($ad->is_featured);
    }

    public function test_urgent_purchase_round_trip_marks_ad_urgent(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('urgent');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $ad->refresh();
        $this->assertTrue($ad->is_urgent);
    }

    public function test_renewal_purchase_revives_expired_ad(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user, ['status' => AdStatus::Expired, 'expires_at' => now()->subDay()]);
        $tariff = $this->makeTariff('renewal', 30000);

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $ad->refresh();
        $this->assertSame(AdStatus::Active, $ad->status, 'renewal must reactivate the expired ad');
        $this->assertTrue($ad->expires_at->gt(now()->addDays(29)), 'expiry must be extended by tariff duration');


        $this->actingAs($user)->get(route('user.expired-ads.index'))
            ->assertOk()
            ->assertDontSee($ad->title, false);
    }

    public function test_gateway_failure_marks_payment_failed_and_page_hides_it(): void
    {
        $this->mock(PaymentGateway::class, function ($mock) {
            $mock->shouldReceive('create')->andReturnUsing(function (Payment $p) {
                $p->update(['authority' => 'FAIL-'.random_int(100000, 999999)]);
                return ['authority' => $p->authority];
            });
            $mock->shouldReceive('verify')->andReturn(['successful' => false, 'reference_id' => null]);
        });

        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $payment = Payment::query()->where('ad_id', $ad->id)->sole();
        $this->assertSame('failed', $payment->status);
        $this->assertDatabaseHas('invoices', ['id' => $payment->invoice_id, 'status' => 'pending']);
        $this->assertSame(0, AdService::query()->where('ad_id', $ad->id)->count());


        $this->actingAs($user)->get(route('user.payments.index'))
            ->assertOk()
            ->assertDontSee($payment->authority, false);
    }

    public function test_double_callback_does_not_duplicate_side_effects(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $this->assertSame(1, Payment::query()->where('ad_id', $ad->id)->count());
        $this->assertSame(1, AdService::query()->where('ad_id', $ad->id)->count());
        $this->assertSame(1, Invoice::query()->where('ad_id', $ad->id)->count());
    }

    public function test_forged_callback_signature_is_rejected(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $forged = (string) \Illuminate\Support\Str::of($callbackUrl)
            ->replaceMatches('/signature=[^&]+/', 'signature=deadbeef');

        $this->get($forged)->assertForbidden();
        $this->assertSame('pending', Payment::query()->where('ad_id', $ad->id)->sole()->status);
    }



    public function test_successful_payment_appears_on_user_and_admin_pages(): void
    {
        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $callbackUrl = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $this->get($callbackUrl)->assertRedirect(route('user.payments.index'));

        $payment = Payment::query()->where('ad_id', $ad->id)->sole();

        $this->actingAs($user)->get(route('user.payments.index'))
            ->assertOk()
            ->assertSee($payment->authority, false);

        $admin = User::factory()->create(['is_staff' => true, 'role' => \App\Domains\Accounts\Enums\UserRole::SuperAdmin]);
        $this->actingAs($admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($payment->reference_id, false);
    }



    public function test_sadad_request_contains_signed_return_url_and_triples_sign_data(): void
    {
        config([
            'payment.gateway' => 'sadad',
            'payment.sadad.merchant_id' => '00000123456789',
            'payment.sadad.terminal_id' => '98765',
            'payment.sadad.transaction_key' => 'raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb=',
        ]);
        \Illuminate\Support\Facades\Http::fake([
            'sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest' => \Illuminate\Support\Facades\Http::response([
                'ResCode' => 0, 'Token' => 'TOKEN-123',
            ]),
        ]);

        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('ladder');

        $response = $this->purchase($user, $ad, $tariff);
        $callbackUrl = $response->headers->get('Location');


        $this->assertStringStartsWith(config('payment.sadad.purchase_url'), $callbackUrl, 'user must land on the Sadad purchase page');
        $this->assertStringContainsString('token=TOKEN-123', $callbackUrl);

        \Illuminate\Support\Facades\Http::assertSent(function ($request): bool {
            $payload = $request->data();


            $returnUrl = $payload['ReturnUrl'] ?? '';
            parse_str(parse_url($returnUrl, PHP_URL_QUERY) ?? '', $query);
            if (!isset($query['signature'])) return false;

            $service = app(\App\Domains\Billing\PaymentService::class);



            $authority = 'S-'.$payload['OrderId'];

            return $service->validateCallbackSignature($authority, $query['signature'])
                && !empty($payload['SignData'])
                && str_starts_with($authority, 'S-');
        });
    }

    public function test_sadad_sign_data_matches_triple_des_spec(): void
    {



        $key = base64_decode('raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb=', false);
        $expected = base64_encode(openssl_encrypt(
            '98765;20260101120000001;500000;'."\n",
            'DES-EDE3-CBC', $key, OPENSSL_RAW_DATA, str_repeat("\x00", 8),
        ));

        $reflection = new \ReflectionClass(\App\Domains\Billing\Gateways\SadadGateway::class);
        $method = $reflection->getMethod('sign');
        $method->setAccessible(true);
        $gateway = new \App\Domains\Billing\Gateways\SadadGateway();

        config(['payment.sadad.transaction_key' => 'raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb=']);
        $this->assertSame($expected, $method->invoke($gateway, '98765;20260101120000001;500000;'."\n"));
    }

    public function test_sadad_post_callback_without_csrf_settles_the_payment(): void
    {
        config([
            'payment.gateway' => 'sadad',
            'payment.sadad.merchant_id' => '00000123456789',
            'payment.sadad.terminal_id' => '98765',
            'payment.sadad.transaction_key' => 'raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb=',
        ]);
        \Illuminate\Support\Facades\Http::fake([
            '*/vpg/api/v0/Request/PaymentRequest' => \Illuminate\Support\Facades\Http::response([
                'ResCode' => 0, 'Token' => 'TOKEN-init',
            ]),
            '*/vpg/api/v0/Advice/Verify' => \Illuminate\Support\Facades\Http::response([
                'ResCode' => 0, 'RetrivalRefNo' => 'REF-998877',
            ]),
        ]);

        $user = User::factory()->create();
        $ad = $this->makeAd($user);
        $tariff = $this->makeTariff('featured');


        $purchaseRedirect = $this->purchase($user, $ad, $tariff)->headers->get('Location');
        $payment = Payment::query()->where('ad_id', $ad->id)->sole();



        $service = app(\App\Domains\Billing\PaymentService::class);
        $this->post(route('user.payments.callback', [
            'authority' => $payment->authority,
            'signature' => $service->computeSignature($payment->authority),
        ]), [
            'Token'   => 'TOKEN-xyz',
            'ResCode' => 0,
        ])->assertRedirect(route('user.payments.index'));

        $payment->refresh();
        $this->assertSame('successful', $payment->status);
        $ad->refresh();
        $this->assertTrue($ad->is_featured);
    }
}
