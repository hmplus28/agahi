<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Billing\PaymentService;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Province;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Ad $ad;
    private Tariff $paidTariff;
    private Tariff $freeTariff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        $this->ad = Ad::query()->create([
            'code'              => 'SEC' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'slug'              => 'security-test-ad-' . random_int(1, 9999),
            'user_id'           => $this->user->id,
            'title'             => 'آگهی تست امنیت',
            'normalized_title'  => 'آگهی تست امنیت',
            'normalized_title_hash'  => hash('sha256', 'آگهی تست امنیت'),
            'description'       => 'توضیح تست.',
            'normalized_description' => 'توضیح تست.',
            'normalized_description_hash' => hash('sha256', 'توضیح تست.'),
            'mobile_1'          => $this->user->mobile,
            'category_id'       => $category->id,
            'city_id'           => $city->id,
            'status'            => 'active',
            'published_at'      => now(),
            'expires_at'        => now()->addDays(30),
        ]);

        $this->paidTariff = Tariff::query()->create([
            'code'          => 'FEATURED_7',
            'title'         => 'ویژه ۷ روز',
            'price'         => 500000,
            'service_type'  => 'featured',
            'duration_days' => 7,
            'is_active'     => true,
        ]);

        $this->freeTariff = Tariff::query()->create([
            'code'          => 'FREE_LADDER',
            'title'         => 'نردبان رایگان',
            'price'         => 0,
            'service_type'  => 'ladder',
            'duration_days' => null,
            'is_active'     => true,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  1. PRICE INTEGRITY — price MUST come from DB, never from user
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function invoice_price_comes_from_tariff_not_from_request(): void
    {
        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($this->user, $this->ad, $this->paidTariff);

        $this->assertSame(500000, $invoice->total);
        $this->assertSame(500000, $invoice->subtotal);

        $item = $invoice->items->first();
        $this->assertSame(500000, $item->unit_price);
        $this->assertSame(500000, $item->total_price);
        $this->assertSame($this->paidTariff->id, $item->metadata['tariff_id']);
    }

    #[Test]
    public function invoice_amount_is_always_read_from_tariff(): void
    {
        $service = app(PaymentService::class);

        // Create two tariffs with different prices
        $expensive = Tariff::query()->create([
            'code' => 'EXPENSIVE', 'title' => 'گران', 'price' => 999000,
            'service_type' => 'featured', 'is_active' => true,
        ]);
        $cheap = Tariff::query()->create([
            'code' => 'CHEAP', 'title' => 'ارزان', 'price' => 1000,
            'service_type' => 'featured', 'is_active' => true,
        ]);

        $invoice1 = $service->createInvoice($this->user, $this->ad, $expensive);
        $invoice2 = $service->createInvoice($this->user, $this->ad, $cheap);

        // Each invoice reflects its tariff price, NOT user input
        $this->assertSame(999000, $invoice1->total);
        $this->assertSame(1000, $invoice2->total);
    }

    #[Test]
    public function inactive_tariff_cannot_be_used(): void
    {
        $inactive = Tariff::query()->create([
            'code' => 'INACTIVE_X', 'title' => 'غیرفعال',
            'price' => 100000, 'service_type' => 'featured', 'is_active' => false,
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('فعال نیست');

        $service = app(PaymentService::class);
        $service->createInvoice($this->user, $this->ad, $inactive);
    }

    // ═══════════════════════════════════════════════════════════════
    //  2. OWNERSHIP — user must own the ad they are paying for
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function user_cannot_create_invoice_for_another_users_ad(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('متعلق به شما نیست');

        $service = app(PaymentService::class);
        $service->createInvoice($this->otherUser, $this->ad, $this->paidTariff);
    }

    #[Test]
    public function begin_validates_invoice_ownership(): void
    {
        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($this->user, $this->ad, $this->paidTariff);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('قابل پرداخت نیست');

        // Try to pay with a different user
        $service->begin($invoice, $this->otherUser);
    }

    #[Test]
    public function verify_validates_payment_ownership(): void
    {
        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($this->user, $this->ad, $this->freeTariff);
        $result = $service->begin($invoice, $this->user);
        $payment = $result['payment'];

        // Generate a valid signature for the authority
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('computeSignature');
        $method->setAccessible(true);
        $sig = $method->invoke($service, $payment->authority);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('متعلق به شما نیست');

        // Try to verify with a different user
        $service->verify($payment->authority, $sig, $this->otherUser);
    }

    // ═══════════════════════════════════════════════════════════════
    //  3. HMAC CALLBACK SIGNATURE — rejects tampered / absent sigs
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function callback_without_signature_is_rejected(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('user.payments.callback', ['authority' => 'SOME-FAKE-AUTHORITY']));

        $response->assertForbidden();
    }

    #[Test]
    public function callback_with_invalid_signature_is_rejected(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('user.payments.callback', [
            'authority' => 'SOME-FAKE-AUTHORITY',
            'signature' => 'tampered-signature-here',
        ]));

        $response->assertForbidden();
    }

    #[Test]
    public function valid_signature_with_unknown_authority_fails(): void
    {
        $service = app(PaymentService::class);

        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('computeSignature');
        $method->setAccessible(true);
        $sig = $method->invoke($service, 'FAKE-AUTH-123');

        $this->actingAs($this->user);

        // Valid signature but authority doesn't exist
        $response = $this->get(route('user.payments.callback', [
            'authority' => 'FAKE-AUTH-123',
            'signature' => $sig,
        ]));

        $response->assertStatus(404);
    }

    #[Test]
    public function signature_is_bound_to_authority(): void
    {
        $service = app(PaymentService::class);

        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('computeSignature');
        $method->setAccessible(true);
        $validate = $ref->getMethod('validateCallbackSignature');
        $validate->setAccessible(true);

        $sigForA = $method->invoke($service, 'AUTHORITY-A');
        $sigForB = $method->invoke($service, 'AUTHORITY-B');

        // Different authorities → different signatures
        $this->assertNotSame($sigForA, $sigForB);

        // Valid pair passes
        $this->assertTrue($validate->invoke($service, 'AUTHORITY-A', $sigForA));

        // Cross-authority fails (attacker swaps authority, keeps signature)
        $this->assertFalse($validate->invoke($service, 'AUTHORITY-B', $sigForA));
    }

    // ═══════════════════════════════════════════════════════════════
    //  4. IDEMPOTENCY — double callback does NOT double-settle
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function settle_is_idempotent(): void
    {
        // Create a pending paid payment (not auto-settled like free)
        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-20260101-IDEM',
            'user_id'        => $this->user->id,
            'ad_id'          => $this->ad->id,
            'status'         => 'pending',
            'subtotal'       => 500000,
            'total'          => 500000,
        ]);

        InvoiceItem::query()->create([
            'invoice_id'  => $invoice->id,
            'title'       => $this->paidTariff->title,
            'quantity'    => 1,
            'unit_price'  => 500000,
            'total_price' => 500000,
            'metadata'    => ['tariff_id' => $this->paidTariff->id, 'service_type' => 'featured'],
        ]);

        $payment = Payment::query()->create([
            'user_id'    => $this->user->id,
            'ad_id'      => $this->ad->id,
            'invoice_id' => $invoice->id,
            'amount'     => 500000,
            'method'     => 'online',
            'status'     => 'pending',
            'gateway'    => 'fake',
            'authority'  => 'DEV-IDEMPOTENT-TEST',
        ]);

        $service = app(PaymentService::class);
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('settle');
        $method->setAccessible(true);

        // First settle
        $first = $method->invoke($service, $payment, 'REF-001');
        $this->assertSame('successful', $first->status);
        $this->assertSame('REF-001', $first->reference_id);

        // Second settle (replay) — idempotent, returns same payment
        $second = $method->invoke($service, $payment, 'REF-002');
        $this->assertSame('successful', $second->status);
        $this->assertSame('REF-001', $second->reference_id); // NOT overwritten

        // Only one ad_service created
        $this->assertDatabaseCount('ad_services', 1);
    }

    #[Test]
    public function settling_a_pay_later_payment_moves_the_ad_to_pending_approval(): void
    {
        $workflow = app(\App\Domains\Ads\Services\AdWorkflow::class);
        $workflow->transition($this->ad, \App\Domains\Ads\Enums\AdStatus::PendingApproval, $this->user, 'در انتظار پرداخت');
        $workflow->transition($this->ad->fresh(), \App\Domains\Ads\Enums\AdStatus::PendingPayment, $this->user, 'انتخاب پرداخت بعداً');
        $this->assertDatabaseHas('ads', ['id' => $this->ad->id, 'status' => 'pending_payment']);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-LATER-001',
            'user_id'        => $this->user->id,
            'ad_id'          => $this->ad->id,
            'status'         => 'pending',
            'subtotal'       => 500000,
            'total'          => 500000,
        ]);
        InvoiceItem::query()->create([
            'invoice_id'  => $invoice->id,
            'title'       => $this->paidTariff->title,
            'quantity'    => 1,
            'unit_price'  => 500000,
            'total_price' => 500000,
            'metadata'    => ['tariff_id' => $this->paidTariff->id, 'service_type' => 'featured'],
        ]);
        $payment = Payment::query()->create([
            'user_id'    => $this->user->id,
            'ad_id'      => $this->ad->id,
            'invoice_id' => $invoice->id,
            'amount'     => 500000,
            'method'     => 'later',
            'status'     => 'pending',
            'gateway'    => 'pending',
            'authority'  => 'LATER-DEV-001',
        ]);

        $service = app(PaymentService::class);
        $ref = new \ReflectionClass($service);
        $method = $ref->getMethod('settle');
        $method->setAccessible(true);
        $method->invoke($service, $payment, 'REF-LATER');

        $this->assertDatabaseHas('ads', ['id' => $this->ad->id, 'status' => 'pending_approval']);
        $this->assertSame('successful', $payment->refresh()->status);
    }

    // ═══════════════════════════════════════════════════════════════
    //  5. FREE TARIFF — skips gateway, settles immediately
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function free_tariff_bypasses_gateway_and_settles_immediately(): void
    {
        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($this->user, $this->ad, $this->freeTariff);
        $result = $service->begin($invoice, $this->user);

        $this->assertSame('successful', $result['payment']->status);
        $this->assertSame('free', $result['payment']->method);
        $this->assertSame(0, $result['payment']->amount);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('ad_services', ['ad_id' => $this->ad->id, 'tariff_id' => $this->freeTariff->id]);
    }

    // ═══════════════════════════════════════════════════════════════
    //  6. INVOICE GUARDS — paid invoice cannot be reused
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function already_paid_invoice_cannot_be_paid_again(): void
    {
        $service = app(PaymentService::class);
        $invoice = $service->createInvoice($this->user, $this->ad, $this->freeTariff);

        // First payment succeeds (free, auto-settles)
        $result = $service->begin($invoice, $this->user);
        $this->assertSame('successful', $result['payment']->status);

        // Second attempt on same invoice MUST fail
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('قابل پرداخت نیست');

        $service->begin($invoice, $this->user);
    }

    // ═══════════════════════════════════════════════════════════════
    //  7. ROUTE PROTECTION — auth middleware enforcement
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function purchase_route_requires_authentication(): void
    {
        $route = Route::getRoutes()->getByName('user.payments.purchase');
        $this->assertNotNull($route);
        $middlewares = $route->gatherMiddleware();
        $this->assertContains('auth', $middlewares);
    }

    #[Test]
    public function callback_route_requires_authentication(): void
    {
        $route = Route::getRoutes()->getByName('user.payments.callback');
        $this->assertNotNull($route);
        $middlewares = $route->gatherMiddleware();
        $this->assertContains('auth', $middlewares);
    }

    #[Test]
    public function purchase_route_has_throttle_middleware(): void
    {
        $route = Route::getRoutes()->getByName('user.payments.purchase');
        $this->assertNotNull($route);

        $middlewares = $route->gatherMiddleware();
        $hasThrottle = false;
        foreach ($middlewares as $m) {
            if (str_starts_with($m, 'throttle:')) {
                $hasThrottle = true;
                break;
            }
        }
        $this->assertTrue($hasThrottle, 'Purchase route must have throttle middleware');
    }

    // ═══════════════════════════════════════════════════════════════
    //  8. SIGNATURE GENERATION — used by the controller for real URLs
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function sign_callback_url_produces_valid_url(): void
    {
        $service = app(PaymentService::class);

        $template = route('user.payments.callback', [
            'authority' => '__AUTHORITY__',
            'signature' => '__SIGNATURE__',
        ]);

        $signed = $service->signCallbackUrl($template, 'REAL-AUTH-001');

        // URL contains the real authority (as route parameter, not query string)
        $this->assertStringContainsString('REAL-AUTH-001', $signed);
        $this->assertStringNotContainsString('__AUTHORITY__', $signed);
        $this->assertStringNotContainsString('__SIGNATURE__', $signed);

        // URL is valid and has signature query param
        $query = parse_url($signed, PHP_URL_QUERY);
        $this->assertNotNull($query);
        parse_str($query, $params);
        $this->assertArrayHasKey('signature', $params);
        $this->assertNotEmpty($params['signature']);
    }

    // ═══════════════════════════════════════════════════════════════
    //  9. COMPLETE FLOW — smoke test for end-to-end integrity
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function complete_paid_flow_creates_pending_payment(): void
    {
        $service = app(PaymentService::class);

        // Step 1: Create invoice from tariff
        $invoice = $service->createInvoice($this->user, $this->ad, $this->paidTariff);
        $this->assertSame('pending', $invoice->status);
        $this->assertSame(500000, $invoice->total);

        // Step 2: Begin payment
        $result = $service->begin($invoice, $this->user);

        // Payment exists with correct amount
        $this->assertSame(500000, $result['payment']->amount);
        $this->assertSame('online', $result['payment']->method);
        $this->assertNotEmpty($result['payment']->authority);

        // The redirect URL is valid
        $this->assertStringContainsString(route('user.dashboard'), $result['redirect_url']);

        // Invoice is still pending (not paid yet — waiting for gateway)
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'pending']);
    }
}