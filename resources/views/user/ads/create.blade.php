@extends('layouts.app',['title'=>'ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<section class="form-page">
    <h1>ثبت آگهی</h1>
    <p class="lead">آگهی پس از بررسی مدیر منتشر خواهد شد. از ثبت اطلاعات حساس خودداری کنید.</p>
    <form method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <label class="wide">عنوان آگهی
                <input name="title" value="{{ old('title') }}" maxlength="300" required>
            </label>
            <label class="wide">توضیحات
                <textarea name="description" maxlength="6000" rows="8" required>{{ old('description') }}</textarea>
            </label>
            <div class="wide">
                <x-category-modal :categories="$categories" name="category_id" :selected-category-id="old('category_id')" />
            </div>
            <label>شهر
                <select name="city_id" required>
                    <option value="">انتخاب کنید</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected(old('city_id')==$city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </label>
            <label>قیمت (ریال)
                <input type="number" name="price" min="0" value="{{ old('price') }}">
            </label>
            <label>نام و نام خانوادگی
                <input name="full_name" value="{{ old('full_name', auth()->user()->name) }}">
            </label>
            <label>نام کسب‌وکار
                <input name="business_name" value="{{ old('business_name') }}">
            </label>
            <label>موبایل اصلی
                <input name="mobile_1" value="{{ old('mobile_1', auth()->user()->mobile) }}" inputmode="numeric" required>
            </label>
            <label>موبایل همراه (تکراری برای تماس)
                <input name="hamrah_1" value="{{ old('hamrah_1', auth()->user()->mobile) }}" inputmode="numeric">
            </label>
            <label>تلفن ثابت ثبت‌کننده
                <input name="sobit_1" value="{{ old('sobit_1') }}" inputmode="numeric">
            </label>
            <label>موبایل دوم
                <input name="mobile_2" value="{{ old('mobile_2') }}" inputmode="numeric">
            </label>
            <label>تلفن
                <input name="phone_1" value="{{ old('phone_1') }}">
            </label>
            <label>ایمیل
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}">
            </label>
            <label class="wide">آدرس
                <input name="address" value="{{ old('address') }}" maxlength="500">
            </label>
            <label class="wide">کلمات کلیدی (اختیاری)
                <input type="hidden" name="keywords_json" id="keywords-json" value="{{ old('keywords_json', is_array(old('keywords')) ? json_encode(old('keywords')) : '') }}">
            </label>
            <label class="wide">تصاویر (حداکثر ۵ فایل، JPG/PNG/WebP)
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            </label>
        </div>
        <button class="button">ثبت و ارسال برای تأیید</button>
    </form>
</section>
@endsection
