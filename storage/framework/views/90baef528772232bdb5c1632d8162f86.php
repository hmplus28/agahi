<?php $__env->startSection('content'); ?>
<?php
    $selectedCategory = $categories->firstWhere('id', (int) request('category'));
    $selectedCity = $cities->firstWhere('id', (int) request('city'));
    $activeFilters = collect([
        filled(request('q')) ? 'جست‌وجو: '.request('q') : null,
        $selectedCategory ? 'دسته: '.$selectedCategory->title : null,
        $selectedCity ? 'شهر: '.$selectedCity->name : null,
        filled(request('min_price')) ? 'از '.number_format((int) request('min_price')) : null,
        filled(request('max_price')) ? 'تا '.number_format((int) request('max_price')) : null,
    ])->filter();
?>
<div class="page-top"><div><h1>جست‌وجوی آگهی‌ها</h1><p>با چند فیلتر ساده، نتیجهٔ مورد نظرتان را پیدا کنید.</p></div></div>
<div class="search-layout">
    <aside class="filter-panel" aria-label="فیلتر نتایج">
        <div class="filter-title"><h2>فیلترها</h2><div><?php if($activeFilters->isNotEmpty()): ?><a class="clear-filters" href="<?php echo e(route('search')); ?>">پاک‌سازی</a><?php endif; ?><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg></div></div>
        <form class="filter-form" method="get">
            <label class="field-label">عبارت جست‌وجو<input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="مثلاً موبایل سامسونگ" autocomplete="off"></label>
            <label class="field-label">دسته<select name="category"><option value="">همهٔ دسته‌ها</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->id); ?>" <?php if(request('category')==$category->id): echo 'selected'; endif; ?>><?php echo e($category->title); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label class="field-label">شهر<select name="city"><option value="">همهٔ شهرها</option><?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($city->id); ?>" <?php if(request('city')==$city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <div class="price-fields"><label class="field-label">حداقل قیمت<span class="input-with-unit"><input type="number" min="0" name="min_price" value="<?php echo e(request('min_price')); ?>" inputmode="numeric" placeholder="۰"><span>تومان</span></span></label><label class="field-label">حداکثر قیمت<span class="input-with-unit"><input type="number" min="0" name="max_price" value="<?php echo e(request('max_price')); ?>" inputmode="numeric" placeholder="بدون سقف"><span>تومان</span></span></label></div>
            <button class="button" type="submit">نمایش نتایج</button>
        </form>
    </aside>
    <section class="search-results" aria-labelledby="search-results-heading"><h2 id="search-results-heading" class="sr-only">نتایج جست‌وجو</h2>
        <div class="result-bar" aria-live="polite"><strong><?php echo e(number_format($ads->total())); ?></strong> آگهی پیدا شد <?php if(request('q')): ?> برای «<?php echo e(request('q')); ?>» <?php endif; ?></div>
        <?php if($activeFilters->isNotEmpty()): ?><div class="active-filters" aria-label="فیلترهای فعال"><?php $__currentLoopData = $activeFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($filter); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
        <?php if($ads->isNotEmpty()): ?>
            <div class="ad-grid ad-grid--context"><?php $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
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
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php if($ads->count() < 3): ?><?php if (isset($component)) { $__componentOriginal0cf670fcaab7c4b201044cf9ac40274b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.post-ad-cta','data' => ['title' => 'آگهی شما هم می‌تواند اینجا باشد','subtitle' => 'ثبت آگهی کمتر از چند دقیقه زمان می‌برد.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('post-ad-cta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'آگهی شما هم می‌تواند اینجا باشد','subtitle' => 'ثبت آگهی کمتر از چند دقیقه زمان می‌برد.']); ?>
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
        <?php else: ?>
            <div class="empty-state empty-state--results"><strong>آگهی منطبق با جست‌وجوی شما پیدا نشد.</strong><span>عبارت یا فیلترها را تغییر دهید، یا آگهی مورد نظرتان را ثبت کنید.</span><div><a class="button button-small" href="<?php echo e(route('search')); ?>">پاک‌سازی فیلترها</a><a class="secondary-action" href="<?php echo e(route('user.ads.create')); ?>">ثبت آگهی جدید</a></div></div>
        <?php endif; ?>
        <?php echo e($ads->links()); ?>

    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['robots'=>'noindex, follow','title'=>'جست‌وجوی آگهی‌ها | '.config('app.name')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/public/search.blade.php ENDPATH**/ ?>