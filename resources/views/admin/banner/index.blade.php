@extends('layouts.app',['title'=>'مدیریت بنر | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<div class="section-heading"><div><h1>بنر صفحهٔ اصلی</h1><p class="muted">تصویر یا فایل HTML دلخواه را برای نمایش در بالای صفحهٔ اصلی بارگذاری کنید.</p></div><a class="button button-outline back-button" href="{{ route('admin.dashboard') }}">داشبورد</a></div>

<section class="panel">
    <h2>بارگذاری بنر</h2>
    <form method="post" action="{{ route('admin.banner.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        <div>
            <label for="banner-file">فایل بنر (تصویر یا HTML — حداکثر ۴ مگابایت)
                <span class="file-upload-wrap">
                    <input id="banner-file" type="file" name="file" accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.html,.htm" required>
                    <label class="file-upload-btn" for="banner-file">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        انتخاب فایل
                    </label>
                    <span class="file-upload-name" id="banner-file-name">فایلی انتخاب نشده</span>
                </span>
            </label>
        </div>
        <div><button class="button">بارگذاری و فعال‌سازی</button></div>
    </form>
    @if(is_array($banner))
        <div style="margin-top:1rem;display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
            <span>وضعیت: <strong>{{ ($banner['is_active'] ?? false) ? 'فعال' : 'غیرفعال' }}</strong> · نوع: {{ $banner['type'] === 'html' ? 'HTML' : 'تصویر' }}</span>
            <form method="post" action="{{ route('admin.banner.toggle') }}">@csrf @method('PATCH')<button class="button button-outline">{{ ($banner['is_active'] ?? false) ? 'غیرفعال‌کردن' : 'فعال‌کردن' }}</button></form>
            <form method="post" action="{{ route('admin.banner.destroy') }}">@csrf @method('DELETE')<button class="link-button">حذف بنر</button></form>
        </div>
    @else
        <p class="muted" style="margin-top:1rem;">در حال حاضر بنری ثبت نشده است.</p>
    @endif
</section>

@if($bannerUrl)
<section>
    <h2>پیش‌نمایش فعلی</h2>
    <div class="panel">
        @if(($banner['type'] ?? null) === 'image')
            <img src="{{ $bannerUrl }}" alt="پیش‌نمایش بنر" style="max-width:100%;height:auto;border-radius:.5rem;">
        @else
            <iframe src="{{ $bannerUrl }}" title="پیش‌نمایش بنر" style="width:100%;height:320px;border:1px solid #e2e8f0;border-radius:.5rem;"></iframe>
        @endif
    </div>
</section>
@endif

<script>
document.getElementById('banner-file').addEventListener('change', function () {
    var name = document.getElementById('banner-file-name');
    if (name) name.textContent = this.files && this.files.length ? this.files[0].name : 'فایلی انتخاب نشده';
});
</script>
@endsection
