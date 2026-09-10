<?php $__env->startSection('content'); ?>
<section class="home-hero">
    <div class="hero-content">
        <p class="eyebrow">بازار آگهی‌های محلی، ساده و مطمئن</p>
        <h1>هر چیزی که نیاز دارید، نزدیک شما پیدا کنید.</h1>
        <p class="hero-copy">میان آگهی‌ها جست‌وجو کنید یا در چند دقیقه آگهی خودتان را ثبت کنید.</p>
        <form class="home-search home-search-section" method="get" action="<?php echo e(route('search')); ?>" role="search">
            <label class="sr-only" for="home-query">جست‌وجوی آگهی</label>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.25 4.25"/></svg>
            <input id="home-query" type="search" name="q" placeholder="مثلاً: تعمیرات کولر، استخدام، لوازم خانه" autocomplete="off">

            <label class="sr-only" for="home-category">دسته‌بندی</label>
            <select id="home-category" name="category">
                <option value="">همهٔ دسته‌ها</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($category['id'] ?? ''); ?>"><?php echo e($category['title']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>

            <label class="sr-only" for="home-min-price">حداقل قیمت</label>
            <input id="home-min-price" type="number" name="min_price" min="0" placeholder="حداقل قیمت">

            <label class="sr-only" for="home-max-price">حداکثر قیمت</label>
            <input id="home-max-price" type="number" name="max_price" min="0" placeholder="حداکثر قیمت">

            <button class="button" type="submit">جست‌وجو</button>
        </form>
        <div class="quick-links" aria-label="دسته‌های پرمراجعه"><?php $__empty_1 = true; $__currentLoopData = array_slice($categories,0,4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><a href="<?php echo e(route('categories.show',['category'=>$category['slug']])); ?>"><?php echo e($category['title']); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><a href="<?php echo e(route('search')); ?>">همهٔ آگهی‌ها</a><?php endif; ?></div>
    </div>
</section>

<section class="section" aria-labelledby="categories-title">
    <div class="section-heading"><div><h2 id="categories-title">دسته‌بندی‌های آگهی</h2><p>با انتخاب دسته، سریع‌تر به نتیجه برسید.</p></div><a class="section-link" href="<?php echo e(route('search')); ?>">همهٔ دسته‌ها</a></div>
    <div class="category-grid">
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="category-card">
                <h3><span class="category-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg></span><a href="<?php echo e(route('categories.show',['category'=>$category['slug']])); ?>"><?php echo e($category['title']); ?></a></h3>
                <?php if($category['children'] !== []): ?><ul><?php $__currentLoopData = array_slice($category['children'],0,4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a href="<?php echo e(route('categories.show',['category'=>$child['slug']])); ?>"><?php echo e($child['title']); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php else: ?><ul><li><a href="<?php echo e(route('categories.show',['category'=>$category['slug']])); ?>">مشاهدهٔ آگهی‌ها</a></li></ul><?php endif; ?>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-state"><strong>دسته‌ای برای نمایش نداریم.</strong><span>به‌زودی دسته‌بندی‌های جدید در دسترس قرار می‌گیرند.</span></div>
        <?php endif; ?>
    </div>
</section>

<?php if($featured->isNotEmpty()): ?>
<section class="section" aria-labelledby="featured-title">
    <div class="section-heading"><div><h2 id="featured-title">آگهی‌های منتخب</h2><p>آگهی‌هایی که بیشتر دیده می‌شوند.</p></div></div>
    <div class="ad-grid ad-grid--context"><?php $__currentLoopData = $featured; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
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
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php if($featured->count() < 3): ?><?php if (isset($component)) { $__componentOriginal0cf670fcaab7c4b201044cf9ac40274b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0cf670fcaab7c4b201044cf9ac40274b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.post-ad-cta','data' => ['title' => 'آگهی شما می‌تواند منتخب باشد','subtitle' => 'با ثبت آگهی، مشتریان نزدیک شما را پیدا می‌کنند.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('post-ad-cta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'آگهی شما می‌تواند منتخب باشد','subtitle' => 'با ثبت آگهی، مشتریان نزدیک شما را پیدا می‌کنند.']); ?>
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
</section>
<?php endif; ?>

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
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php if($latest->count() < 3): ?><?php if (isset($component)) { $__componentOriginal0cf670fcaab7c4b201044cf9ac40274b = $component; } ?>
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
    <?php else: ?>
        <div class="empty-state empty-state--wide"><strong>هنوز آگهی فعالی ثبت نشده است.</strong><span>اولین آگهی را ثبت کنید و این بازار را شروع کنید.</span><a class="button button-small" href="<?php echo e(route('user.ads.create')); ?>">ثبت آگهی</a></div>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/public/home.blade.php ENDPATH**/ ?>