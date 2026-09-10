@extends('layouts.app', ['title' => 'مدیریت آگهی‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

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

@php
    // Compute the set of currently-active filter chips so the UI can render
    // them as removable pills below the main filter form.
    $activeChips = collect([]);
    if ($term) $activeChips->push(['key' => 'q', 'label' => 'جست‌وجو: ' . $term]);
    if ($status) {
        $statusLabel = collect($status_options)->firstWhere('value', $status)['label'] ?? $status;
        $activeChips->push(['key' => 'status', 'label' => 'وضعیت: ' . $statusLabel]);
    }
    if ($type) {
        $typeLabel = collect($type_options)->firstWhere('value', $type)['label'] ?? $type;
        $activeChips->push(['key' => 'type', 'label' => 'نوع: ' . $typeLabel]);
    }
    if ($category_id) {
        $cat = $categories->firstWhere('id', $category_id);
        $activeChips->push(['key' => 'category', 'label' => 'دسته: ' . ($cat->title ?? $category_id)]);
    }
    if ($province_id) {
        $prov = $provinces->firstWhere('id', $province_id);
        $activeChips->push(['key' => 'province', 'label' => 'استان: ' . ($prov->name ?? $province_id)]);
    }
    if ($city_id) {
        $ci = $cities->firstWhere('id', $city_id);
        $activeChips->push(['key' => 'city', 'label' => 'شهر: ' . ($ci->name ?? $city_id)]);
    }
    if ($from_date) $activeChips->push(['key' => 'from_date', 'label' => 'از: ' . $from_date]);
    if ($to_date)   $activeChips->push(['key' => 'to_date',   'label' => 'تا: ' . $to_date]);
@endphp

<form class="filter-form filter-form--ads" method="get" aria-label="فیلتر آگهی‌ها">
    <div class="filter-form__row">
        <label class="field-label field-label--grow">
            <span>جست‌وجو</span>
            <input name="q" value="{{ $term }}" placeholder="کد، موبایل یا عنوان" autocomplete="off">
            <span class="field-hint">جست‌وجوی کد، موبایل یا عنوان با exact/prefix/contains.</span>
        </label>

        <label class="field-label">
            <span>وضعیت</span>
            <select name="status" data-filter-select>
                @foreach($status_options as $opt)
                    <option value="{{ $opt['value'] }}" @selected($status === $opt['value'])>{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </label>

        <label class="field-label">
            <span>نوع</span>
            <select name="type" data-filter-select>
                @foreach($type_options as $opt)
                    <option value="{{ $opt['value'] }}" @selected($type === $opt['value'])>{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </label>

        <label class="field-label">
            <span>دسته‌بندی</span>
            <select name="category" data-filter-select>
                <option value="">همهٔ دسته‌ها</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((int)$category_id === (int)$cat->id)>{{ $cat->title }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="filter-form__row filter-form__row--secondary" data-filter-collapse hidden>
        <label class="field-label">
            <span>استان</span>
            <select name="province" id="filter-province" data-filter-select data-cascade-province>
                <option value="">همهٔ استان‌ها</option>
                @foreach($provinces as $prov)
                    <option value="{{ $prov->id }}" @selected((int)$province_id === (int)$prov->id)>{{ $prov->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="field-label">
            <span>شهر</span>
            <select name="city" id="filter-city" data-filter-select data-cascade-city @disabled(empty($province_id))>
                <option value="">همهٔ شهرها</option>
                @foreach($cities as $ci)
                    <option value="{{ $ci->id }}" data-province="{{ $ci->province_id }}" @selected((int)$city_id === (int)$ci->id)>{{ $ci->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="field-label">
            <span>از تاریخ</span>
            <input type="date" name="from_date" value="{{ $from_date }}" autocomplete="off">
        </label>

        <label class="field-label">
            <span>تا تاریخ</span>
            <input type="date" name="to_date" value="{{ $to_date }}" autocomplete="off">
        </label>
    </div>

    <div class="filter-actions filter-actions--ads">
        <button class="button" type="submit">اعمال فیلتر</button>
        <button type="button" class="link-button" data-filter-toggle>
            <span data-filter-toggle-label>فیلترهای پیشرفته ＋</span>
        </button>
        @if($activeChips->isNotEmpty())
            <a class="link-button link-button--muted" href="{{ route('admin.ads.index') }}">پاک‌سازی همه</a>
        @endif
    </div>

    @if($activeChips->isNotEmpty())
    <div class="filter-chips" role="list" aria-label="فیلترهای فعال">
        @foreach($activeChips as $chip)
            <a class="filter-chip" role="listitem" href="{{ route('admin.ads.index', request()->query()->except($chip['key'])) }}" aria-label="حذف {{ $chip['label'] }}">
                <span>{{ $chip['label'] }}</span>
                <span aria-hidden="true">✕</span>
            </a>
        @endforeach
    </div>
    @endif
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
                        <td dir="ltr">{{ $ad->user->mobile ?? $ad->mobile_1 ?? '—' }}</td>
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
                            <form method="post" action="{{ route('admin.ads.transition', $ad) }}" style="display:flex;gap:.375rem;align-items:center;flex-wrap:wrap;">
                                @csrf @method('PATCH')
                                <select name="status" style="min-width:100px;font-size:.75rem;border:1px solid #e5e7eb;border-radius:6px;padding:.25rem .5rem;">
                                    @foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item2)
                                        <option value="{{ $item2->value }}" @selected($ad->status === $item2)>{{ $item2->label() }}</option>
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
                        <td>{{ $rowNumber }}</td>
                        <td>{{ jdate($pending->created_at) }}</td>
                        <td>—</td>
                        <td>
                            <span style="font-weight:600;">{{ $pending->ad_data['title'] ?? '—' }}</span>
                            <small class="badge badge-warning" style="font-size:.65rem;">ناموفق</small>
                        </td>
                        <td dir="ltr">—</td>
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
                                        <option value="pending" @selected($pending->status === 'pending')>در انتظار</option>
                                        <option value="completed" @selected($pending->status === 'completed')>تکمیل شده</option>
                                        <option value="expired" @selected($pending->status === 'expired')>منقضی شده</option>
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
        </tbody>
    </table>
</div>

{{ $paginated->links() }}

<style>
/* ─────────────────────────  Admin Ads Filter UI  ───────────────────────── */
.filter-form--ads{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:.625rem;
    padding:1rem;
    margin-bottom:1.5rem;
    display:flex;
    flex-direction:column;
    gap:.75rem;
}
.filter-form__row{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));
    gap:.75rem 1rem;
    align-items:end;
}
.filter-form__row .field-label--grow{grid-column:1 / -1;}
.filter-form__row--secondary{
    padding-top:.75rem;
    border-top:1px dashed #e5e7eb;
}
.filter-actions--ads{
    display:flex;
    gap:.5rem;
    align-items:center;
    flex-wrap:wrap;
}
.filter-actions--ads .link-button{
    background:transparent;
    border:0;
    color:#0d9488;
    cursor:pointer;
    font-size:.85rem;
    padding:.25rem .5rem;
    text-decoration:none;
}
.filter-actions--ads .link-button--muted{color:#6b7280;}
.filter-chips{
    display:flex;
    flex-wrap:wrap;
    gap:.375rem;
    padding-top:.5rem;
    border-top:1px dashed #e5e7eb;
}
.filter-chip{
    display:inline-flex;
    align-items:center;
    gap:.375rem;
    background:#e0f2fe;
    color:#0369a1;
    border:1px solid #bae6fd;
    border-radius:999px;
    padding:.25rem .75rem;
    font-size:.75rem;
    text-decoration:none;
    transition:background .15s ease;
}
.filter-chip:hover{background:#bae6fd;}
.filter-chip span:last-child{
    font-size:.85rem;
    line-height:1;
    opacity:.7;
}
.field-label span{display:block;font-size:.75rem;color:#374151;margin-bottom:.25rem;font-weight:600;}
.field-label select,
.field-label input{
    width:100%;
    padding:.375rem .5rem;
    border:1px solid #d1d5db;
    border-radius:.375rem;
    background:#fff;
    font-size:.85rem;
    color:#111827;
}
.field-hint{display:block;font-size:.7rem;color:#9ca3af;margin-top:.25rem;}
@media (max-width: 640px){
    .filter-form__row{grid-template-columns:1fr;}
    .filter-form__row .field-label--grow{grid-column:auto;}
}
</style>

<script>
(function(){
    // Cascade province -> city in the filter form.
    var prov = document.getElementById('filter-province');
    var city = document.getElementById('filter-city');
    if (prov && city){
        var cityOptions = Array.prototype.slice.call(city.querySelectorAll('option[data-province]'));
        function syncCity(){
            var pid = prov.value;
            city.disabled = !pid;
            cityOptions.forEach(function(opt){
                opt.hidden = pid && opt.dataset.province !== pid;
            });
            if (pid && city.value){
                var active = cityOptions.find(function(o){return o.value === city.value && !o.hidden;});
                if (!active) city.value = '';
            }
        }
        prov.addEventListener('change', syncCity);
        syncCity();
    }

    // Toggle the advanced filter row.
    var toggle = document.querySelector('[data-filter-toggle]');
    var collapse = document.querySelector('[data-filter-collapse]');
    var label = document.querySelector('[data-filter-toggle-label]');
    if (toggle && collapse){
        // Auto-open advanced row when any advanced field is set.
        var advancedSet = (city && city.value) || (document.querySelector('[name=from_date]') && document.querySelector('[name=from_date]').value) || (document.querySelector('[name=to_date]') && document.querySelector('[name=to_date]').value);
        if (advancedSet){
            collapse.hidden = false;
            if (label) label.textContent = 'فیلترهای پیشرفته －';
        }
        toggle.addEventListener('click', function(){
            collapse.hidden = !collapse.hidden;
            if (label) label.textContent = collapse.hidden ? 'فیلترهای پیشرفته ＋' : 'فیلترهای پیشرفته －';
        });
    }

    // Auto-submit the form when a select changes (faster UX).
    document.querySelectorAll('[data-filter-select]').forEach(function(sel){
        sel.addEventListener('change', function(){
            sel.form.submit();
        });
    });
})();
</script>
@endsection
