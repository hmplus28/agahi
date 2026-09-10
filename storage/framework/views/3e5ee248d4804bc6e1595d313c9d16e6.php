<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>ثبت آگهی</h1>
    <p class="lead">آگهی پس از بررسی مدیر منتشر خواهد شد. از ثبت اطلاعات حساس خودداری کنید.</p>
    <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.5rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#0f766e;">
        <strong>آی‌پی شما:</strong> <code dir="ltr" style="background:#e0f2fe;padding:.1rem .4rem;border-radius:.25rem;"><?php echo e($clientIp ?? request()->ip()); ?></code>
    </div>
    <form method="post" action="<?php echo e(route('user.ads.store')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <label class="wide">عنوان آگهی <span class="req">*</span>
                <input name="title" value="<?php echo e(old('title')); ?>" maxlength="300" required>
            </label>

            <?php if (isset($component)) { $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.category-modal','data' => ['categories' => $categories,'name' => 'category_id','selectedCategoryId' => old('category_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('category-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'name' => 'category_id','selected-category-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('category_id'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae)): ?>
<?php $attributes = $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae; ?>
<?php unset($__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae)): ?>
<?php $component = $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae; ?>
<?php unset($__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae); ?>
<?php endif; ?>

            <label class="wide geo-label">موقعیت جغرافیایی</label>

            <label>کشور
                <select name="country_id" id="user-create-country" data-cascade-country>
                    <option value="">انتخاب کنید</option>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>" <?php if((int) (old('country_id') ?? ($country->slug === 'iran' ? $country->id : '')) == $country->id): echo 'selected'; endif; ?>><?php echo e($country->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>استان
                <select name="province_id" id="user-create-province" data-cascade-province disabled>
                    <option value="">ابتدا کشور را انتخاب کنید</option>
                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($province->id); ?>" data-country="<?php echo e($province->country_id); ?>" <?php if(old('province_id')==$province->id): echo 'selected'; endif; ?>><?php echo e($province->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>شهر <span class="req">*</span>
                <select name="city_id" id="user-create-city" data-cascade-city required disabled>
                    <option value="">ابتدا استان را انتخاب کنید</option>
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city->id); ?>" data-province="<?php echo e($city->province_id); ?>" <?php if(old('city_id')==$city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>قیمت (تومان)
                <input type="number" name="price" min="0" value="<?php echo e(old('price')); ?>">
            </label>

            <label>نام و نام خانوادگی
                <input name="full_name" value="<?php echo e(old('full_name',auth()->user()->name ?? '')); ?>">
            </label>

            <label>نام کسب‌وکار
                <input name="business_name" value="<?php echo e(old('business_name')); ?>">
            </label>

            <label>ایمیل
                <input type="email" name="email" value="<?php echo e(old('email',auth()->user()->email ?? '')); ?>">
            </label>

            <label>موبایل اصلی <span class="req">*</span>
                <div class="input-row">
                    <input name="mobile_1" value="<?php echo e(old('mobile_1',auth()->user()->mobile ?? '')); ?>" inputmode="numeric" required>
                    <label class="check-label"><input type="checkbox" name="show_mobile_1" value="1" <?php if(old('show_mobile_1', true)): echo 'checked'; endif; ?>> <span>نمایش در آگهی</span></label>
                </div>
            </label>

            <label>موبایل دوم
                <input name="mobile_2" value="<?php echo e(old('mobile_2')); ?>" inputmode="numeric">
            </label>

            <label>تلفن ثابت
                <input name="phone_1" value="<?php echo e(old('phone_1')); ?>">
            </label>

            <label>تلفن ثابت ۲
                <input name="phone_2" value="<?php echo e(old('phone_2')); ?>">
            </label>

            <label class="wide">توضیحات <span class="req">*</span>
                <textarea name="description" maxlength="6000" rows="8" required><?php echo e(old('description')); ?></textarea>
            </label>

            <label class="wide">آدرس
                <input name="address" value="<?php echo e(old('address')); ?>" maxlength="500">
            </label>

            <div class="form-section-title wide">لینک‌ها (اختیاری)</div>
            <?php
                $linkTypes = [
                    'site' => 'سایت', 'aparat' => 'ویدیو آپارات', 'eitaa' => 'کانال ایتا',
                    'bale' => 'کانال بله', 'rubika' => 'کانال روبیکا', 'whatsapp' => 'واتس‌آپ',
                    'telegram' => 'تلگرام', 'instagram' => 'اینستاگرام',
                ];
                $oldLinks = old('links', []);
            ?>
            <?php for($i = 1; $i <= 5; $i++): ?>
                <?php ($lk = $oldLinks[$i-1] ?? []); ?>
                <label>لینک <?php echo e($i); ?>

                    <span class="link-row">
                        <select name="links[<?php echo e($i-1); ?>][type]">
                            <?php $__currentLoopData = $linkTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tKey => $tLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tKey); ?>" <?php if(($lk['type'] ?? 'site') === $tKey): echo 'selected'; endif; ?>><?php echo e($tLabel); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input type="url" name="links[<?php echo e($i-1); ?>][url]" value="<?php echo e($lk['url'] ?? ''); ?>" placeholder="https://example.com" maxlength="500">
                    </span>
                </label>
            <?php endfor; ?>

            <label class="wide">معرف (اختیاری)
                <input name="referrer" value="<?php echo e(old('referrer')); ?>" maxlength="160" placeholder="نام معرف">
            </label>

            <div class="form-section-title wide">اطلاعات مجوز (اختیاری)</div>

            <label>شماره مجوز
                <input name="permit_number" value="<?php echo e(old('permit_number')); ?>" maxlength="160">
            </label>

            <label>صادرکننده
                <input name="permit_issuer" value="<?php echo e(old('permit_issuer')); ?>" maxlength="160">
            </label>

            <label>تاریخ صدور (شمسی)
                <input type="text" name="permit_issued_at_display" id="permit-issued-display" value="<?php echo e(old('permit_issued_at') ? jdate(old('permit_issued_at')) : ''); ?>" placeholder="۱۴۰۴/۰۵/۱۵" readonly>
                <input type="hidden" name="permit_issued_at" id="permit-issued" value="<?php echo e(old('permit_issued_at')); ?>">
            </label>

            <label class="wide">توضیحات مجوز (شامل شماره، تاریخ، مرجع و...)
                <textarea name="permit_description" maxlength="2000" rows="3"><?php echo e(old('permit_description')); ?></textarea>
            </label>

            <label>تصویر مجوز ۱
                <span class="file-upload-wrap">
                    <input type="file" name="permit_image" accept="image/jpeg,image/png,image/webp" id="user-create-permit">
                    <label class="file-upload-btn" for="user-create-permit">انتخاب فایل</label>
                    <span class="file-upload-name" id="user-create-permit-name">فایلی انتخاب نشده</span>
                </span>
            </label>

            <label>تصویر مجوز ۲ (اختیاری)
                <span class="file-upload-wrap">
                    <input type="file" name="permit_image2" accept="image/jpeg,image/png,image/webp" id="user-create-permit2">
                    <label class="file-upload-btn" for="user-create-permit2">انتخاب فایل</label>
                    <span class="file-upload-name" id="user-create-permit2-name">فایلی انتخاب نشده</span>
                </span>
            </label>

            <div class="form-section-title wide">کلمات کلیدی (اختیاری)</div>

            <label class="wide">کلمات کلیدی (حداکثر ۱۱ مورد، با Enter یا کاما (,) جدا کنید)
                <div class="keywords-input-wrap" id="keywords-wrap">
                    <?php if(old('keywords')): ?>
                        <?php $__currentLoopData = old('keywords'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(filled($kw)): ?>
                                <span class="keyword-tag"><?php echo e($kw); ?><button type="button" class="keyword-remove" onclick="this.parentElement.remove()">✕</button></span>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <input type="text" id="keyword-input" placeholder="کلمه کلیدی را تایپ کنید…" autocomplete="off">
                </div>
                <input type="hidden" name="keywords_json" id="keywords-json" value="<?php echo e(old('keywords_json', is_array(old('keywords')) ? json_encode(old('keywords')) : '')); ?>">
            </label>

            <label class="wide">تصاویر (حداکثر <?php echo e(config('agahi.max_images', 5)); ?> فایل، JPG/PNG/WebP — تصاویر خودکار فشرده می‌شوند)
                <span class="file-upload-wrap">
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple id="user-create-images">
                    <label class="file-upload-btn" for="user-create-images">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        انتخاب فایل
                    </label>
                    <span class="file-upload-name" id="user-create-images-name">فایلی انتخاب نشده</span>
                </span>
                <span class="image-count-info" id="user-create-images-count"></span>
                <span class="image-preview" id="user-create-images-preview"></span>
            </label>

            <div class="form-section-title wide">بسته آگهی / انتخاب خدمات</div>
            <div class="choice-box wide">
                <?php $__currentLoopData = $tariffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tariff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="check service-option">
                        <input type="checkbox" name="services[]" value="<?php echo e($tariff->id); ?>" data-price="<?php echo e($tariff->price); ?>" data-is-free="<?php echo e($tariff->code === 'FREE_30' ? '1' : '0'); ?>" <?php if($tariff->code==='FREE_30'): echo 'checked'; endif; ?> onclick="handleServiceClick(this)">
                        <span>
                            <strong><?php echo e($tariff->title); ?></strong>
                            <?php if($tariff->description): ?><span class="muted"><?php echo e($tariff->description); ?></span><?php endif; ?>
                            <span class="service-price"><?php echo e($tariff->price===0 ? 'رایگان' : number_format($tariff->price / 10).' تومان'); ?></span>
                        </span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="total-price-section wide">
                <div class="total-price-box">
                    <span>جمع مبلغ:</span>
                    <span id="total-price" class="total-price-amount">0 تومان</span>
                </div>
            </div>

            <div class="form-section-title wide">نوع پرداخت</div>
            <div class="choice-box wide">
                <label class="check payment-option"><input type="radio" name="payment_type" value="online" checked> <span>پرداخت آنلاین</span></label>
                <label class="check payment-option"><input type="radio" name="payment_type" value="card_to_card"> <span>کارت به کارت</span></label>
                <label class="check payment-option"><input type="radio" name="payment_type" value="later"> <span>پرداخت بعداً (آگهی تا پرداخت منتشر نمی‌شود)</span></label>
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
        <button class="button">ثبت و ارسال برای تأیید</button>
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
            if(v&&tags.length<11&&tags.indexOf(v)===-1){
                tags.push(v);
            }
        });
        input.value='';
        renderTags();
    }
    renderTags();
    initImagePreview('user-create-images', 'user-create-images-preview', 'user-create-images-name', 'user-create-images-count');

    /* ── فشرده‌سازی تصاویر قبل از آپلود ── */
    var imgInput=document.getElementById('user-create-images');
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

    cascadeGeo();
})();

// این توابع باید در سطح global باشند تا از onclick HTML قابل فراخوانی باشند
function handleServiceClick(checkbox) {
    var isFree = checkbox.getAttribute('data-is-free') === '1';
    if (checkbox.checked && isFree) {
        // Uncheck all paid tariffs
        document.querySelectorAll('input[name="services[]"]').forEach(function(cb) {
            if (cb.getAttribute('data-is-free') !== '1' && cb !== checkbox) {
                cb.checked = false;
            }
        });
    } else if (checkbox.checked && !isFree) {
        // Uncheck free tariff
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
    // تبدیل ریال به تومان
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
    var maxImages=<?php echo e(config('agahi.max_images', 5)); ?>;
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
.rules-note{margin-top:1.25rem;border:1px solid #e5e7eb;border-radius:.625rem;background:#fafafa;overflow:hidden;}
.rules-note-header{padding:.75rem 1rem;font-size:.875rem;font-weight:700;color:#0f766e;background:#f0fdfa;border-bottom:1px solid #e5e7eb;}
.rules-note-body{padding:.75rem 1rem;}
.rules-note-body ul{margin:0;padding:0;list-style:disc;padding-inline-start:1.25rem;font-size:.8rem;color:#4b5563;line-height:1.9;}
.rules-note-body li{margin-bottom:.25rem;}
.req{color:#dc2626;font-weight:700;font-size:1.1em;margin-inline-start:.15rem;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/user/ads/create.blade.php ENDPATH**/ ?>