<?php $__env->startSection('content'); ?>
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="<?php echo e(route('home')); ?>">خانه</a><span>/</span><span><?php echo e($category->title); ?></span></nav>
<div class="page-top"><div><h1>آگهی‌های <?php echo e($category->title); ?></h1><?php if($category->description): ?><p><?php echo e($category->description); ?></p><?php else: ?><p>جدیدترین آگهی‌های این دسته را ببینید.</p><?php endif; ?></div></div>
<?php if($category->children->isNotEmpty()): ?>
<section class="subcategories" aria-labelledby="subcategories-title"><h2 id="subcategories-title">زیر‌دسته‌ها</h2><?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('categories.show',$child)); ?>"><?php echo e($child->title); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></section>
<?php endif; ?>
<section aria-label="آگهی‌های دسته"><div class="result-bar"><strong><?php echo e(number_format($ads->total())); ?></strong> آگهی در این دسته</div><div class="ad-grid"><?php $__empty_1 = true; $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
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
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">در این دسته هنوز آگهی فعالی وجود ندارد.</p><?php endif; ?></div><?php echo e($ads->links()); ?></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>($category->seo_title ?: 'آگهی '.$category->title).' | '.config('app.name'),'description'=>$category->seo_description ?: ($category->description ?: config('agahi.site_description')),'robots'=>$ads->total() ? 'index, follow' : 'noindex, follow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/public/categories/show.blade.php ENDPATH**/ ?>