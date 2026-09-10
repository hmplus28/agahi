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
use App\Models\User;
use App\Support\PersianNormalizer;
use App\Support\SmsTemplates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function __construct(
        private readonly SmsService $sms,
    ) {}

    public function index(): View
    {
        return view('admin.tickets.index', [
            'tickets' => Ticket::query()->with('user')->latest()->paginate(50),
        ]);
    }

    public function show(Ticket $ticket): View
    {
        return view('admin.tickets.show', [
            'ticket'    => $ticket->load(['user', 'messages.sender']),
            'templates' => SmsTemplates::all(),
        ]);
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:open,waiting_user,waiting_support,closed'],
        ]);

        TicketMessage::query()->create([
            'ticket_id'  => $ticket->id,
            'sender_id'  => $request->user()->id,
            'message'    => $data['message'],
            'created_at' => now(),
        ]);

        $ticket->update([
            'status'    => $data['status'],
            'closed_at' => $data['status'] === 'closed' ? now() : null,
        ]);

        return back()->with('success', 'پاسخ ثبت شد.');
    }

    /**
     * Admin creates a new ticket on behalf of a user identified by mobile.
     * Persian digits in the mobile number are normalized before lookup.
     */
    public function storeForUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile'   => ['required', 'string'],
            'subject'  => ['required', 'string', 'max:190'],
            'message'  => ['required', 'string', 'max:5000'],
            'priority' => ['required', 'in:normal,high,urgent'],
        ]);

        $mobile = PersianNormalizer::mobile($data['mobile']);

        $user = User::query()->where('mobile', $mobile)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'mobile' => 'کاربری با این شمارهٔ موبایل یافت نشد.',
            ])->status(422);
        }

        $ticket = Ticket::query()->create([
            'user_id'  => $user->id,
            'subject'  => $data['subject'],
            'priority' => $data['priority'],
            'status'   => 'waiting_user',
        ]);

        TicketMessage::query()->create([
            'ticket_id'  => $ticket->id,
            'sender_id'  => $request->user()->id,
            'message'    => $data['message'],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'تیکت برای کاربر ایجاد شد.');
    }

    /**
     * Send a templated SMS to the ticket owner. Useful when the admin wants
     * to communicate outside the ticket thread (e.g. to remind them).
     */
    public function sendSms(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['required', 'string'],
        ]);

        $template = SmsTemplates::find($data['template_id']);
        if (!$template) {
            return back()->withErrors(['template_id' => 'الگوی پیامک یافت نشد.']);
        }

        $this->sms->send(
            key:     'admin-ticket-sms:'.$ticket->id.':'.$data['template_id'],
            type:    'support',
            mobile:  $ticket->user->mobile,
            message: $template['text'],
            user:    $ticket->user,
        );

        return back()->with('success', 'پیامک برای کاربر ارسال شد.');
    }
}
