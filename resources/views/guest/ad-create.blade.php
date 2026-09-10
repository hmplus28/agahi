@extends('layouts.app',['title'=>'ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')
<section class="form-page">
    <h1>ثبت آگهی</h1>
    <div class="steps-bar" aria-label="مراحل ثبت آگهی">
        <div class="step active"><span class="step-num">۱</span> ثبت آگهی</div>
        <div class="step"><span class="step-num">۲</span> ورود</div>
    </div>
    <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.5rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#0f766e;">
        <strong>آی‌پی شما:</strong> <code dir="ltr" style="background:#e0f2fe;padding:.1rem .4rem;border-radius:.25rem;">{{ $clientIp ?? request()->ip() }}</code>
    </div>
    <form method="post" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <label class="wide">عنوان آگهی <span class="req">*</span>
                <input name="title" value="{{ old('title') }}" maxlength="300" required>
            </label>

            <x-category-modal :categories="$categories" name="category_id" :selected-category-id="old('category_id')" />

            <label class="wide geo-label">موقعیت جغرافیایی</label>

            <label>کشور
                <select name="country_id" id="guest-country" data-cascade-country>
                    <option value="">انتخاب کنید</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" @selected(old('country_id', ($country->slug ?? '') === 'iran' ? $country->id : '')==$country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>استان
                <select name="province_id" id="guest-province" data-cascade-province disabled>
                    <option value="">ابتدا کشور را انتخاب کنید</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" data-country="{{ $province->country_id }}" @selected(old('province_id')==$province->id)>{{ $province->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>شهر <span class="req">*</span>
                <select name="city_id" id="guest-city" data-cascade-city required disabled>
                    <option value="">ابتدا استان را انتخاب کنید</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" data-province="{{ $city->province_id }}" @selected(old('city_id')==$city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>قیمت (تومان)
                <input type="number" name="price" min="0" value="{{ old('price') }}">
            </label>

            <label>نام و نام خانوادگی
                <input name="full_name" value="{{ old('full_name') }}">
            </label>

            <label>نام کسب‌وکار
                <input name="business_name" value="{{ old('business_name') }}">
            </label>

            <label>ایمیل
                <input type="email" name="email" value="{{ old('email') }}">
            </label>

            <label><span>موبایل <span class="req">*</span></span>
                <div class="input-row">
                    <input name="mobile_1" value="{{ old('mobile_1') }}" inputmode="numeric" placeholder="09123456789" required>
                    <label class="check-label"><input type="checkbox" name="show_mobile_1" value="1" @checked(old('show_mobile_1', true))> <span>نمایش در آگهی</span></label>
                </div>
            </label>

            <label>موبایل دوم
                <input name="mobile_2" value="{{ old('mobile_2') }}" inputmode="numeric">
            </label>

            <label>موبایل همراه (تکراری برای تماس)
                <input name="hamrah_1" value="{{ old('hamrah_1') }}" inputmode="numeric" placeholder="09123456789">
            </label>

            <label>تلفن ثابت ثبت‌کننده
                <input name="sobit_1" value="{{ old('sobit_1') }}" inputmode="numeric">
            </label>

            <label>تلفن ثابت
                <input name="phone_1" value="{{ old('phone_1') }}">
            </label>

            <label>تلفن ثابت ۲
                <input name="phone_2" value="{{ old('phone_2') }}">
            </label>

            <label class="wide">توضیحات <span class="req">*</span>
                <textarea name="description" maxlength="6000" rows="8" required>{{ old('description') }}</textarea>
            </label>

            <label class="wide">آدرس
                <input name="address" value="{{ old('address') }}" maxlength="500">
            </label>

            <div class="form-section-title wide">لینک‌ها (اختیاری)</div>
            @php
                $linkTypes = [
                    'site' => 'سایت', 'aparat' => 'ویدیو آپارات', 'eitaa' => 'کانال ایتا',
                    'bale' => 'کانال بله', 'rubika' => 'کانال روبیکا', 'whatsapp' => 'واتس‌آپ',
                    'telegram' => 'تلگرام', 'instagram' => 'اینستاگرام',
                ];
                $oldLinks = old('links', []);
            @endphp
            @for($i = 1; $i <= 5; $i++)
                @php($lk = $oldLinks[$i-1] ?? [])
                <label>لینک {{ $i }}
                    <span class="link-row">
                        <select name="links[{{ $i-1 }}][type]">
                            @foreach($linkTypes as $tKey => $tLabel)
                                <option value="{{ $tKey }}" @selected(($lk['type'] ?? 'site') === $tKey)>{{ $tLabel }}</option>
                            @endforeach
                        </select>
                        <input type="url" name="links[{{ $i-1 }}][url]" value="{{ $lk['url'] ?? '' }}" placeholder="https://example.com" maxlength="500">
                    </span>
                </label>
            @endfor

            <div class="form-section-title wide">کلمات کلیدی (اختیاری)</div>

            <label class="wide">کلمات کلیدی (حداکثر ۱۰ مورد، با Enter یا کاما (,) جدا کنید)
                <div class="keywords-input-wrap" id="keywords-wrap">
                    @if(old('keywords'))
                        @foreach(old('keywords') as $kw)
                            @if(filled($kw))
                                <span class="keyword-tag">{{ $kw }}<button type="button" class="keyword-remove" onclick="this.parentElement.remove()">✕</button></span>
                            @endif
                        @endforeach
                    @endif
                    <input type="text" id="keyword-input" placeholder="کلمه کلیدی را تایپ کنید…" autocomplete="off">
                </div>
                <input type="hidden" name="keywords_json" id="keywords-json" value="{{ old('keywords_json', is_array(old('keywords')) ? json_encode(old('keywords')) : '') }}">
            </label>

            <label class="wide">تصاویر (حداکثر {{ config('agahi.max_images', 5) }} فایل، JPG/PNG/WebP — تصاویر خودکار فشرده می‌شوند)
                <span class="file-upload-wrap">
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple id="guest-images">
                    <label class="file-upload-btn" for="guest-images">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        انتخاب فایل
                    </label>
                    <span class="file-upload-name" id="guest-images-name">فایلی انتخاب نشده</span>
                </span>
                <span class="image-count-info" id="guest-images-count"></span>
                <span class="image-preview" id="guest-images-preview"></span>
            </label>

            <div class="form-section-title wide">معرف (اختیاری)</div>
            <label class="wide">معرف
                <input name="referrer" value="{{ old('referrer') }}" maxlength="160" placeholder="نام کسی که شما را معرفی کرده است">
            </label>

            <div class="form-section-title wide">مجوز (اختیاری — الزامی برای مشاغل دارای مجوز)</div>
            <label>شماره مجوز
                <input name="permit_number" value="{{ old('permit_number') }}" maxlength="160">
            </label>
            <label>مرجع صادر‌کننده مجوز
                <input name="permit_issuer" value="{{ old('permit_issuer') }}" maxlength="160">
            </label>
            <label>تاریخ صدور مجوز (شمسی)
                <input type="text" name="permit_issued_at_display" id="permit-issued-display" value="{{ old('permit_issued_at') ? jdate(old('permit_issued_at')) : '' }}" placeholder="۱۴۰۴/۰۵/۱۵" readonly>
                <input type="hidden" name="permit_issued_at" id="permit-issued" value="{{ old('permit_issued_at') }}">
            </label>
            <label class="wide">تصویر مجوز
                <span class="file-upload-wrap">
                    <input type="file" name="permit_image" accept="image/jpeg,image/png,image/webp" id="guest-permit-image">
                    <label class="file-upload-btn" for="guest-permit-image">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        انتخاب فایل
                    </label>
                    <span class="file-upload-name" id="guest-permit-image-name">فایلی انتخاب نشده</span>
                </span>
            </label>

            <div class="form-section-title wide">بسته آگهی / انتخاب خدمات</div>
            <div class="choice-box wide">
                @foreach($tariffs as $tariff)
                    <label class="check service-option">
                        <input type="checkbox" name="services[]" value="{{ $tariff->id }}" data-price="{{ $tariff->price }}" data-is-free="{{ $tariff->code === 'FREE_30' ? '1' : '0' }}" @checked($tariff->code==='FREE_30') onclick="handleServiceClick(this)">
                        <span>
                            <strong>{{ $tariff->title }}</strong>
                            @if($tariff->description)<span class="muted">{{ $tariff->description }}</span>@endif
                            <span class="service-price">{{ $tariff->price===0 ? 'رایگان' : number_format($tariff->price / 10).' تومان' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="total-price-section wide">
                <div class="total-price-box">
                    <span>جمع مبلغ:</span>
                    <span id="total-price" class="total-price-amount">0 تومان</span>
                </div>
            </div>
        </div>
        <div class="rules-note" role="note">
            <div class="rules-note-header"><strong>قوانین ثبت آگهی</strong></div>
            <div class="rules-note-body">
                <ul>
                    <li>هر آگهی که مغایر با قانون ، شرع ، عرف جامعه یا شبهه دار باشد حذف می گردد</li>
                    <li>آگهی های با تصویر خانم بی حجاب حذف می گردد.</li>
                    <li>آگهی‌هایی با متن تکراری حذف می گردد .</li>
                    <li>در موضوعات ذیل ارسال آگهی ممنوع است : قاچاق زیرلنجی ته لنجی – خرید و فروش حیوانات خانگی- خرید و فروش هر گونه مجوز-کارچاق کنی-شرخری- اجاره کوتاه مدت منزل- اعضای بدن و کلیه- پایان نامه- تتو- تجمع اعتراض- حجامت- خرید فروش طلا دلار- ملک خارج از کشور- خوانندگی- دارو- رقاصی- سگ و گربه- سلاح سرد و گرم- سیگار و مواد مخدر- شبکه هرمی- صیغه و همسریابی- ضمانت و فیش حقوقی- طلاق و مهریه- عکسهای بی حجاب- فارکس و ارز دیجیتال- فالگیری- فروش فاکتور- فلزیاب و گنج یاب- فیش حج- قرعه کشی- کاشت مژه- کاشت ناخن- گرین کارت و اقامت- لاغری- لباس زیر- لیزینگ- ماساژ درمانی- ماهواره- موسیقی- مهاجرت-نیروی کار به خارج- وام و سرمایه گذاری- وی پی ان- ویزا- توریستی- هک- همسریابی</li>
                    <li>در آگهی استخدام دریافت مبلغ از کارجو ممنوع می‌باشد.</li>
                    <li>جهت تائید آگهی تصویر مجوز شغلی در رشته‌های پزشکی، روان‌پزشکی، دارو ، خوراکی ، آشامیدنی ، سلامتی و و مرتبط را در قسمت بالا مجوزها ارسال نمائید.</li>
                    <li>در صورت سوال با شماره 02166248174 تماس بفرمایید.</li>
                </ul>
            </div>
        </div>
        <button class="button step-next-btn">مرحله بعد</button>
    </form>
</section>

<script>
(function(){
    var wrap=document.getElementById('keywords-wrap');
    var input=document.getElementById('keyword-input');
    var hidden=document.getElementById('keywords-json');
    if(!wrap||!input||!hidden)return;
    var tags=[];
    try{tags=JSON.parse(hidden.value)||[]}catch(e){}

    function renderTags(){
        wrap.querySelectorAll('.keyword-tag').forEach(function(t){t.remove();});
        tags.forEach(function(kw){
            var span=document.createElement('span');
            span.className='keyword-tag';
            span.textContent=kw;
            var btn=document.createElement('button');
            btn.type='button';
            btn.className='keyword-remove';
            btn.textContent='✕';
            btn.onclick=function(){tags=tags.filter(function(t){return t!==kw;});renderTags();};
            span.appendChild(btn);
            wrap.insertBefore(span,input);
        });
        hidden.value=JSON.stringify(tags);
    }

    input.addEventListener('keydown',function(e){
        if(e.key==='Enter'||e.key===','){
            e.preventDefault();
            addKeyword();
        }
    });
    input.addEventListener('blur',function(){
        if(input.value.trim()&&!wrap.contains(document.activeElement)){
            addKeyword();
        }
    });
    var addBtn=document.createElement('button');
    addBtn.type='button';
    addBtn.className='keyword-add';
    addBtn.textContent='＋';
    addBtn.title='افزودن کلمه';
    addBtn.onclick=addKeyword;
    wrap.appendChild(addBtn);
    function addKeyword(){
        var parts=input.value.split(',').map(function(s){return s.trim();}).filter(Boolean);
        parts.forEach(function(v){
            if(v&&tags.length<10&&tags.indexOf(v)===-1){
                tags.push(v);
            }
        });
        input.value='';
        renderTags();
    }
    renderTags();
    cascadeGeo();
    initImagePreview('guest-images', 'guest-images-preview', 'guest-images-name', 'guest-images-count');

    /* ── فشرده‌سازی تصاویر قبل از آپلود ── */
    var imgInput=document.getElementById('guest-images');
    if(imgInput){
        imgInput.addEventListener('change',function(){
            if(!imgInput.files||!imgInput.files.length)return;
            var files=Array.prototype.slice.call(imgInput.files);
            var dt=new DataTransfer();
            var done=0;
            files.forEach(function(file){
                compressImage(file,function(compressed){
                    dt.items.add(compressed);
                    done++;
                    if(done===files.length){imgInput.files=dt.files;imgInput.dispatchEvent(new Event('change'));}
                });
            });
        });
    }
    function compressImage(file,cb){
        if(file.size<=300*1024||!file.type.match(/^image\/jpeg|^image\/png|^image\/webp$/)){cb(file);return;}
        var reader=new FileReader();
        reader.onload=function(e){
            var img=new Image();
            img.onload=function(){
                var w=img.width,h=img.height;
                var maxW=1600;
                if(w>maxW){h=Math.round(h*maxW/w);w=maxW;}
                var c=document.createElement('canvas');c.width=w;c.height=h;
                var ctx=c.getContext('2d');
                ctx.drawImage(img,0,0,w,h);
                c.toBlob(function(blob){
                    cb(new File([blob],file.name.replace(/\.[^.]+$/,'.jpg'),{type:'image/jpeg',lastModified:Date.now()}));
                },'image/jpeg',0.82);
            };
            img.src=e.target.result;
        };
        reader.readAsDataURL(file);
    }
})();

function handleServiceClick(checkbox) {
    var isFree = checkbox.getAttribute('data-is-free') === '1';
    if (checkbox.checked && isFree) {
        document.querySelectorAll('input[name="services[]"]').forEach(function(cb) {
            if (cb.getAttribute('data-is-free') !== '1' && cb !== checkbox) {
                cb.checked = false;
            }
        });
    } else if (checkbox.checked && !isFree) {
        document.querySelectorAll('input[name="services[]"]').forEach(function(cb) {
            if (cb.getAttribute('data-is-free') === '1' && cb !== checkbox) {
                cb.checked = false;
            }
        });
    }
    calculateTotal();
}

function calculateTotal() {
    var checkboxes = document.querySelectorAll('input[name="services[]"]:checked');
    var total = 0;
    checkboxes.forEach(function(checkbox) {
        var price = parseInt(checkbox.getAttribute('data-price')) || 0;
        total += price;
    });
    var totalInToman = Math.round(total / 10);
    var totalElement = document.getElementById('total-price');
    if (totalElement) {
        totalElement.textContent = totalInToman === 0 ? '0 تومان' : number_format(totalInToman) + ' تومان';
    }
}

function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// محاسبه اولیه بعد از لود صفحه
calculateTotal();

function cascadeGeo(){
    var country=document.querySelector('[data-cascade-country]');
    var province=document.querySelector('[data-cascade-province]');
    var city=document.querySelector('[data-cascade-city]');
    if(!country||!province||!city)return;
    function filterProvince(){
        var cid=country.value;
        var ph=province.options[0];
        if(ph&&ph.value==='')ph.textContent=cid?'استان را انتخاب کنید':'ابتدا کشور را انتخاب کنید';
        var hasSelected=false;
        province.querySelectorAll('option[data-country]').forEach(function(o){
            var show=(cid===''||o.getAttribute('data-country')===cid);
            o.style.display=show?'':'none';
            if(show&&o.selected)hasSelected=true;
        });
        province.disabled=(cid==='');
        if(!hasSelected||cid===''){province.value='';}
        filterCity();
    }
    function filterCity(){
        var pid=province.value;
        var ph=city.options[0];
        if(ph&&ph.value==='')ph.textContent=pid?'شهر را انتخاب کنید':'ابتدا استان را انتخاب کنید';
        var hasSelected=false;
        city.querySelectorAll('option[data-province]').forEach(function(o){
            var show=(pid===''||o.getAttribute('data-province')===pid);
            o.style.display=show?'':'none';
            if(show&&o.selected)hasSelected=true;
        });
        city.disabled=(pid==='');
        if(!hasSelected||pid===''){city.value='';}
    }
    country.addEventListener('change',filterProvince);
    province.addEventListener('change',filterCity);
    filterProvince();
}
function initImagePreview(inputId, previewId, nameId, countId){
    var input=document.getElementById(inputId);
    var preview=document.getElementById(previewId);
    var name=document.getElementById(nameId);
    var countEl=countId?document.getElementById(countId):null;
    var maxImages={{ config('agahi.max_images', 5) }};
    if(!input||!preview)return;
    function render(files){
        preview.innerHTML='';
        var fileCount=files?files.length:0;
        Array.prototype.forEach.call(files,function(file,idx){
            var box=document.createElement('span');
            box.className='image-preview-item';
            var img=document.createElement('img');
            img.src=URL.createObjectURL(file);
            img.alt=file.name;
            var del=document.createElement('button');
            del.type='button';
            del.className='image-preview-remove';
            del.textContent='✕';
            del.setAttribute('aria-label','حذف تصویر');
            del.addEventListener('click',function(){
                var dt=new DataTransfer();
                var list=Array.prototype.slice.call(input.files);
                list.splice(idx,1);
                list.forEach(function(f){dt.items.add(f);});
                input.files=dt.files;
                update();
            });
            box.appendChild(img);
            box.appendChild(del);
            preview.appendChild(box);
        });
        if(input.files&&input.files.length&&name){name.textContent=input.files.length+' فایل انتخاب شد';}
        else if(name){name.textContent='فایلی انتخاب نشده';}
        if(countEl){
            countEl.textContent=fileCount>0?fileCount+' از '+maxImages+' تصویر انتخاب شده':'';
        }
    }
    function update(){render(input.files);}
    input.addEventListener('change',update);
}
</script>

<style>
.steps-bar{display:flex;gap:0;margin:0 0 1.25rem;border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;}
.step-next-btn{margin-bottom:2rem;}
.rules-note{margin-top:1.25rem;border:1px solid #e5e7eb;border-radius:.625rem;background:#fafafa;overflow:hidden;}
.rules-note-header{padding:.75rem 1rem;font-size:.875rem;font-weight:700;color:#0f766e;background:#f0fdfa;border-bottom:1px solid #e5e7eb;}
.rules-note-body{padding:.75rem 1rem;}
.rules-note-body ul{margin:0;padding:0;list-style:disc;padding-inline-start:1.25rem;font-size:.8rem;color:#4b5563;line-height:1.9;}
.rules-note-body li{margin-bottom:.25rem;}
.step{flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1rem;background:#f9fafb;color:#9ca3af;font-size:.875rem;font-weight:600;border-left:1px solid #e5e7eb;}
.step:last-child{border-left:none;}
.step.active{background:#f0fdfa;color:#0f766e;}
.step-num{display:inline-flex;align-items:center;justify-content:center;width:1.5rem;height:1.5rem;border-radius:50%;background:#e5e7eb;color:#6b7280;font-size:.75rem;font-weight:700;}
.step.active .step-num{background:#14b8a6;color:#fff;}
.keywords-input-wrap{display:flex;flex-wrap:wrap;gap:.375rem;padding:.5rem;border:1px solid #e5e7eb;border-radius:.375rem;background:#fff;min-height:2.5rem;align-items:center;cursor:text;}
.keywords-input-wrap:focus-within{border-color:#2dd4bf;box-shadow:0 0 0 3px rgba(45,212,191,.1);}
.keywords-input-wrap input{border:0;outline:0;flex:1;min-width:120px;font-size:.875rem;padding:0;}
.keyword-tag{display:inline-flex;align-items:center;gap:.25rem;background:#f0fdfa;border:1px solid #99f6e4;color:#0f766e;font-size:.75rem;padding:.2rem .5rem;border-radius:999px;font-weight:500;}
.keyword-remove{border:0;background:0;color:#0f766e;cursor:pointer;font-size:.7rem;padding:0;line-height:1;opacity:.7;}
.keyword-remove:hover{opacity:1;}
.form-section-title{font-size:.875rem;font-weight:700;color:#374151;margin-top:.75rem;padding-bottom:.25rem;border-bottom:1px solid #f3f4f6;}
.geo-label{font-size:.875rem;font-weight:700;color:#374151;margin-top:.25rem!important;padding-bottom:.25rem;border-bottom:1px solid #f3f4f6;}
.total-price-section{margin-top:1rem;padding:1rem;background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;}
.total-price-box{display:flex;justify-content:space-between;align-items:center;font-size:1rem;font-weight:700;color:#0f766e;}
.total-price-amount{font-size:1.25rem;color:#14b8a6;}
.keyword-add{flex:0 0 auto;width:2rem;height:2rem;border:0;border-radius:.375rem;background:#14b8a6;color:#fff;font-size:1.1rem;font-weight:600;cursor:pointer;line-height:1;transition:background .15s;}
.keyword-add:hover{background:#0d9488;}
.keyword-add:active{transform:scale(.95);}
.image-count-info{display:block;font-size:.8rem;color:#6b7280;margin-top:.25rem;font-weight:500;}
.image-preview{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:.5rem;margin-top:.5rem;width:100%;}
.image-preview-item{position:relative;border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;background:#f9fafb;}
.image-preview-item img{display:block;width:100%;height:72px;object-fit:cover;}
.image-preview-remove{position:absolute;top:.25rem;inset-inline-end:.25rem;width:1.35rem;height:1.35rem;border:0;border-radius:999px;background:rgba(220,38,38,.9);color:#fff;font-size:.75rem;cursor:pointer;line-height:1;}
.required{color:#dc2626;}
.req{color:#dc2626;font-weight:700;font-size:1.1em;margin-inline-start:.15rem;}
.existing-account-prompt{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin:0 0 1.25rem;padding:.75rem 1rem;background:#f0fdfa;border:1px solid #ccfbf1;border-radius:.625rem;color:#115e59;font-size:.875rem;font-weight:600;}
.existing-account-prompt .button{margin:0;white-space:nowrap;}
@media (max-width:480px){.existing-account-prompt{align-items:stretch;flex-direction:column;}.existing-account-prompt .button{width:100%;}}
</style>
@endsection
