<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Billing\Contracts\PaymentGateway;
use App\Domains\Billing\Gateways\SadadGateway;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SadadGatewayTest extends TestCase
{
    use RefreshDatabase;

    private const CONFIG = [
        'merchant_id' => '00000123456789',
        'terminal_id' => '98765',
        'transaction_key' => 'raCKPaxVFmWC7Luf6YfW7bNVywPyzbJb=',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payment.gateway' => 'sadad',
            'payment.sadad.merchant_id' => self::CONFIG['merchant_id'],
            'payment.sadad.terminal_id' => self::CONFIG['terminal_id'],
            'payment.sadad.transaction_key' => self::CONFIG['transaction_key'],
        ]);
    }

    private function payment(): Payment
    {
        return Payment::query()->make([
            'user_id' => 1,
            'invoice_id' => 1,
            'amount' => 500000,
            'method' => 'online',
            'status' => 'pending',
            'gateway' => 'sadad',
        ]);
    }

    private function gateway(): SadadGateway
    {
        $gateway = app(PaymentGateway::class);
        assert($gateway instanceof SadadGateway);

        return $gateway;
    }

    // ═══════════════════════════════════════════════════════════════
    //  API: PaymentRequest (create)
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function create_sends_correct_payload_and_returns_token_redirect(): void
    {
        Http::fake([
            '*Request/PaymentRequest' => Http::response(['ResCode' => 0, 'Token' => 'TOKEN-123']),
        ]);

        $result = $this->gateway()->create($this->payment());

        Http::assertSent(function ($request): bool {
            $body = $request->data();

            return str_contains($request->url(), '/vpg/api/v0/Request/PaymentRequest')
                && $body['MerchantId'] === self::CONFIG['merchant_id']
                && $body['TerminalId'] === self::CONFIG['terminal_id']
                && $body['Amount'] === 500000
                && isset($body['OrderId'], $body['SignData'], $body['LocalDateTime'], $body['ReturnUrl']);
        });

        $this->assertStringStartsWith('S-', $result['authority']);
        $this->assertSame('https://sadad.shaparak.ir/VPG/Purchase?token=TOKEN-123', $result['redirect_url']);
    }

    #[Test]
    public function create_return_url_contains_hmac_signature(): void
    {
        Http::fake(['*Request/PaymentRequest' => Http::response(['ResCode' => 0, 'Token' => 'T'])]);

        $result = $this->gateway()->create($this->payment());

        $query = parse_url($result['redirect_url'], PHP_URL_QUERY);
        // ReturnUrl embedded in the request — inspect the fake request instead
        Http::assertSent(function ($request) use ($result): bool {
            $returnUrl = $request->data()['ReturnUrl'];

            return str_contains($returnUrl, route('user.payments.callback', ['authority' => $result['authority']], false))
                || (str_contains($returnUrl, (string) $result['authority'])
                    && str_contains($returnUrl, 'signature='));
        });
    }

    #[Test]
    public function create_throws_logic_exception_when_gateway_rejects(): void
    {
        Http::fake(['*Request/PaymentRequest' => Http::response(['ResCode' => -1, 'Description' => 'bad'])]);

        $this->expectException(\LogicException::class);

        $this->gateway()->create($this->payment());
    }

    #[Test]
    public function create_throws_runtime_exception_on_network_error(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'));

        $this->expectException(RuntimeException::class);

        $this->gateway()->create($this->payment());
    }

    #[Test]
    public function create_rejects_zero_amount_without_calling_api(): void
    {
        Http::fake();

        $payment = $this->payment();
        $payment->amount = 0;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('بیشتر از صفر');

        $this->gateway()->create($payment);

        Http::assertNothingSent();
    }

    #[Test]
    public function create_fails_fast_when_credentials_missing(): void
    {
        config([
            'payment.sadad.merchant_id' => '',
            'payment.sadad.transaction_key' => '',
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('پیکربندی نشده');

        $this->gateway()->create($this->payment());
    }

    // ═══════════════════════════════════════════════════════════════
    //  API: Advice/Verify
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function verify_returns_success_on_res_code_zero(): void
    {
        Http::fake([
            '*Advice/Verify' => Http::response([
                'ResCode' => 0,
                'RetrivalRefNo' => '123456',
                'SystemTraceNo' => '789012',
            ]),
        ]);

        $result = $this->gateway()->verify($this->payment(), [
            'ResCode' => 0,
            'Token' => 'TOKEN-123',
            'OrderId' => '260824120000001',
        ]);

        $this->assertTrue($result['successful']);
        $this->assertSame('123456', $result['reference_id']);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/vpg/api/v0/Advice/Verify')
            && $request->data()['Token'] === 'TOKEN-123'
            && isset($request->data()['SignData']));
    }

    #[Test]
    public function verify_short_circuits_on_non_zero_callback_res_code_without_api_call(): void
    {
        Http::fake();

        $result = $this->gateway()->verify($this->payment(), ['ResCode' => -6, 'Token' => 'X']);

        $this->assertFalse($result['successful']);
        $this->assertNull($result['reference_id']);

        Http::assertNothingSent();
    }

    #[Test]
    public function verify_fails_when_verify_api_reports_failure(): void
    {
        Http::fake(['*Advice/Verify' => Http::response(['ResCode' => -1])]);

        $result = $this->gateway()->verify($this->payment(), ['ResCode' => 0, 'Token' => 'TAMPERED']);

        $this->assertFalse($result['successful']);
        $this->assertNull($result['reference_id']);
    }

    #[Test]
    public function verify_fails_when_callback_has_no_token(): void
    {
        Http::fake();

        $result = $this->gateway()->verify($this->payment(), ['ResCode' => 0]);

        $this->assertFalse($result['successful']);
        Http::assertNothingSent();
    }

    // ═══════════════════════════════════════════════════════════════
    //  SECURITY: full POST callback flow (Sadad posts without CSRF)
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function sadad_post_callback_without_csrf_token_is_accepted_and_settles(): void
    {
        $user = \App\Models\User::factory()->create();

        $country = \App\Models\Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = \App\Models\Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = \App\Models\City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = \App\Models\Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        $ad = \App\Models\Ad::query()->create([
            'code' => 'SAD001',
            'slug' => 'sadad-test-ad',
            'user_id' => $user->id,
            'title' => 'آگهی سداد',
            'normalized_title' => 'آگهی سداد',
            'normalized_title_hash' => hash('sha256', 'آگهی سداد'),
            'description' => 'توضیح.',
            'normalized_description' => 'توضیح.',
            'normalized_description_hash' => hash('sha256', 'توضیح.'),
            'mobile_1' => $user->mobile,
            'category_id' => $category->id,
            'city_id' => $city->id,
            'status' => 'active',
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $tariff = \App\Models\Tariff::query()->create([
            'code' => 'SADAD_FEATURED',
            'title' => 'ویژه',
            'price' => 500000,
            'service_type' => 'featured',
            'duration_days' => 7,
            'is_active' => true,
        ]);

        $invoice = \App\Models\Invoice::query()->create([
            'invoice_number' => 'INV-SADAD-TEST',
            'user_id' => $user->id,
            'ad_id' => $ad->id,
            'status' => 'pending',
            'subtotal' => 500000,
            'total' => 500000,
        ]);

        \App\Models\InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'title' => 'ویژه',
            'quantity' => 1,
            'unit_price' => 500000,
            'total_price' => 500000,
            'metadata' => ['tariff_id' => $tariff->id, 'service_type' => 'featured'],
        ]);

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
            'amount' => 500000,
            'method' => 'online',
            'status' => 'pending',
            'gateway' => 'sadad',
            'authority' => 'S-260824120000001',
        ]);

        $service = app(\App\Domains\Billing\PaymentService::class);
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('computeSignature');
        $method->setAccessible(true);
        $signature = $method->invoke($service, $payment->authority);

        Http::fake([
            '*Advice/Verify' => Http::response(['ResCode' => 0, 'RetrivalRefNo' => '998877']),
        ]);

        // Sadad auto-POSTs the browser here WITHOUT any CSRF token
        $response = $this->actingAs($user)
            ->post(route('user.payments.callback', ['authority' => $payment->authority]) . '?signature=' . $signature, [
                'ResCode' => 0,
                'Token' => 'TOKEN-ABC',
                'OrderId' => '260824120000001',
            ]);

        $response->assertRedirect(route('user.payments.index'));

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'successful',
            'reference_id' => '998877',
        ]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id, 'status' => 'failed']);
    }

    #[Test]
    public function forged_sadad_post_callback_is_rejected(): void
    {
        Http::fake(['*Advice/Verify' => Http::response(['ResCode' => 0, 'RetrivalRefNo' => 'X'])]);

        $response = $this->actingAs(\App\Models\User::factory()->create())
            ->post(route('user.payments.callback', ['authority' => 'S-FORGED']) . '?signature=invalid', [
                'ResCode' => 0,
                'Token' => 'STOLEN',
            ]);

        $response->assertForbidden();
        Http::assertNothingSent();
    }
}
