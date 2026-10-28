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

            {{-- ─── Paid / free options (same catalog as the guest form) ─── --}}
            <div class="form-section-title wide">بسته آگهی / انتخاب خدمات</div>
            <div class="choice-box wide">
                @foreach($tariffs as $tariff)
                    <label class="check service-option">
                        <input type="checkbox" name="services[]" value="{{ $tariff->id }}" data-price="{{ $tariff->price }}" data-is-free="{{ $tariff->code === 'FREE_30' ? '1' : '0' }}" @checked($tariff->code==='FREE_30') onchange="handleServiceClick(this)">
                        <span>
                            <strong>{{ $tariff->title }}</strong>
                            @if($tariff->description)<span class="muted">{{ $tariff->description }}</span>@endif
                            <span class="service-price">{{ $tariff->price===0 ? 'رایگان' : to_persian_digits(number_format($tariff->price / 10)).' تومان' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="total-price-section wide">
                <div class="total-price-box">
                    <span>جمع مبلغ:</span>
                    <span id="total-price" class="total-price-amount">۰ تومان</span>
                </div>
            </div>
            <style>
            .total-price-section{margin-top:1rem;padding:1rem;background:#f0f1fa;border:1px solid #c6c7ee;border-radius:.5rem}
            .total-price-box{display:flex;justify-content:space-between;align-items:center;font-size:1rem;font-weight:700;color:#3e4095}
            .total-price-amount{font-size:1.25rem;color:#0c8a4d}
            </style>
            <script>
            // Free tariff and paid tariffs are mutually exclusive.
            function handleServiceClick(checkbox){
                var isFree=checkbox.getAttribute('data-is-free')==='1';
                if(checkbox.checked&&isFree){
                    document.querySelectorAll('input[name="services[]"]').forEach(function(cb){
                        if(cb.getAttribute('data-is-free')!=='1'&&cb!==checkbox){cb.checked=false;}
                    });
                }else if(checkbox.checked&&!isFree){
                    document.querySelectorAll('input[name="services[]"]').forEach(function(cb){
                        if(cb.getAttribute('data-is-free')==='1'&&cb!==checkbox){cb.checked=false;}
                    });
                }
                calculateTotal();
            }
            function calculateTotal(){
                var total=0;
                document.querySelectorAll('input[name="services[]"]:checked').forEach(function(checkbox){
                    total+=parseInt(checkbox.getAttribute('data-price'))||0;
                });
                var totalInToman=Math.round(total/10);
                var totalElement=document.getElementById('total-price');
                if(totalElement){
                    totalElement.textContent=totalInToman===0?'۰ تومان':toFaDigits(numberFormat(totalInToman))+' تومان';
                }
            }
            function numberFormat(num){
                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g,',');
            }
            function toFaDigits(value){
                return String(value).replace(/[0-9]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[+d];});
            }
            calculateTotal();
            </script>

            <label class="wide file-upload-area">
                <span class="file-upload-label">تصاویر (حداکثر ۵ فایل، JPG/PNG/WebP)</span>
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple id="ad-images-input" hidden>
                <span class="file-upload-btn" onclick="document.getElementById('ad-images-input').click()">انتخاب فایل</span>
                <span class="file-upload-name" id="ad-images-name">فایلی انتخاب نشده</span>
            </label>
            <style>
            .file-upload-area{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;cursor:pointer}
            .file-upload-btn{background:#4b4dbe;color:#fff;padding:.4rem 1rem;border-radius:.5rem;font-size:.85rem;cursor:pointer;display:inline-block}
            .file-upload-btn:hover{background:#3e4095}
            .file-upload-name{color:#6b7280;font-size:.8rem}
            </style>
            <script>
            document.getElementById('ad-images-input').addEventListener('change',function(){
                var n=this.files.length;
                document.getElementById('ad-images-name').textContent=n?n+' فایل انتخاب شد':'فایلی انتخاب نشده';
            });
            </script>
        </div>
        <button class="button">ثبت و ارسال برای تأیید</button>
    </form>
</section>
@endsection
