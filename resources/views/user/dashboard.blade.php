@extends('layouts.app', ['title' => 'پنل کاربری | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
<div class="section-heading">
    <div>
        <h1>آگهی‌های من</h1>
        <p class="muted">وضعیت، بازدید و عملیات مجاز آگهی‌ها را مدیریت کنید.</p>
    </div>
    <a class="button" href="{{ route('user.ads.create') }}">ثبت آگهی</a>
</div>

<div class="dashboard-links">
    <a href="{{ route('user.payments.index') }}">پرداخت‌ها و تمدید</a>
    <a href="{{ route('user.tickets.index') }}">تیکت‌ها</a>
    <a href="{{ route('user.profile.edit') }}">پروفایل</a>
</div>

<div class="tabs">
    <a class="@if(!$status) active @endif" href="{{ route('user.dashboard') }}">همه</a>
    @foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item)
        <a class="@if($status === $item->value) active @endif" href="{{ route('user.dashboard', ['status' => $item->value]) }}">{{ $item->label() }}</a>
    @endforeach
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>کد</th>
                <th>شهر</th>
                <th>بازدید</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ads as $ad)
                <tr>
                    <td>{{ $ad->title }}</td>
                    <td>{{ $ad->code }}</td>
                    <td>{{ $ad->city?->name }}</td>
                    <td>{{ number_format($ad->views_count) }}</td>
                    <td><span class="badge">{{ $ad->status->label() }}</span></td>
                    <td>
                        <div class="row-actions">
                            @if(!in_array($ad->status, [\App\Domains\Ads\Enums\AdStatus::Expired, \App\Domains\Ads\Enums\AdStatus::Deleted], true))
                                <a href="{{ route('user.ads.edit', $ad) }}">ویرایش</a>
                            @endif
                            @if($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired)
                                <a href="{{ route('user.payments.index') }}">تمدید</a>
                            @endif
                            @if($ad->status === \App\Domains\Ads\Enums\AdStatus::NeedsPermit)
                                <form method="post" action="{{ route('user.ads.permits.store', $ad) }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="image" accept="image/*">
                                    <button class="link-button">ارسال مجوز</button>
                                </form>
                            @endif
                            @if($ad->status !== \App\Domains\Ads\Enums\AdStatus::Deleted)
                                <form method="post" action="{{ route('user.ads.destroy', $ad) }}" onsubmit="return confirm('آگهی حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="link-button danger">حذف</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">آگهی‌ای وجود ندارد.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
{{ $ads->links() }}
@endsection
