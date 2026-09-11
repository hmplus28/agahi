@extends('layouts.app', ['title' => 'یادآوری انقضای آگهی‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="page-top"><div><h1>یادآوری انقضای آگهی‌ها</h1><p>آگهی‌هایی که بیش از ۱ سال از ثبت آنها گذشته و منتظر تأیید ارسال پیامک هستند.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a></div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>کاربر</th>
                <th>شماره موبایل</th>
                <th>تاریخ ثبت آگهی</th>
                <th>تاریخ انقضا</th>
                <th>وضعیت</th>
                <th>اقدام</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reminders as $reminder)
                @php($ad = $reminder->ad)
                <tr>
                    <td>
                        @if($ad)
                            <a href="{{ $ad->publicUrl() }}" target="_blank" rel="noopener">{{ $ad->title }}</a>
                            <small style="display:block;color:#6b7280">کد: {{ $ad->code }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if($ad?->user)
                            {{ $ad->user->name ?? '—' }}
                            <small style="display:block;color:#6b7280">{{ $ad->user->mobile }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $ad?->mobile_1 ?? '—' }}</td>
                    <td>{{ jdate($ad?->created_at)->format('Y/m/d') ?? '—' }}</td>
                    <td>{{ jdate($ad?->expires_at)->format('Y/m/d') ?? '—' }}</td>
                    <td>
                        @if($reminder->status === 'pending')
                            <span class="badge badge-warning">در انتظار تأیید</span>
                        @elseif($reminder->status === 'sent')
                            <span class="badge badge-success">ارسال شده</span>
                        @elseif($reminder->status === 'rejected')
                            <span class="badge badge-danger">رد شده</span>
                        @endif
                    </td>
                    <td>
                        @if($reminder->status === 'pending')
                            <div class="quick-actions">
                                <form method="post" action="{{ route('admin.expiry-reminders.approve', $reminder) }}" onsubmit="return confirm('پیامک یادآوری انقضا برای کاربر ارسال شود؟')">
                                    @csrf
                                    <button class="button button-small" type="submit">تأیید و ارسال پیامک</button>
                                </form>
                                <form method="post" action="{{ route('admin.expiry-reminders.reject', $reminder) }}" onsubmit="return confirm('این یادآوری رد شود؟')">
                                    @csrf
                                    <button class="button button-small button-danger" type="submit">رد کردن</button>
                                </form>
                            </div>
                        @elseif($reminder->status === 'sent')
                            <small style="color:#6b7280">{{ jdate($reminder->sent_at)->format('Y/m/d H:i') }}</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">یادآوری انقضای آگهی‌ای وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $reminders->links() }}

<style>
.quick-actions{display:flex;gap:.375rem;flex-wrap:wrap;align-items:center;}
.button-small{font-size:.7rem;padding:.25rem .6rem;}
.button-danger{background:#dc2626;color:#fff;border:none;}
.badge{display:inline-block;padding:.15rem .5rem;border-radius:999px;font-size:.72rem;font-weight:600;}
.badge-success{background:#dcfce7;color:#166534;}
.badge-warning{background:#fef3c7;color:#92400e;}
.badge-danger{background:#fee2e2;color:#991b1b;}
</style>
@endsection
