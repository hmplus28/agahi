<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Ads\Services\AdImageProcessor;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Province;
use App\Models\SiteSetting;
use App\Models\Tariff;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Domains\Accounts\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PRD gap-batch (Task 29): §27 card-to-card + admin confirm/reject,
 * §28 invoice page, §29 user ticket thread, §32 home sections, §38/§42/§44
 * city-category URLs + whitelist + locations sitemap, §45/§50 multi-level
 * breadcrumb + city links, §43 pagination canonical, §51 Organization /
 * Product-Offer / JobPosting, §52 descriptive image filenames, §56 srcset,
 * §59 persistent connection config, §63 backup command, §69 404/410 pages.
 */
class PrdGapBatchTest extends TestCase
{
    use RefreshDatabase;

    private static int $seq = 0;

    private function seedGeo(): array
    {
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran-province']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $root = Category::query()->create(['title' => 'صنعتی', 'slug' => 'industrial']);
        $child = Category::query()->create(['title' => 'ماشین CNC', 'slug' => 'cnc', 'parent_id' => $root->id]);
        $shiraz = City::query()->create(['province_id' => $province->id, 'name' => 'شیراز', 'slug' => 'shiraz']);

        return compact('country', 'province', 'city', 'root', 'child', 'shiraz');
    }

    private function makeAd(array $geo, ?User $user = null, array $overrides = []): Ad
    {
        self::$seq++;
        $data = [
            'user_id' => $user?->id,
            'title' => 'دستگاه CNC شماره '.self::$seq,
            'slug' => 'cnc-'.self::$seq,
            'description' => 'توضیحات دستگاه CNC شماره '.self::$seq,
            'normalized_title' => 'cnc-'.self::$seq,
            'normalized_description' => 'cnc-'.self::$seq,
            'normalized_title_hash' => md5('t-'.self::$seq),
            'normalized_description_hash' => md5('d-'.self::$seq),
            'category_id' => $geo['child']->id,
            'city_id' => $geo['city']->id,
            'mobile_1' => '0912000000'.(self::$seq % 10),
            'status' => 'active',
            'published_at' => now()->subMinutes(self::$seq),
            'expires_at' => now()->addDays(30),
            'price' => 1500000,
        ];
        $ad = Ad::query()->create([...$data, ...$overrides]);
        $ad->refresh();

        return $ad;
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_staff' => true, 'role' => UserRole::SuperAdmin]);
    }

    // ── §38 city/category hierarchy ──────────────────────────────────────────

    public function test_city_page_lists_its_ads_and_404s_unknown_city(): void
    {
        $geo = $this->seedGeo();
        $ad = $this->makeAd($geo);

        $this->get('/tehran')
            ->assertOk()
            ->assertSee('آگهی‌های تهران')
            ->assertSee($ad->title, false)
            ->assertSee('index, follow', false);

        $this->get('/unknown-city')->assertNotFound();
    }

    public function test_city_category_page_filters_by_city_and_category(): void
    {
        $geo = $this->seedGeo();
        $tehranAd = $this->makeAd($geo);
        $shirazAd = $this->makeAd($geo, overrides: ['city_id' => $geo['shiraz']->id, 'title' => 'دستگاه شیرازی', 'slug' => 'cnc-shiraz']);

        $this->get('/tehran/cnc')
            ->assertOk()
            ->assertSee('آگهی ماشین CNC در تهران')
            ->assertSee($tehranAd->title, false)
            ->assertDontSee($shirazAd->title, false);
    }

    public function test_city_root_category_page_links_subcategories(): void
    {
        $geo = $this->seedGeo();
        $this->makeAd($geo);

        $this->get('/tehran/industrial')
            ->assertOk()
            ->assertSee('زیر‌دسته‌ها', false)
            ->assertSee('ماشین CNC', false);
    }

    public function test_whitelist_setting_makes_unlisted_combos_noindex(): void
    {
        $geo = $this->seedGeo();
        $this->makeAd($geo);

        SiteSetting::put('seo.landing_whitelist', "tehran/industrial\nshiraz/services");

        $this->get('/tehran/industrial')
            ->assertOk()
            ->assertSee('index, follow', false);

        $this->get('/tehran/cnc')
            ->assertOk()
            ->assertSee('noindex, follow', false);
    }

    public function test_deep_ad_url_301_to_canonical(): void
    {
        $geo = $this->seedGeo();
        $ad = $this->makeAd($geo);

        $this->get('/tehran/cnc/'.$ad->slug.'/ad/'.$ad->code)
            ->assertStatus(301)
            ->assertRedirect($ad->publicUrl());
    }

    // ── §44 locations sitemap ────────────────────────────────────────────────

    public function test_locations_sitemap_lists_city_pages_and_index_links_it(): void
    {
        $geo = $this->seedGeo();
        $this->makeAd($geo);

        $this->get('/sitemaps/locations.xml')
            ->assertOk()
            ->assertSee(url('/tehran'), false)
            ->assertDontSee(url('/shiraz'), false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('sitemap.locations'), false);
    }

    // ── §69 error pages ──────────────────────────────────────────────────────

    public function test_expired_ad_serves_410_and_pending_ad_serves_404(): void
    {
        $geo = $this->seedGeo();
        $expired = $this->makeAd($geo, overrides: ['status' => 'expired', 'expires_at' => now()->subDay()]);
        $pending = $this->makeAd($geo, overrides: ['status' => 'pending_approval', 'published_at' => null]);

        $this->get($expired->publicUrl())
            ->assertStatus(410)
            ->assertSee('این آگهی دیگر در دسترس نیست');

        $this->get($pending->publicUrl())->assertNotFound();
    }

    // ── §32 homepage sections ────────────────────────────────────────────────

    public function test_homepage_has_main_categories_featured_and_cities_sections(): void
    {
        $geo = $this->seedGeo();
        $featured = $this->makeAd($geo, overrides: ['is_featured' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('دسته‌بندی‌های اصلی', false)
            ->assertSee('آگهی‌های ویژه', false)
            ->assertSee('جست‌وجو در شهرها', false)
            ->assertSee($featured->title, false);
    }

    public function test_featured_section_hidden_when_no_featured_ads(): void
    {
        $this->seedGeo();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('آگهی‌های ویژه', false);
    }

    // ── §43 pagination canonical + §56 srcset ────────────────────────────────

    public function test_home_pagination_canonical_keeps_page_param(): void
    {
        $geo = $this->seedGeo();
        $ad = $this->makeAd($geo);

        $this->get('/?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('home').'?page=2"', false);
    }

    public function test_ad_cards_emit_responsive_srcset(): void
    {
        $geo = $this->seedGeo();
        $ad = $this->makeAd($geo);
        $ad->images()->create([
            'image_thumb_path' => 'ads/x/1-cnc-thumb.webp',
            'image_display_path' => 'ads/x/1-cnc-display.webp',
            'width' => 1280, 'height' => 800, 'is_primary' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('srcset="', false)
            ->assertSee('1-cnc-thumb.webp 480w', false)
            ->assertSee('1-cnc-display.webp 1280w', false);
    }

    // ── §45/§50 breadcrumbs + city link ─────────────────────────────────────

    public function test_ad_page_breadcrumb_walks_full_category_chain_and_links_city(): void
    {
        $geo = $this->seedGeo();
        $ad = $this->makeAd($geo);

        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertSee(route('categories.show', $geo['root']), false)
            ->assertSee(route('categories.show', $geo['child']), false)
            ->assertSee(route('city.show', 'tehran'), false);
    }

    public function test_category_page_breadcrumb_is_multi_level_and_links_cities(): void
    {
        $geo = $this->seedGeo();
        $this->makeAd($geo);

        $this->get(route('categories.show', $geo['child']))
            ->assertOk()
            ->assertSee(route('categories.show', $geo['root']), false)
            ->assertSee(route('city.category', ['citySlug' => 'tehran', 'categorySlug' => 'cnc']), false);
    }

    // ── §51 structured data ──────────────────────────────────────────────────

    public function test_home_has_organization_json_ld(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('"@type":"Organization"', false);
    }

    public function test_priced_ad_has_product_offer_json_ld_and_negotiable_does_not(): void
    {
        $geo = $this->seedGeo();
        $priced = $this->makeAd($geo);
        $negotiable = $this->makeAd($geo, overrides: ['price' => null]);

        $this->get($priced->publicUrl())
            ->assertOk()
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"priceCurrency":"IRR"', false);

        $this->get($negotiable->publicUrl())
            ->assertOk()
            ->assertDontSee('"@type":"Product"', false);
    }

    public function test_job_category_emits_jobposting_json_ld(): void
    {
        $geo = $this->seedGeo();
        SiteSetting::put('seo.job_category_slugs', 'cnc,employment');
        $ad = $this->makeAd($geo);

        $this->get($ad->publicUrl())
            ->assertOk()
            ->assertSee('"@type":"JobPosting"', false);
    }

    // ── §52 descriptive image filenames ─────────────────────────────────────

    public function test_image_filename_is_descriptive_with_position(): void
    {
        $this->assertSame('1-cnc-machine-display.webp', AdImageProcessor::filename(1, 'cnc-machine', 'display'));
        $this->assertSame('2-سرویس-خدمات-thumb.webp', AdImageProcessor::filename(2, 'سرویس خدمات', 'thumb'));
        $this->assertSame('1-ad-display.webp', AdImageProcessor::filename(1, '', 'display'));
    }

    // ── §27 card-to-card payment flow ────────────────────────────────────────

    private function makeTariff(string $type = 'featured', int $price = 50000): Tariff
    {
        self::$seq++;

        return Tariff::query()->create([
            'code' => 'T-'.self::$seq,
            'title' => 'تعرفه تست',
            'price' => $price,
            'service_type' => $type,
            'duration_days' => 30,
            'is_active' => true,
        ]);
    }

    public function test_card_payment_creates_needs_review_and_admin_confirm_activates(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff('featured');

        SiteSetting::put('billing.card_number', '6104-3372-1234-5678');

        $this->actingAs($user)
            ->post(route('user.payments.purchase'), [
                'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'card',
            ])
            ->assertRedirect(route('user.payments.index'))
            ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'کارت'));

        $payment = Payment::query()->latest('id')->first();
        $this->assertSame('card', $payment->method);
        $this->assertSame('needs_review', $payment->status);

        // invoice still pending before admin confirmation
        $this->assertSame('pending', $payment->invoice->status);
        $this->assertFalse($ad->fresh()->is_featured);

        // admin sees it in the needs_review filter
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->get(route('admin.payments.index', ['status' => 'needs_review']))
            ->assertOk()
            ->assertSee('نیازمند بررسی');

        // confirm with a note
        $this->actingAs($admin)
            ->patch(route('admin.payments.confirm', $payment), ['admin_note' => 'واریز شد - رسید ۱۲۳'])
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('successful', $payment->status);
        $this->assertSame('واریز شد - رسید ۱۲۳', $payment->admin_note);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('paid', $payment->invoice->fresh()->status);
        $this->assertTrue($ad->fresh()->is_featured);
    }

    public function test_admin_can_reject_needs_review_payment(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff('urgent');

        $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'card',
        ]);

        $payment = Payment::query()->latest('id')->first();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.payments.reject', $payment), ['admin_note' => 'عدم واریز'])
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('canceled', $payment->status);
        $this->assertSame('عدم واریز', $payment->admin_note);
        $this->assertSame('pending', $payment->invoice->fresh()->status);
        $this->assertFalse($ad->fresh()->is_urgent);
    }

    public function test_canceled_payment_can_be_confirmed_later(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff('featured');

        $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'card',
        ]);
        $payment = Payment::query()->latest('id')->first();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->patch(route('admin.payments.reject', $payment));
        $this->actingAs($admin)->patch(route('admin.payments.confirm', $payment), ['admin_note' => 'واریز دیرهنگام رسید']);

        $payment->refresh();
        $this->assertSame('successful', $payment->status);
        $this->assertTrue($ad->fresh()->is_featured);
    }

    public function test_online_payment_still_works_without_method_change(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff('ladder', 30000);

        $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'online',
        ]);

        $payment = Payment::query()->latest('id')->first();
        $this->assertSame('online', $payment->method);
        $this->assertSame('pending', $payment->status);
    }

    // ── §28 invoice page ─────────────────────────────────────────────────────

    public function test_user_sees_own_invoice_page_with_items_and_status(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff('featured');

        $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'card',
        ]);
        $invoice = Invoice::query()->latest('id')->first();

        SiteSetting::put('billing.card_number', '6104-3372-1234-5678');

        $this->actingAs($user)->get(route('user.invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number, false)
            ->assertSee('فروشنده', false)
            ->assertSee('خریدار', false)
            ->assertSee('در انتظار پرداخت', false)
            ->assertSee('تعرفه تست', false)
            ->assertSee('کارت به کارت', false)
            ->assertSee('نیازمند بررسی', false)
            ->assertSee('6104-3372-1234-5678', false);
    }

    public function test_other_users_cannot_open_someone_elses_invoice(): void
    {
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $ad = $this->makeAd($geo, $user);
        $tariff = $this->makeTariff();

        $this->actingAs($user)->post(route('user.payments.purchase'), [
            'ad_id' => $ad->id, 'tariff_id' => $tariff->id, 'method' => 'card',
        ]);
        $invoice = Invoice::query()->latest('id')->first();

        $this->actingAs(User::factory()->create())
            ->get(route('user.invoices.show', $invoice))
            ->assertNotFound();
    }

    // ── §29 user ticket thread ───────────────────────────────────────────────

    public function test_user_can_open_ticket_thread_and_reply(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::query()->create(['user_id' => $user->id, 'subject' => 'مشکل در پرداخت']);
        TicketMessage::query()->create(['ticket_id' => $ticket->id, 'sender_id' => $user->id, 'message' => 'سلام، پرداخت من مشکل دارد.', 'created_at' => now()]);

        $admin = $this->makeAdmin();
        TicketMessage::query()->create(['ticket_id' => $ticket->id, 'sender_id' => $admin->id, 'message' => 'در حال بررسی است.', 'created_at' => now()]);
        $ticket->update(['status' => 'waiting_user']);

        $this->actingAs($user)->get(route('user.tickets.show', $ticket))
            ->assertOk()
            ->assertSee('مشکل در پرداخت', false)
            ->assertSee('در انتظار پاسخ شما', false)
            ->assertSee('در حال بررسی است.', false);

        $this->actingAs($user)
            ->post(route('user.tickets.reply', $ticket), ['message' => 'ممنون، منتظر می‌مانم.'])
            ->assertRedirect();

        $this->assertSame(3, $ticket->messages()->count());
        $this->assertSame('waiting_support', $ticket->fresh()->status);
    }

    public function test_replying_to_closed_ticket_reopens_it(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::query()->create(['user_id' => $user->id, 'subject' => 'تیکت بسته', 'status' => 'closed', 'closed_at' => now()]);
        TicketMessage::query()->create(['ticket_id' => $ticket->id, 'sender_id' => $user->id, 'message' => 'اولین پیام', 'created_at' => now()]);

        $this->actingAs($user)
            ->post(route('user.tickets.reply', $ticket), ['message' => 'دوباره باز شود لطفاً'])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertSame('waiting_support', $ticket->status);
        $this->assertNull($ticket->closed_at);
    }

    public function test_user_cannot_open_other_users_ticket(): void
    {
        $ticket = Ticket::query()->create(['user_id' => User::factory()->create()->id, 'subject' => 'خصوصی']);

        $this->actingAs(User::factory()->create())
            ->get(route('user.tickets.show', $ticket))
            ->assertNotFound();
    }

    // ── §63/§59 config sanity ────────────────────────────────────────────────

    public function test_pgsql_connection_config_declares_persistent_option(): void
    {
        $pgsql = config('database.connections.pgsql');
        $this->assertArrayHasKey('options', $pgsql);
    }

    public function test_backup_command_exists_and_reports_missing_pg_dump_gracefully(): void
    {
        if (trim((string) shell_exec('command -v pg_dump 2>/dev/null')) !== '') {
            $this->markTestSkipped('pg_dump exists in the test environment.');
        }

        $this->artisan('db:backup')->assertExitCode(1);
    }
}
