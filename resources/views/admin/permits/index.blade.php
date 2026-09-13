@extends('layouts.app', ['title' => 'مجوزها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="page-top">
    <div><h1>مجوزهای آگهی</h1><p class="muted">بررسی و تأیید تصاویر مجوز آپلود‌شده توسط کاربران.</p></div>
    <a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<div class="table-wrap">
    <table class="permits-table">
        <thead>
            <tr>
                <th>آگهی</th>
                <th>شماره مجوز</th>
                <th>صادرکننده</th>
                <th>تصویر مجوز</th>
                <th>تاریخ صدور</th>
                <th>وضعیت</th>
                <th>یادداشت مدیر</th>
                <th>اقدام</th>
            </tr>
        </thead>
        <tbody>
            @forelse($permits as $permit)
                <tr class="permit-row permit-status-{{ $permit->status }}">
                    <td>
                        @if($permit->ad)
                            <a href="{{ $permit->ad->publicUrl() }}" target="_blank" rel="noopener">
                                {{ $permit->ad->title }}
                            </a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td dir="ltr">{{ $permit->permit_number ?: '—' }}</td>
                    <td>{{ $permit->issuer ?: '—' }}</td>
                    <td>
                        @if($permit->image_path)
                            <a href="{{ asset('storage/' . $permit->image_path) }}"
                               target="_blank" rel="noopener"
                               class="permit-image-thumb"
                               title="مشاهده تصویر مجوز">
                                <img src="{{ asset('storage/' . $permit->image_path) }}"
                                     alt="تصویر مجوز آگهی"
                                     loading="lazy"
                                     style="width:80px;height:60px;object-fit:cover;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;">
                            </a>
                            <div style="margin-top:4px;">
                                <a href="{{ asset('storage/' . $permit->image_path) }}"
                                   target="_blank" rel="noopener"
                                   class="link-button link-button--small">
                                    مشاهده کامل
                                </a>
                            </div>
                        @else
                            <span class="muted">تصویری ثبت نشده</span>
                        @endif
                    </td>
                    <td>{{ $permit->issued_at ? jdate($permit->issued_at) : '—' }}</td>
                    <td>
                        <span class="badge permit-status-badge permit-status-{{ $permit->status }}">
                            @switch($permit->status)
                                @case('pending')  در انتظار @break
                                @case('approved')  تأیید شده @break
                                @case('rejected')  رد شده @break
                                @default          {{ $permit->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>
                        @if($permit->admin_note)
                            <span title="{{ $permit->admin_note }}">{{ \Illuminate\Support\Str::limit($permit->admin_note, 40) }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td>
                        <form method="post" action="{{ route('admin.permits.update', $permit) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status">
                                <option value="pending"  @selected($permit->status === 'pending')>در انتظار</option>
                                <option value="approved" @selected($permit->status === 'approved')>تأیید</option>
                                <option value="rejected" @selected($permit->status === 'rejected')>رد</option>
                            </select>
                            <input name="admin_note" placeholder="یادداشت" value="{{ $permit->admin_note }}" style="margin-top:4px;">
                            <button class="button button-small" type="submit">ذخیره</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;">مجوزی وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $permits->links() }}

<style>
.permits-table th,
.permits-table td {
    padding: .75rem;
    vertical-align: top;
}
.permit-row.permit-status-pending  { background: #fef9c3; }
.permit-row.permit-status-approved { background: #f0fdf4; }
.permit-row.permit-status-rejected { background: #fef2f2; }

.permit-status-badge {
    display: inline-block;
    padding: .15rem .5rem;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 600;
}
.permit-status-badge.permit-status-pending  { background: #fef3c7; color: #92400e; }
.permit-status-badge.permit-status-approved { background: #d1fae5; color: #065f46; }
.permit-status-badge.permit-status-rejected { background: #fee2e2; color: #991b1b; }

.link-button--small {
    display: inline-flex;
    align-items: center;
    min-height: 28px;
    padding: 0 8px;
    font-size: .7rem;
}
</style>
@endsection
