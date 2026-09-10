@extends('layouts.app', ['title' => 'مدیریت داده‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'])

@section('content')
@php
    $labels = ['categories' => 'دسته‌بندی', 'countries' => 'کشور', 'provinces' => 'استان', 'cities' => 'شهر', 'tariffs' => 'تعرفه', 'forbidden-words' => 'لغت غیرمجاز'];
    $importHints = [
        'countries' => 'ستون‌ها: نام کشور، slug (اختیاری)',
        'provinces' => 'ستون‌ها: نام کشور، نام استان، slug (اختیاری)',
        'cities'    => 'ستون‌ها: نام استان، نام شهر، slug (اختیاری)',
    ];
@endphp

<div class="section-heading">
    <div>
        <h1>مدیریت {{ $labels[$type] }}</h1>
        <p class="muted">داده‌های عمومی و تنظیمات قابل مدیریت سامانه.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">داشبورد</a>
</div>

@if(!empty($importable))
<section class="panel import-panel">
    <h2>ورود از اکسل</h2>
    <div class="import-row">
        <form method="post" action="{{ route('admin.catalog.import', $type) }}" enctype="multipart/form-data" class="import-form">
            @csrf
            <label class="field-label">
                <span>فایل اکسل (.xlsx)</span>
                <input type="file" name="file" accept=".xlsx,.xls" required>
            </label>
            <button class="button" type="submit">ورود از اکسل</button>
        </form>
        <a class="link-button" href="{{ route('admin.catalog.sample', $type) }}">دانلود فایل نمونه</a>
    </div>
    @if(isset($importHints[$type]))
    <p class="field-hint">{{ $importHints[$type] }}</p>
    @endif
</section>
@endif

<section class="panel">
    <h2>افزودن رکورد</h2>
    <form method="post" action="{{ route('admin.catalog.store', $type) }}" class="form-grid">
        @csrf
        @include('admin.catalog.fields')
        <div><button class="button">ثبت</button></div>
    </form>
</section>

<section>
    <h2>رکوردها</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>شناسه / slug</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->title ?? $record->name ?? $record->word }}</td>
                        <td>{{ $record->slug ?? $record->code ?? $record->normalized_word }}</td>
                        <td>{{ $record->is_active ? 'فعال' : 'غیرفعال' }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.catalog.toggle', [$type, $record->id]) }}">
                                @csrf @method('PATCH')
                                <button class="link-button">{{ $record->is_active ? 'غیرفعال‌کردن' : 'فعال‌کردن' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">رکوردی وجود ندارد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</section>

<style>
.import-panel{margin-bottom:1.5rem;}
.import-row{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.import-form{display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;}
.field-label span{display:block;font-size:.75rem;color:#374151;margin-bottom:.25rem;font-weight:600;}
.field-hint{font-size:.7rem;color:#9ca3af;margin-top:.5rem;}
</style>
@endsection
