@extends('layouts.app',['title'=>'مدیریت آگهی‌ها | '.config('app.name'),'robots'=>'noindex, nofollow'])

@section('content')
<div class="screenshot-preview" hidden id="screenshot-preview">
    <img id="screenshot-preview-img" src="" alt="نمایش تصویر آگهی" style="max-width:90vw;max-height:70vh;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.15);">
</div>
<div class="page-top">
    <div>
        <h1>مدیریت آگهی‌ها</h1>
        <p>تمام آگهی‌ها (ثبت‌شده و ناموفق) و کد دیتابیس، تصویر و صفحه‌نمایش در یک صفحه.</p>
        <p class="muted">هر صفحه ۱۵ ردیف نمایش داده می‌شود؛ بقیه در صفحه‌های بعدی.</p>
    </div>
    <a class="button button-outline" href="{{ route('admin.dashboard') }}">بازگشت به داشبورد</a>
</div>

<form class="filter-form" method="get" style="display:flex;gap:.75rem 1rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:end;">
    <label class="field-label" style="min-width:220px;flex:1 1 220px;">جست‌وجو
        <input name="q" value="{{ $term }}" placeholder="کد، موبایل یا عنوان">
        <span class="field-hint">جست‌وجوی کد، موبایل یا عنوان با exact/prefix/contains.</span>
    </label>
    <label class="field-label" style="min-width:150px;flex:1 1 150px;">وضعیت
        <select name="status">
            <option value="">همه</option>
            @foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item)
                <option value="{{ $item->value }}" @selected($status===$item->value)>{{ $item->label() }}</option>
            @endforeach
            <option value="pending" @selected($status==='pending')>⏳ ناموفق (در انتظار)</option>
            <option value="expired_pending" @selected($status==='expired_pending')>❌ ناموفق (منقضی)</option>
        </select>
    </label>
    <label class="field-label" style="min-width:150px;flex:1 1 150px;">نوع
        <select name="type">
            <option value="">همه</option>
            <option value="registered" @selected($type==='registered')>کاربر ثبت‌نام‌شده</option>
            <option value="guest" @selected($type==='guest')>کاربر مهمان</option>
        </select>
    </label>
    <div class="filter-actions" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
        <button class="button" type="submit">اعمال فیلتر</button>
        @if($term || $status || $type)
            <a class="link-button" href="{{ route('admin.ads.index') }}">پاک‌سازی فیلترها</a>
        @endif
    </div>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>تاریخ ثبت</th>
                <th>تاریخ بروزرسانی</th>
                <th>عنوان</th>
                <th>کد دیتابیس</th>
                <th>نویسنده (موبایل)</th>
                <th>شهر</th>
                <th>بازدید</th>
                <th>وضعیت</th>
                <th>نمایش تصویر</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paginated as $index => $item)
                @php($rowNumber = $paginated->firstItem() + $index)
                @if($item['type'] === 'registered')
                    @php($ad = $item['model'])
                    <tr>
                        <td>{{ $rowNumber }}</td>
                        <td>{{ jdate($ad->created_at) }}</td>
                        <td>{{ $ad->updated_at && $ad->updated_at->gt($ad->created_at) ? jdate($ad->updated_at) : '—' }}</td>
                        <td>
                            <a href="{{ $ad->publicUrl() }}" class="font-medium" style="color:inherit;text-decoration:none;" dir="rtl">
                                {{ $ad->title }}
                            </a>
                        </td>
                        <td dir="ltr"><span dir="ltr">{{ $ad->code }}</span></td>
                        <td dir="ltr">{{ $ad->user->mobile }}</td>
                        <td>{{ $ad->city?->name ?? '—' }}</td>
                        <td>{{ number_format($ad->views_count) }}</td>
                        <td>
                            <span class="badge {{ $item['status_class'] }}">
                                {{ $item['status_label'] }}
                            </span>
                        </td>
                        <td>
                            @php($firstImage = $ad->images?->first())
                            @if($firstImage)
                                <button type="button" class="screenshot-link" data-src="{{ $firstImage->displayUrl() }}" style="display:inline-flex;" onmouseup="openScreenshot('{{ $firstImage->displayUrl() }}')">
                                    <img src="{{ $firstImage->thumbUrl() }}" alt="تصویر آگهی" class="screenshot-thumb" loading="lazy">
                                    <span class="screenshot-hint">نمایش</span>
                                </button>
                            @else
                                <span class="muted">بدون تصویر</span>
                            @endif
                        </td>
                        <td>
                            <form method="post" action="{{ route('admin.ads.transition',$ad) }}" style="display:flex;gap:.375rem;align-items:center;flex-wrap:wrap;">
                                @csrf @method('PATCH')
                                <select name="status" style="min-width:100px;font-size:.75rem;border:1px solid #e5e7eb;border-radius:6px;padding:.25rem .5rem;">
                                    @foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item2)
                                        <option value="{{ $item2->value }}" @selected($ad->status===$item2)>{{ $item2->label() }}</option>
                                    @endforeach
                                </select>
                                <input name="reason" aria-label="علت" placeholder="علت" style="width:80px;font-size:.75rem;border:1px solid #e5e7eb;border-radius:6px;padding:.25rem .5rem;">
                                <button class="button button-small" type="submit">ذخیره</button>
                            </form>
                        </td>
                    </tr>
                @else
                    @php($pending = $item['model'])
                    <tr style="background:#fef9c3;">
                        <td>{{ jdate($pending->created_at) }}</td>
                        <td>—</td>
                        <td>
                            <span style="font-weight:600;">{{ $pending->ad_data['title'] ?? '—' }}</span>
                            <small class="badge badge-warning" style="font-size:.65rem;">ناموفق</small>
                        </td>
                        <td dir="ltr">{{ $pending->ad_data['mobile_1'] ?? '—' }}</td>
                        <td>{{ \App\Models\City::find($pending->ad_data['city_id'])->name ?? '—' }}</td>
                        <td>—</td>
                        <td>
                            <span class="badge {{ $item['status_class'] }}">
                                {{ $item['status_label'] }}
                            </span>
                        </td>
                        <td>
                            @php($firstPendingImage = $pending->images?->first())
                            @if($firstPendingImage)
                                <button type="button" class="screenshot-link" data-src="{{ $firstPendingImage->displayUrl() }}" style="display:inline-flex;" onmouseup="openScreenshot('{{ $firstPendingImage->displayUrl() }}')">
                                    <img src="{{ $firstPendingImage->thumbUrl() }}" alt="تصویر آگهی" class="screenshot-thumb" loading="lazy">
                                    <span class="screenshot-hint">نمایش</span>
                                </button>
                            @else
                                <span class="muted">بدون تصویر</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem;align-items:center;flex-wrap:wrap;">
                                @if($pending->status === 'pending')
                                    <form method="post" action="{{ route('admin.pending-ads.finalize', $pending) }}" style="display:inline;" onsubmit="return confirm('آیا از ثبت این آگهی مطمئن هستید؟')">
                                        @csrf
                                        <button class="button button-small" type="submit" style="font-size:.75rem;">ثبت آگهی</button>
                                    </form>
                                @endif

                                <form method="post" action="{{ route('admin.pending-ads.status', $pending) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <select name="status" style="font-size:.7rem;border:1px solid #e5e7eb;border-radius:6px;padding:.2rem .3rem;">
                                        <option value="pending" @selected($pending->status==='pending')>در انتظار</option>
                                        <option value="completed" @selected($pending->status==='completed')>تکمیل شده</option>
                                        <option value="expired" @selected($pending->status==='expired')>منقضی شده</option>
                                    </select>
                                </form>

                                <form method="post" action="{{ route('admin.pending-ads.destroy', $pending) }}" style="display:inline;" onsubmit="return confirm('آیا از حذف این آگهی مطمئن هستید؟')">
                                    @csrf @method('DELETE')
                                    <button class="button button-small button-danger" type="submit" style="font-size:.7rem;background:#dc2626;color:#fff;border:none;padding:.2rem .5rem;border-radius:6px;cursor:pointer;">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="11" style="text-align:center;padding:2rem;">موردی برای نمایش وجود ندارد.</td></tr>
            @endforelse
        </tbody>        </table>
</div>{{ $paginated->links() }}
@endsection
