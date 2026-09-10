@extends('layouts.app',['title'=>'آگهی‌های ناموفق | '.config('app.name'),'robots'=>'noindex, nofollow'])

@section('content')
<div class="page-top">
    <div>
        <h1>آگهی‌های ناموفق</h1>
        <p>آگهی‌هایی که کاربر اطلاعات آن را وارد کرده ولی مرحله ثبت‌نام/ورود را تکمیل نکرده است.</p>
    </div>
    <a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<form class="filter-form" method="get" style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:end;">
    <label class="field-label" style="min-width:150px;">وضعیت
        <select name="status">
            <option value="">همه</option>
            <option value="pending" @selected($status==='pending')>در انتظار</option>
            <option value="completed" @selected($status==='completed')>تکمیل شده</option>
            <option value="expired" @selected($status==='expired')>منقضی شده</option>
        </select>
    </label>
    <button class="button" type="submit">فیلتر</button>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>تاریخ ایجاد</th>
                <th>عنوان آگهی</th>
                <th>موبایل</th>
                <th>شهر</th>
                <th>وضعیت</th>
                <th>انقضا</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingAds as $pending)
                <tr>
                    <td>{{ jdate($pending->created_at) }}</td>
                    <td>{{ $pending->ad_data['title'] ?? '—' }}</td>
                    <td dir="ltr">{{ $pending->ad_data['mobile_1'] ?? '—' }}</td>
                    <td>{{ \App\Models\City::find($pending->ad_data['city_id'])->name ?? '—' }}</td>
                    <td>
                        <span class="badge @if($pending->status==='completed')badge-success @elseif($pending->status==='expired')badge-danger @endif">
                            @if($pending->status==='pending') در انتظار
                            @elseif($pending->status==='completed') تکمیل شده
                            @else منقضی شده
                            @endif
                        </span>
                    </td>
                    <td>{{ jdate($pending->expires_at) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;">موردی برای نمایش وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $pendingAds->links() }}
@endsection
