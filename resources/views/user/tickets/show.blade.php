@extends('layouts.app',['title'=>'تیکت '.$ticket->subject.' | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<div class="section-heading">
    <div>
        <h1>{{ $ticket->subject }}</h1>
        <p class="muted">وضعیت: <span class="badge ticket-status-{{ $ticket->status }}">{{ $ticket->statusLabel() }}</span> — اولویت: {{ $ticket->priorityLabel() }} — تاریخ: {{ jdate($ticket->created_at) }}</p>
    </div>
    <a href="{{ route('user.tickets.index') }}">بازگشت به تیکت‌ها</a>
</div>

<section class="ticket-thread">
    @foreach($ticket->messages->sortBy('created_at') as $message)
        <article class="ticket-message {{ $message->sender->id === $ticket->user_id ? 'ticket-message--user' : 'ticket-message--admin' }}">
            <header>
                <strong>{{ $message->sender_id === $ticket->user_id ? 'شما' : 'پشتیبانی' }}</strong>
                <span class="muted">{{ jdate($message->created_at) }}</span>
            </header>
            <p>{{ $message->message }}</p>
        </article>
    @endforeach
</section>

<section class="panel">
    <h2>@if($ticket->status === 'closed')پاسخ جدید (این کار تیکت را دوباره باز می‌کند)@elseارسال پاسخ@endif</h2>
    <form method="post" action="{{ route('user.tickets.reply', $ticket) }}">
        @csrf
        <label>پیام<textarea name="message" rows="5" required maxlength="5000"></textarea></label>
        <button class="button">ارسال پاسخ</button>
    </form>
</section>

<style>
.ticket-thread{display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.5rem}
.ticket-message{border:1px solid #e5e7eb;border-radius:.625rem;padding:.75rem 1rem;background:#fff;max-width:640px}
.ticket-message--admin{align-self:flex-start;background:#f0f1fa;border-color:#c6c7ee}
.ticket-message--user{align-self:flex-end}
.ticket-message header{display:flex;gap:.75rem;align-items:center;margin-bottom:.35rem;font-size:.8rem}
.ticket-message p{font-size:.9rem;line-height:1.9;white-space:pre-wrap}
.ticket-status-open{background:#d1fae5;color:#065f46}
.ticket-status-waiting_user{background:#fef3c7;color:#92400e}
.ticket-status-waiting_support{background:#ede9fe;color:#5b21b6}
.ticket-status-closed{background:#f3f4f6;color:#374151}
.badge{font-size:.72rem;border-radius:999px;padding:.1rem .55rem}
</style>
@endsection
