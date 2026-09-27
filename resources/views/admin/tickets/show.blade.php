@extends('layouts.app',['title'=>'پاسخ تیکت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
@include('admin._toolbar')
<div class="page-top"><div><h1>{{ $ticket->subject }}</h1><p class="muted">کاربر: {{ $ticket->user->mobile }}</p></div><a class="button button-outline" href="{{ route('admin.tickets.index') }}">بازگشت به تیکت‌ها</a></div>
<section class="panel"><div class="messages">@foreach($ticket->messages as $message)<article class="message"><strong>{{ $message->sender->name }}</strong><p>{{ $message->message }}</p><small>{{ jdate($message->created_at) }}</small></article>@endforeach</div><form method="post" action="{{ route('admin.tickets.reply',$ticket) }}">@csrf<label>پاسخ<textarea name="message" rows="5" required></textarea></label><label>وضعیت<select name="status"><option value="waiting_user">در انتظار کاربر</option><option value="waiting_support">در انتظار پشتیبانی</option><option value="closed">بسته</option></select></label><button class="button">ارسال پاسخ</button></form></section>
@endsection
