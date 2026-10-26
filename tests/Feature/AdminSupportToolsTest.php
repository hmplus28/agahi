<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Accounts\Enums\UserRole;
use App\Models\Ad;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\AdReport;
use App\Models\Category;
use App\Models\Country;
use App\Models\Province;
use App\Models\City;
use App\Models\SmsLog;
use App\Models\Ticket;
use App\Models\User;
use App\Support\SmsTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['mobile' => '09120001111', 'role' => UserRole::SuperAdmin->value, 'is_staff' => true, 'is_active' => true]);
        $this->user = User::factory()->create(['mobile' => '09120002222', 'is_active' => true]);
    }

    private function createAd(User $owner): Ad
    {
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $category = Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        return Ad::query()->create([
            'code' => 'RPT1234567', 'slug' => 'reported-ad', 'user_id' => $owner->id,
            'title' => 'آگهی گزارش‌شده', 'normalized_title' => 'آگهی گزارش‌شده',
            'normalized_title_hash' => hash('sha256', 'آگهی گزارش‌شده'),
            'description' => 'توضیحات', 'normalized_description' => 'توضیحات',
            'normalized_description_hash' => hash('sha256', 'توضیحات'),
            'mobile_1' => $owner->mobile, 'category_id' => $category->id, 'city_id' => $city->id,
            'status' => AdStatus::Active,
        ]);
    }

    public function test_guests_and_non_staff_cannot_access_admin_support_routes(): void
    {
        $this->post(route('admin.tickets.storeForUser'), ['mobile' => '09120002222'])->assertRedirect('/login');
        $this->actingAs($this->user)->post(route('admin.tickets.storeForUser'), ['mobile' => '09120002222'])->assertForbidden();
        $this->actingAs($this->user)->post(route('admin.sms.templates.store'), ['label' => 'x', 'text' => 'y'])->assertForbidden();
        $this->get(route('terms'))->assertOk();
    }

    public function test_admin_creates_a_ticket_for_a_mobile_number(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tickets.storeForUser'), [
            'mobile' => '09120002222', 'subject' => 'موضوع تستی', 'message' => 'پیام مدیریت', 'priority' => 'high',
        ]);

        $ticket = Ticket::query()->latest('id')->first();
        $response->assertRedirect(route('admin.tickets.show', $ticket));
        $this->assertSame($this->user->id, $ticket->user_id);
        $this->assertSame('waiting_user', $ticket->status);
        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id]);
    }

    public function test_ticket_creation_normalizes_persian_digits(): void
    {
        $this->actingAs($this->admin)->post(route('admin.tickets.storeForUser'), [
            'mobile' => '۰۹۱۲۰۰۰۲۲۲۲', 'subject' => 's', 'message' => 'm', 'priority' => 'normal',
        ])->assertSessionDoesntHaveErrors();

        $this->assertTrue(Ticket::query()->where('user_id', $this->user->id)->exists());
    }

    public function test_ticket_creation_rejects_unknown_mobile(): void
    {
        $this->actingAs($this->admin)->post(route('admin.tickets.storeForUser'), [
            'mobile' => '09350000000', 'subject' => 's', 'message' => 'm', 'priority' => 'normal',
        ])->assertSessionHasErrors('mobile');
    }

    public function test_admin_can_send_a_template_sms_on_a_ticket(): void
    {
        SmsTemplates::save('پاسخ پشتیبانی', 'پیام پاسخ');
        $ticket = Ticket::query()->create(['user_id' => $this->user->id, 'subject' => 's', 'priority' => 'normal']);

        $template = SmsTemplates::all()[0];
        $this->actingAs($this->admin)->post(route('admin.tickets.sms', $ticket), [
            'template_id' => $template['id'],
        ])->assertRedirect();

        $log = SmsLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame('support', $log->type);
        $this->assertSame($this->user->mobile, $log->mobile);
        $this->assertSame('sent', $log->status);
    }

    public function test_report_quick_action_deletes_the_ad_and_resolves_the_report(): void
    {
        $ad = $this->createAd($this->user);
        $report = AdReport::query()->create(['ad_id' => $ad->id, 'reason' => 'fraud', 'description' => 'test', 'status' => 'new']);

        $this->actingAs($this->admin)->post(route('admin.reports.deleteAd', $report))->assertRedirect();

        $this->assertSame(AdStatus::Deleted, $ad->fresh()->status);
        $this->assertSame('resolved', $report->fresh()->status);
    }

    public function test_report_quick_action_sends_warning_sms(): void
    {
        $ad = $this->createAd($this->user);
        $report = AdReport::query()->create(['ad_id' => $ad->id, 'reason' => 'fraud', 'description' => null, 'status' => 'new']);
        $id = SmsTemplates::save('هشدار', 'لطفاً محتوای آگهی را اصلاح کنید.');

        $this->actingAs($this->admin)->post(route('admin.reports.sms', $report), ['template_id' => $id])->assertRedirect();

        $this->assertDatabaseHas('sms_logs', ['type' => 'report_warning', 'mobile' => $this->user->mobile, 'status' => 'sent']);
        $log = SmsLog::query()->latest('id')->first();
        $this->assertSame($ad->id, $log->ad_id);
        $this->assertNotNull($log->sent_at);
    }

    public function test_report_quick_action_opens_a_ticket_for_the_ad_owner(): void
    {
        $ad = $this->createAd($this->user);
        $report = AdReport::query()->create(['ad_id' => $ad->id, 'reason' => 'spam', 'description' => null, 'status' => 'new']);

        $this->actingAs($this->admin)->post(route('admin.reports.ticket', $report))->assertRedirect();

        $ticket = Ticket::query()->latest('id')->first();
        $this->assertSame($this->user->id, $ticket->user_id);
        $this->assertSame('reviewing', $report->fresh()->status);
    }

    public function test_reports_index_shows_ad_link_and_actions_for_admins(): void
    {
        SmsTemplates::save('هشدار تخلف', 'لطفاً اصلاح کنید.');
        $ad = $this->createAd($this->user);
        AdReport::query()->create(['ad_id' => $ad->id, 'reason' => 'fraud', 'description' => null, 'status' => 'new']);

        $this->actingAs($this->admin)->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee($ad->publicUrl(), false)
            ->assertSee('حذف آگهی', false)
            ->assertSee('پیامک به کاربر', false)
            ->assertSee('تیکت به کاربر', false);
    }
}
