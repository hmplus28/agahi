<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Domains\Notifications\SmsService;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdReport;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Support\SmsTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly SmsService $sms,
        private readonly AdWorkflow $workflow,
    ) {}

    public function index(): View
    {
        // Load active templates so the admin can pick one when sending an SMS.
        // SmsTemplates::all() returns a plain array; wrap it in a collection
        // so the Blade view can call ->isNotEmpty() and ->first() fluently.
        return view('admin.reports.index', [
            'reports'   => AdReport::query()->with(['ad', 'ad.user'])->latest()->paginate(50),
            'templates' => collect(SmsTemplates::all()),
        ]);
    }

    public function update(Request $request, AdReport $report): RedirectResponse
    {
        $data = $request->validate([
            'status'     => ['required', 'in:new,reviewing,resolved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:1500'],
        ]);

        $report->update([
            ...$data,
            'reviewed_at' => in_array($data['status'], ['resolved', 'rejected'], true) ? now() : null,
        ]);

        return back()->with('success', 'گزارش به‌روزرسانی شد.');
    }

    /**
     * Quick action: delete the reported ad and mark the report as resolved.
     */
    public function deleteAd(AdReport $report): RedirectResponse
    {
        $ad = $report->ad;
        if ($ad && $ad->status !== AdStatus::Deleted) {
            $this->workflow->transition($ad, AdStatus::Deleted, request()->user(), 'حذف در پی گزارش تخلف');
        }
        $report->update(['status' => 'resolved', 'reviewed_at' => now()]);

        return back()->with('success', 'آگهی حذف شد و گزارش بسته شد.');
    }

    /**
     * Quick action: SMS a warning template to the ad owner.
     */
    public function sendSms(Request $request, AdReport $report): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'string'],
        ]);

        $template = SmsTemplates::find($data['template_id']);
        if (!$template) {
            return back()->withErrors(['template_id' => 'الگوی پیامک یافت نشد.']);
        }

        $ad = $report->ad;
        $user = $ad?->user;

        if (!$user) {
            return back()->withErrors(['mobile' => 'کاربر این آگهی در دسترس نیست.']);
        }

        $this->sms->send(
            key:     'admin-report-sms:'.$report->id.':'.$data['template_id'],
            type:    'report_warning',
            mobile:  $user->mobile,
            message: $template['text'],
            user:    $user,
            ad:      $ad,
        );

        return back()->with('success', 'پیامک هشدار برای کاربر ارسال شد.');
    }

    /**
     * Quick action: open a ticket for the ad owner so support can talk to
     * them directly. The report is moved to "reviewing" status while we wait.
     */
    public function openTicket(AdReport $report): RedirectResponse
    {
        $ad = $report->ad;
        $user = $ad?->user;

        if (!$user) {
            return back()->withErrors(['user' => 'کاربر این آگهی در دسترس نیست.']);
        }

        $ticket = Ticket::query()->create([
            'user_id'  => $user->id,
            'subject'  => 'بررسی آگهی «' . ($ad?->title ?? '') . '»',
            'priority' => 'normal',
            'status'   => 'waiting_user',
        ]);

        TicketMessage::query()->create([
            'ticket_id'  => $ticket->id,
            'sender_id'  => request()->user()->id,
            'message'    => 'در خصوص آگهی شما گزارش تخلف ثبت شده است. لطفاً پاسخ دهید.',
            'created_at' => now(),
        ]);

        $report->update(['status' => 'reviewing']);

        return back()->with('success', 'تیکت برای کاربر ایجاد شد.');
    }
}
