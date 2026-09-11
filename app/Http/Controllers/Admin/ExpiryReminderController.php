<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domains\Notifications\SmsService;
use App\Http\Controllers\Controller;
use App\Models\AdExpiryReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpiryReminderController extends Controller
{
    public function __construct(
        private readonly SmsService $sms,
    ) {}

    public function index(): View
    {
        $reminders = AdExpiryReminder::query()
            ->with(['ad', 'ad.user', 'ad.category', 'ad.city'])
            ->latest('scheduled_at')
            ->paginate(50);

        return view('admin.expiry-reminders.index', compact('reminders'));
    }

    public function approve(AdExpiryReminder $reminder): RedirectResponse
    {
        if ($reminder->status !== 'pending') {
            return back()->withErrors(['status' => 'این یادآوری قبلاً پردازش شده است.']);
        }

        $ad = $reminder->ad;
        if (!$ad || !$ad->user) {
            return back()->withErrors(['ad' => 'آگهی یا کاربر یافت نشد.']);
        }

        $message = "آگهی «{$ad->title}» شما پس از ۱ سال در حال منقضی شدن است. برای تمدید به پنل کاربری مراجعه کنید. کد آگهی: {$ad->code}";

        $this->sms->send(
            key: 'expiry-reminder:' . $reminder->id,
            type: 'expiry_reminder',
            mobile: $ad->mobile_1,
            message: $message,
            user: $ad->user,
            ad: $ad,
        );

        $reminder->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'پیامک یادآوری انقضا ارسال شد.');
    }

    public function reject(AdExpiryReminder $reminder): RedirectResponse
    {
        if ($reminder->status !== 'pending') {
            return back()->withErrors(['status' => 'این یادآوری قبلاً پردازش شده است.']);
        }

        $reminder->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'یادآوری رد شد.');
    }
}
