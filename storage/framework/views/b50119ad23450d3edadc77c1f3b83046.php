<?php $__env->startSection('content'); ?>
<section class="home-hero" aria-label="معرفی">
    
</section>

<section class="home-search-section" aria-label="جست‌وجوی آگهی">
    <div class="container">
        <form class="home-search-form" method="get" action="<?php echo e(route('search')); ?>" role="search">
            <div class="search-row">
                <div class="search-input-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
                    <input type="search" name="q" placeholder="جست‌وجو در آگهی‌ها…" autocomplete="off">
                </div>
                <div class="search-select-wrap">
                    <?php if (isset($component)) { $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.category-modal','data' => ['categories' => $categoryOptions,'name' => 'category','selectedCategoryId' => request('category'),'hideLabel' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('category-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categoryOptions),'name' => 'category','selected-category-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('category')),'hide-label' => true]); ?>
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
                </div>
            </div>
            <div class="search-row">
                <div class="search-price-fields">
                    <label class="sr-only" for="min-price">از مبلغ</label>
                    <input id="min-price" type="number" name="min_price" min="0" placeholder="از مبلغ…" inputmode="numeric">
                    <span class="search-price-sep">تا</span>
                    <label class="sr-only" for="max-price">تا مبلغ</label>
                    <input id="max-price" type="number" name="max_price" min="0" placeholder="تا مبلغ…" inputmode="numeric">
                </div>
                <button class="button search-submit-btn" type="submit">جست‌وجو</button>
            </div>
            <div class="search-row search-geo-row">
                <div class="search-select-wrap">
                    <label class="sr-only" for="home-country">کشور</label>
                    <select id="home-country" name="country" data-cascade-country>
                        <option value="">همهٔ کشورها</option>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($country->id); ?>" <?php if(($country->slug ?? '') === 'iran'): echo 'selected'; endif; ?>><?php echo e($country->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="search-select-wrap">
                    <label class="sr-only" for="home-province">استان</label>
                    <select id="home-province" name="province" data-cascade-province disabled>
                        <option value="">همهٔ استان‌ها</option>
                        <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($province->id); ?>" data-country="<?php echo e($province->country_id); ?>"><?php echo e($province->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="search-select-wrap">
                    <label class="sr-only" for="home-city">شهر</label>
                    <select id="home-city" name="city" data-cascade-city disabled>
                        <option value="">همهٔ شهرها</option>
                        <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($city->id); ?>" data-province="<?php echo e($city->province_id); ?>"><?php echo e($city->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="section" aria-labelledby="latest-title">
    <div class="section-heading"><div><h2 id="latest-title">تازه‌ترین آگهی‌ها</h2><p>آخرین آگهی‌های منتشرشده در بازار.</p></div><a class="section-link" href="<?php echo e(route('search')); ?>">مشاهدهٔ همه</a></div>
    <?php if($latest->isNotEmpty()): ?>
        <div class="ad-grid ad-grid--context"><?php $__currentLoopData = $latest; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ad-card','data' => ['ad' => $ad]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ad-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ad' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ad)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $attributes = $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $component = $__componentOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php if($latest->currentPage() === 1 && $latest->total() < 30): ?><?php if (isset($component)) { $__componentOriginal0cf670fcaab7c4b201044cf9ac40274b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.post-ad-cta','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('post-ad-cta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b)): ?>
<?php $attributes = $__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b; ?>
<?php unset($__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0cf670fcaab7c4b201044cf9ac40274b)): ?>
<?php $component = $__componentOriginal0cf670fcaab7c4b201044cf9ac40274b; ?>
<?php unset($__componentOriginal0cf670fcaab7c4b201044cf9ac40274b); ?>
<?php endif; ?><?php endif; ?></div>
        <div class="pagination"><?php echo e($latest->links()); ?></div>
    <?php else: ?>
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="<?php echo e(route('user.ads.create')); ?>">ثبت آگهی</a></div>
    <?php endif; ?>
</section>

<script>
(function(){
    var country=document.getElementById('home-country');
    var province=document.getElementById('home-province');
    var city=document.getElementById('home-city');
    if(!country||!province||!city)return;
    function filterProvince(){
        var cid=country.value;
        var hasSelected=false;
        province.querySelectorAll('option[data-country]').forEach(function(o){
            var show=(cid===''||o.getAttribute('data-country')===cid);
            o.style.display=show?'':'none';
            if(show&&o.selected)hasSelected=true;
        });
        province.disabled=(cid===''&&!hasSelected);
        if(!hasSelected&&cid!==''){province.value='';}
        filterCity();
    }
    function filterCity(){
        var pid=province.value;
        var hasSelected=false;
        city.querySelectorAll('option[data-province]').forEach(function(o){
            var show=(pid===''||o.getAttribute('data-province')===pid);
            o.style.display=show?'':'none';
            if(show&&o.selected)hasSelected=true;
        });
        city.disabled=(pid===''&&!hasSelected);
        if(!hasSelected&&pid!==''){city.value='';}
    }
    country.addEventListener('change',filterProvince);
    province.addEventListener('change',filterCity);
    filterProvince();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/public/home.blade.php ENDPATH**/ ?>