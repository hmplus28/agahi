@extends('layouts.app', ['title' => 'گزارش‌های تخلف | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<h1>گزارش‌های تخلف</h1>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>دلیل</th>
                <th>توضیح</th>
                <th>وضعیت</th>
                <th>اقدام سریع</th>
                <th>تغییر وضعیت</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
                @php($ad = $report->ad)
                <tr>
                    <td>
                        @if($ad)
                            <a href="{{ $ad->publicUrl() }}" target="_blank" rel="noopener">{{ $ad->title }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $report->reason }}</td>
                    <td>{{ $report->description ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $report->status === 'resolved' ? 'success' : ($report->status === 'reviewing' ? 'warning' : '') }}">
                            {{ $report->status }}
                        </span>
                    </td>
                    <td>
                        <div class="quick-actions">
                            @if($ad && $ad->status !== \App\Domains\Ads\Enums\AdStatus::Deleted)
                                <form method="post" action="{{ route('admin.reports.deleteAd', $report) }}" onsubmit="return confirm('آگهی حذف شود؟')">
                                    @csrf
                                    <button class="button button-small button-danger" type="submit">حذف آگهی</button>
                                </form>
                            @endif

                            @if($templates->isNotEmpty() && $ad?->user)
                                <form method="post" action="{{ route('admin.reports.sms', $report) }}">
                                    @csrf
                                    <select name="template_id" required>
                                        @foreach($templates as $tpl)
                                            <option value="{{ $tpl['id'] }}">{{ $tpl['label'] }}</option>
                                        @endforeach
                                    </select>
                                    <button class="button button-small" type="submit">پیامک به کاربر</button>
                                </form>
                            @endif

                            @if($ad?->user)
                                <form method="post" action="{{ route('admin.reports.ticket', $report) }}">
                                    @csrf
                                    <button class="button button-small" type="submit">تیکت به کاربر</button>
                                </form>
                            @endif
                        </div>
                    </td>
                    <td>
                        <form method="post" action="{{ route('admin.reports.update', $report) }}">
                            @csrf @method('PATCH')
                            <select name="status">
                                <option value="new" @selected($report->status === 'new')>جدید</option>
                                <option value="reviewing" @selected($report->status === 'reviewing')>در حال بررسی</option>
                                <option value="resolved" @selected($report->status === 'resolved')>حل‌شده</option>
                                <option value="rejected" @selected($report->status === 'rejected')>ردشده</option>
                            </select>
                            <input name="admin_note" placeholder="یادداشت" value="{{ $report->admin_note }}">
                            <button class="button button-small">ذخیره</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">گزارشی وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $reports->links() }}

<style>
.quick-actions{display:flex;gap:.375rem;flex-wrap:wrap;align-items:center;}
.quick-actions select{font-size:.7rem;padding:.2rem .3rem;border:1px solid #e5e7eb;border-radius:6px;}
.button-small{font-size:.7rem;padding:.2rem .5rem;}
.button-danger{background:#dc2626;color:#fff;border:none;}
</style>
@endsection
