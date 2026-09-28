@extends('layouts.app', ['title' => 'مدیریت آگهی‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
@include('admin._toolbar')
<div class="screenshot-preview" hidden id="screenshot-preview">
    <img id="screenshot-preview-img" src="" alt="نمایش تصویر آگهی" style="max-width:90vw;max-height:70vh;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.15);">
</div>

<div class="page-top">
    <div>
        <h1>مدیریت آگهی‌ها</h1>
        <p>تمام آگهی‌ها (ثبت‌شده و ناموفق) و کد دیتابیس، تصویر و صفحه‌نمایش در یک صفحه.</p>

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
            <input type="text" name="from_date_display" class="jalali-date-input" data-jalali-input="from_date" value="{{ $from_date ? jdate($from_date) : '' }}" autocomplete="off" placeholder="۱۴۰۳/۰۱/۰۱">
            <input type="hidden" name="from_date" value="{{ $from_date }}">
        </label>

        <label class="field-label">
            <span>تا تاریخ</span>
            <input type="text" name="to_date_display" class="jalali-date-input" data-jalali-input="to_date" value="{{ $to_date ? jdate($to_date) : '' }}" autocomplete="off" placeholder="۱۴۰۳/۱۲/۳۰">
            <input type="hidden" name="to_date" value="{{ $to_date }}">
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
            <a class="filter-chip" role="listitem" href="{{ route('admin.ads.index', collect(request()->query())->except($chip['key'])->all()) }}" aria-label="حذف {{ $chip['label'] }}">
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
                            <div style="display:flex;gap:.375rem;align-items:center;flex-wrap:wrap;">
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
                                <button type="button" class="ad-edit-toggle" data-edit-toggle="{{ $ad->id }}" title="ویرایش کامل مشخصات آگهی">✏️ ویرایش</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="ad-edit-row" id="ad-edit-row-{{ $ad->id }}" hidden>
                        <td colspan="11">
                            <form method="post" action="{{ route('admin.ads.edit', $ad) }}" class="ad-edit-form">
                                @csrf @method('PATCH')
                                <h4 class="ad-edit-title">ویرایش آگهی «{{ $ad->title }}» — کد {{ $ad->code }}</h4>
                                <div class="ad-edit-grid">
                                    <label class="field-label ad-edit-span2">
                                        <span>عنوان *</span>
                                        <input name="title" required maxlength="300" value="{{ old('title', $ad->title) }}">
                                    </label>
                                    <label class="field-label ad-edit-span2">
                                        <span>توضیحات *</span>
                                        <textarea name="description" rows="4" required maxlength="6000">{{ old('description', $ad->description) }}</textarea>
                                    </label>
                                    <label class="field-label">
                                        <span>قیمت (خالی = توافقی)</span>
                                        <input type="number" name="price" min="0" value="{{ old('price', $ad->price) }}">
                                    </label>
                                    <label class="field-label">
                                        <span>دسته‌بندی *</span>
                                        <select name="category_id" required>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" @selected((int) old('category_id', $ad->category_id) === (int) $cat->id)>{{ $cat->title }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="field-label">
                                        <span>شهر *</span>
                                        {{-- Options are lazily cloned from the filter's city list by JS to keep the DOM light. --}}
                                        <select name="city_id" required data-city-select="{{ $ad->city_id }}">
                                            <option value="">انتخاب شهر</option>
                                        </select>
                                    </label>
                                    <label class="field-label">
                                        <span>تاریخ انقضا (میلادی)</span>
                                        <input type="date" name="expires_at" value="{{ $ad->expires_at?->format('Y-m-d') }}">
                                    </label>
                                    <label class="field-label"><span>موبایل ۱</span><input name="mobile_1" value="{{ old('mobile_1', $ad->mobile_1) }}"></label>
                                    <label class="field-label"><span>موبایل ۲</span><input name="mobile_2" value="{{ old('mobile_2', $ad->mobile_2) }}"></label>
                                    <label class="field-label"><span>تلفن ۱</span><input name="phone_1" value="{{ old('phone_1', $ad->phone_1) }}"></label>
                                    <label class="field-label"><span>تلفن ۲</span><input name="phone_2" value="{{ old('phone_2', $ad->phone_2) }}"></label>
                                    <label class="field-label"><span>ایمیل</span><input type="email" name="email" value="{{ old('email', $ad->email) }}"></label>
                                    <label class="field-label"><span>نام و نام خانوادگی</span><input name="full_name" value="{{ old('full_name', $ad->full_name) }}"></label>
                                    <label class="field-label"><span>نام کسب‌وکار</span><input name="business_name" value="{{ old('business_name', $ad->business_name) }}"></label>
                                    <label class="field-label ad-edit-span2"><span>آدرس</span><input name="address" value="{{ old('address', $ad->address) }}"></label>
                                </div>
                                <div class="ad-edit-flags">
                                    <label class="check"><input type="checkbox" name="is_featured" value="1" @checked($ad->is_featured)> <span>ویژه</span></label>
                                    <label class="check"><input type="checkbox" name="is_urgent" value="1" @checked($ad->is_urgent)> <span>فوری</span></label>
                                    <label class="check"><input type="checkbox" name="is_colored" value="1" @checked($ad->is_colored)> <span>رنگی</span></label>
                                    <label class="check"><input type="checkbox" name="auto_ladder" value="1" @checked($ad->auto_ladder)> <span>نردبان خودکار</span></label>
                                    <label class="check"><input type="checkbox" name="show_mobile_1" value="1" @checked($ad->show_mobile_1)> <span>نمایش موبایل</span></label>
                                </div>
                                <div class="ad-edit-actions">
                                    <button class="button button-small" type="submit">ذخیره تغییرات</button>
                                    <button type="button" class="ad-edit-toggle" data-edit-toggle="{{ $ad->id }}">انصراف</button>
                                </div>
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
/* ─────────────────────────  Inline Ad Edit Row  ───────────────────────── */
.ad-edit-toggle{
    background:transparent;border:0;cursor:pointer;
    color:#0d9488;font-size:.78rem;font-weight:600;
    padding:.25rem .4rem;border-radius:.375rem;
}
.ad-edit-toggle:hover{background:#f0fdfa;color:#0f766e;}
.ad-edit-row > td{background:#f0fdfa;border-top:2px solid #99f6e4;padding:1rem;}
.ad-edit-form{display:flex;flex-direction:column;gap:.75rem;}
.ad-edit-title{margin:0;font-size:.9rem;color:#0f766e;}
.ad-edit-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.6rem 1rem;}
.ad-edit-grid .field-label input,
.ad-edit-grid .field-label select,
.ad-edit-grid .field-label textarea{
    width:100%;padding:.375rem .5rem;border:1px solid #d1d5db;border-radius:.375rem;
    font-size:.85rem;font-family:inherit;color:#111827;background:#fff;
}
.ad-edit-span2{grid-column:1 / -1;}
.ad-edit-flags{display:flex;gap:1rem;flex-wrap:wrap;}
.ad-edit-actions{display:flex;gap:.5rem;align-items:center;}
@media (max-width:640px){.ad-edit-span2{grid-column:auto;}}
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
        var advancedSet = (city && city.value) || (document.querySelector('input[name=from_date]') && document.querySelector('input[name=from_date]').value) || (document.querySelector('input[name=to_date]') && document.querySelector('input[name=to_date]').value);
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

    // Toggle the inline ad-edit rows. The city <select> of each edit form is
    // lazily populated from the filter's city list (already in the DOM once)
    // to avoid duplicating the full city list for every table row.
    document.querySelectorAll('[data-edit-toggle]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = btn.getAttribute('data-edit-toggle');
            var row = document.getElementById('ad-edit-row-' + id);
            if (!row) return;
            row.hidden = !row.hidden;
            var citySel = row.querySelector('select[data-city-select]');
            if (!row.hidden && citySel && !citySel.dataset.populated){
                var source = document.getElementById('filter-city');
                if (source){
                    var current = citySel.getAttribute('data-city-select');
                    Array.prototype.slice.call(source.options).forEach(function(opt){
                        if (!opt.value) return;
                        var o = document.createElement('option');
                        o.value = opt.value;
                        o.textContent = opt.textContent;
                        if (opt.value === current) o.selected = true;
                        citySel.appendChild(o);
                    });
                    citySel.dataset.populated = '1';
                }
            }
        });
    });

    // Initialize Persian datepicker on filter inputs.
    document.querySelectorAll('.jalali-date-input').forEach(function(el){
        var hiddenName = el.getAttribute('data-jalali-input');
        var hidden = el.parentElement.querySelector('input[name="'+hiddenName+'"]');
        if (typeof $ !== 'undefined' && $.fn.persianDatepicker) {
            $(el).persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                onSelect: function(unix){
                    var d = new Date(unix);
                    var gy = d.getFullYear();
                    var gm = String(d.getMonth()+1).padStart(2,'0');
                    var gd = String(d.getDate()).padStart(2,'0');
                    if (hidden) hidden.value = gy+'-'+gm+'-'+gd;
                }
            });
        }
    });
})();
</script>
@endsection
