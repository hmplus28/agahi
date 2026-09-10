<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>ویرایش آگهی</h1>
    <p class="lead">ویرایش آگهی فعال، آن را برای بررسی مجدد ارسال می‌کند.</p>
    <form method="post" action="<?php echo e(route('user.ads.update',$ad)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="form-grid">
            <label class="wide">عنوان آگهی
                <input name="title" value="<?php echo e(old('title',$ad->title)); ?>" maxlength="300" required>
            </label>

            <?php if (isset($component)) { $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.category-modal','data' => ['categories' => $categories,'name' => 'category_id','selectedCategoryId' => old('category_id',$ad->category_id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('category-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'name' => 'category_id','selected-category-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('category_id',$ad->category_id))]); ?>
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
                <select name="country_id" id="user-edit-country" data-cascade-country>
                    <option value="">انتخاب کنید</option>
                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country->id); ?>" <?php if(old('country_id',$ad->country_id)==$country->id): echo 'selected'; endif; ?>><?php echo e($country->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>استان
                <select name="province_id" id="user-edit-province" data-cascade-province disabled>
                    <option value="">ابتدا کشور را انتخاب کنید</option>
                    <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($province->id); ?>" data-country="<?php echo e($province->country_id); ?>" <?php if(old('province_id',$ad->province_id)==$province->id): echo 'selected'; endif; ?>><?php echo e($province->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>شهر
                <select name="city_id" id="user-edit-city" data-cascade-city required disabled>
                    <option value="">ابتدا استان را انتخاب کنید</option>
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city->id); ?>" data-province="<?php echo e($city->province_id); ?>" <?php if(old('city_id',$ad->city_id)==$city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <label>قیمت (تومان)
                <input type="number" name="price" min="0" value="<?php echo e(old('price',$ad->priceInToman())); ?>">
            </label>

            <label>نام و نام خانوادگی
                <input name="full_name" value="<?php echo e(old('full_name',$ad->full_name)); ?>">
            </label>

            <label>نام کسب‌وکار
                <input name="business_name" value="<?php echo e(old('business_name',$ad->business_name)); ?>">
            </label>

            <label>ایمیل
                <input type="email" name="email" value="<?php echo e(old('email',$ad->email)); ?>">
            </label>

            <label>موبایل اصلی
                <div class="input-row">
                    <input name="mobile_1" value="<?php echo e(old('mobile_1',$ad->mobile_1)); ?>" inputmode="numeric" required>
                    <label class="check-label"><input type="checkbox" name="show_mobile_1" value="1" <?php if(old('show_mobile_1',$ad->show_mobile_1)): echo 'checked'; endif; ?>> <span>نمایش در آگهی</span></label>
                </div>
            </label>

            <label>موبایل دوم
                <input name="mobile_2" value="<?php echo e(old('mobile_2',$ad->mobile_2)); ?>" inputmode="numeric">
            </label>

            <label>تلفن ثابت
                <input name="phone_1" value="<?php echo e(old('phone_1',$ad->phone_1)); ?>">
            </label>

            <label>تلفن ثابت ۲
                <input name="phone_2" value="<?php echo e(old('phone_2',$ad->phone_2)); ?>">
            </label>

            <label class="wide">توضیحات
                <textarea name="description" maxlength="6000" rows="8" required><?php echo e(old('description',$ad->description)); ?></textarea>
            </label>

            <div class="form-section-title wide">لینک‌ها (اختیاری)</div>
            <?php
                $linkTypes = [
                    'site' => 'سایت', 'aparat' => 'ویدیو آپارات', 'eitaa' => 'کانال ایتا',
                    'bale' => 'کانال بله', 'rubika' => 'کانال روبیکا', 'whatsapp' => 'واتس‌آپ',
                    'telegram' => 'تلگرام', 'instagram' => 'اینستاگرام',
                ];
                $oldLinks = old('links', $ad->links->map(fn ($l) => ['type' => $l->type, 'url' => $l->url])->values()->all());
            ?>
            <?php for($i = 1; $i <= 5; $i++): ?>
                <?php $lk = $oldLinks[$i-1] ?? []; ?>

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
                <input name="referrer" value="<?php echo e(old('referrer',$ad->referrer)); ?>" maxlength="160" placeholder="نام معرف">
            </label>

            <div class="form-section-title wide">اطلاعات مجوز (اختیاری)</div>

            <?php
                $permit = old('permit_number')
                    ? (object) ['permit_number'=>old('permit_number'),'permit_issuer'=>old('permit_issuer'),'permit_issued_at'=>old('permit_issued_at'),'permit_description'=>old('permit_description')]
                    : $ad->permits->first();
            ?>

            <label>شماره مجوز
                <input name="permit_number" value="<?php echo e(old('permit_number', $permit->permit_number ?? '')); ?>" maxlength="160">
            </label>

            <label>صادرکننده
                <input name="permit_issuer" value="<?php echo e(old('permit_issuer', $permit->issuer ?? '')); ?>" maxlength="160">
            </label>

            <label>تاریخ صدور (شمسی)
                <?php $issuedAt = old('permit_issued_at', $permit->issued_at ?? ''); ?>
                <input type="text" name="permit_issued_at_display" id="permit-issued-display" value="<?php echo e($issuedAt ? jdate($issuedAt) : ''); ?>" placeholder="۱۴۰۴/۰۵/۱۵" readonly>
                <input type="hidden" name="permit_issued_at" id="permit-issued" value="<?php echo e($issuedAt); ?>">
            </label>

            <label class="wide">توضیحات مجوز (شامل شماره، تاریخ، مرجع و...)
                <textarea name="permit_description" maxlength="2000" rows="3"><?php echo e(old('permit_description', $permit->permit_description ?? '')); ?></textarea>
            </label>

            <label>تصویر مجوز ۱
                <span class="file-upload-wrap">
                    <input type="file" name="permit_image" accept="image/jpeg,image/png,image/webp" id="user-edit-permit">
                    <label class="file-upload-btn" for="user-edit-permit">انتخاب فایل</label>
                    <span class="file-upload-name" id="user-edit-permit-name">فایلی انتخاب نشده</span>
                </span>
                <?php if($ad->permits->first() && $ad->permits->first()->image_path): ?>
                    <small>تصویر فعلی: <a href="<?php echo e(asset('storage/'.$ad->permits->first()->image_path)); ?>" target="_blank">مشاهده</a></small>
                <?php endif; ?>
            </label>

            <label>تصویر مجوز ۲ (اختیاری)
                <span class="file-upload-wrap">
                    <input type="file" name="permit_image2" accept="image/jpeg,image/png,image/webp" id="user-edit-permit2">
                    <label class="file-upload-btn" for="user-edit-permit2">انتخاب فایل</label>
                    <span class="file-upload-name" id="user-edit-permit2-name">فایلی انتخاب نشده</span>
                </span>
                <?php if($ad->permits->first() && $ad->permits->first()->image2_path): ?>
                    <small>تصویر فعلی: <a href="<?php echo e(asset('storage/'.$ad->permits->first()->image2_path)); ?>" target="_blank">مشاهده</a></small>
                <?php endif; ?>
            </label>

            <div class="form-section-title wide">کلمات کلیدی (اختیاری)</div>

            <label class="wide">کلمات کلیدی (حداکثر ۱۱ مورد، با دکمه «＋» یا Enter اضافه کنید)
                <div class="keywords-input-wrap" id="keywords-wrap">
                    <?php if(old('keywords', $ad->keywords)): ?>
                        <?php $__currentLoopData = old('keywords', $ad->keywords ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(filled($kw)): ?>
                                <span class="keyword-tag"><?php echo e($kw); ?><button type="button" class="keyword-remove" onclick="this.parentElement.remove()">✕</button></span>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <input type="text" id="keyword-input" placeholder="کلمه کلیدی را تایپ کنید…" autocomplete="off">
                </div>
                <?php
                    $editKeywordsOld = old('keywords', $ad->keywords ?? []);
                    $editKeywordsJson = is_array($editKeywordsOld) ? json_encode($editKeywordsOld) : ($editKeywordsOld ?: '');
                ?>
                <input type="hidden" name="keywords_json" id="keywords-json" value="<?php echo e($editKeywordsJson); ?>">
            </label>

            <label class="wide">آدرس
                <input name="address" value="<?php echo e(old('address',$ad->address)); ?>" maxlength="500">
            </label>

            <label class="wide">افزودن تصویر جدید (<?php echo e($ad->images()->count()); ?> از <?php echo e(config('agahi.max_images', 5)); ?> تصویر فعلی)
                <span class="file-upload-wrap">
                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple id="user-edit-images">
                    <label class="file-upload-btn" for="user-edit-images">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        انتخاب فایل
                    </label>
                    <span class="file-upload-name" id="user-edit-images-name">فایلی انتخاب نشده</span>
                </span>
                <span class="image-count-info" id="user-edit-images-count"></span>
                <span class="image-preview" id="user-edit-images-preview"></span>
            </label>

            <div class="form-section-title wide">بسته آگهی / انتخاب خدمات</div>
            <div class="choice-box wide">
                <?php $__currentLoopData = $tariffs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tariff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="check service-option">
                        <input type="checkbox" name="services[]" value="<?php echo e($tariff->id); ?>" <?php if($ad->services->contains('tariff_id', $tariff->id)): echo 'checked'; endif; ?>>
                        <span>
                            <strong><?php echo e($tariff->title); ?></strong>
                            <?php if($tariff->description): ?><span class="muted"><?php echo e($tariff->description); ?></span><?php endif; ?>
                            <span class="service-price"><?php echo e($tariff->price===0 ? 'رایگان' : number_format($tariff->price).' ریال'); ?></span>
                        </span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="form-section-title wide">نوع پرداخت</div>
            <div class="choice-box wide">
                <label class="check payment-option"><input type="radio" name="payment_type" value="online" checked> <span>پرداخت آنلاین</span></label>
                <label class="check payment-option"><input type="radio" name="payment_type" value="card_to_card"> <span>کارت به کارت</span></label>
                <label class="check payment-option"><input type="radio" name="payment_type" value="later"> <span>پرداخت بعداً (آگهی تا پرداخت کامل منتشر نمی‌شود)</span></label>
            </div>

        </div>

        <?php if($ad->images->isNotEmpty()): ?>
            <div class="image-strip">
                <?php $__currentLoopData = $ad->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <img src="<?php echo e($image->thumbUrl()); ?>" width="120" height="90" loading="lazy" alt="تصویر آگهی">
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <button class="button">ذخیرهٔ ویرایش</button>
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
        if(e.key==='Enter'){
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
        var v=input.value.trim();
        if(v&&tags.length<11&&tags.indexOf(v)===-1){
            tags.push(v);
            input.value='';
            renderTags();
        }
    }
    renderTags();
    cascadeGeo();
    initImagePreview('user-edit-images', 'user-edit-images-preview', 'user-edit-images-name', 'user-edit-images-count');
})();
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
    var existingCount=<?php echo e($ad->images()->count()); ?>;
    if(!input||!preview)return;
    function render(files){
        preview.innerHTML='';
        var fileCount=files?files.length:0;
        var totalExistingAndNew=existingCount+fileCount;
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
            countEl.textContent=fileCount>0?fileCount+' تصویر جدید (جمع: '+totalExistingAndNew+' از '+maxImages+')':'جمع: '+existingCount+' از '+maxImages+' تصویر فعلی';
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
.keyword-add{flex:0 0 auto;width:2rem;height:2rem;border:0;border-radius:.375rem;background:#14b8a6;color:#fff;font-size:1.1rem;font-weight:600;cursor:pointer;line-height:1;transition:background .15s;}
.keyword-add:hover{background:#0d9488;}
.keyword-add:active{transform:scale(.95);}
.image-count-info{display:block;font-size:.8rem;color:#6b7280;margin-top:.25rem;font-weight:500;}
.image-preview{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:.5rem;margin-top:.5rem;width:100%;}
.image-preview-item{position:relative;border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;background:#f9fafb;}
.image-preview-item img{display:block;width:100%;height:72px;object-fit:cover;}
.image-preview-remove{position:absolute;top:.25rem;inset-inline-end:.25rem;width:1.35rem;height:1.35rem;border:0;border-radius:999px;background:rgba(220,38,38,.9);color:#fff;font-size:.75rem;cursor:pointer;line-height:1;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ویرایش آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/user/ads/edit.blade.php ENDPATH**/ ?>