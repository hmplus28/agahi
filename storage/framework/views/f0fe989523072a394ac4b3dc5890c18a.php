<?php $__env->startPush('head'); ?>
<script type="application/ld+json"><?php echo json_encode(['<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>collect($seo->breadcrumbForAd($ad))->values()->map(fn($item,$index)=>['@type'=>'ListItem','position'=>$index+1,'name'=>$item['name'],'item'=>$item['url']])->all()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php if($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired || ($ad->expires_at && $ad->expires_at->isPast())): ?>
<div class="expired-banner" role="alert">
    <strong>این آگهی منقضی شده است.</strong>
    <span>برای اطلاع از شرایط جدید با آگهی‌دهنده تماس بگیرید.</span>
</div>
<?php endif; ?>
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="<?php echo e(route('home')); ?>">خانه</a><span>/</span><?php if($ad->category): ?><a href="<?php echo e(route('categories.show',$ad->category)); ?>"><?php echo e($ad->category->title); ?></a><span>/</span><?php endif; ?><span><?php echo e($ad->title); ?></span></nav>
<article class="ad-detail">
    <section class="gallery" aria-label="تصاویر آگهی">
        <?php ($first=true); ?>
        <?php $__empty_1 = true; $__currentLoopData = $ad->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <img src="<?php echo e($image->displayUrl()); ?>" width="<?php echo e($image->width); ?>" height="<?php echo e($image->height); ?>" <?php if($first): ?> fetchpriority="high" <?php else: ?> loading="lazy" <?php endif; ?> alt="<?php echo e($ad->title); ?>">
            <?php ($first=false); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="image-placeholder gallery-placeholder"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg><span>تصویری برای این آگهی ثبت نشده است.</span></div>
        <?php endif; ?>
    </section>
    <aside class="ad-sidecard">
        <div class="ad-detail-heading"><h1><?php echo e($ad->title); ?></h1><?php if($ad->is_featured): ?><span class="badge">ویژه</span><?php endif; ?></div>
        <p class="detail-price" dir="auto"><?php echo e($ad->priceLabel()); ?></p>
        <p class="ad-date"><?php echo e($ad->published_at?->diffForHumans()); ?> در <?php echo e($ad->city?->name ?? '—'); ?></p>
        <dl class="details"><dt>کد آگهی</dt><dd dir="ltr"><?php echo e($ad->code); ?></dd><dt>بازدید</dt><dd><?php echo e(number_format($ad->views_count)); ?></dd><dt>تاریخ انتشار</dt><dd><?php echo e($ad->published_at?->format('Y/m/d')); ?></dd></dl>
        <?php if($ad->mobile_1): ?><a class="button contact-action" href="tel:<?php echo e($ad->mobile_1); ?>">تماس با آگهی‌دهنده</a><?php endif; ?>
    </aside>
    <section class="description"><h2>توضیحات آگهی</h2><p><?php echo nl2br(e($ad->description)); ?></p><?php if($ad->links->isNotEmpty()): ?><h2 style="margin-top:22px">لینک‌های مرتبط</h2><ul><?php $__currentLoopData = $ad->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><a rel="ugc" target="_blank" href="<?php echo e($link->url); ?>"><?php echo e($link->url); ?></a></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul><?php endif; ?></section>
    <section class="report-box"><h2>گزارش تخلف</h2><form method="post" action="<?php echo e(route('ads.reports.store',$ad->code)); ?>"><?php echo csrf_field(); ?><label class="field-label">دلیل<select name="reason" required><option value="">انتخاب کنید</option><option value="no_response">عدم پاسخگویی</option><option value="fraud">کلاهبرداری</option><option value="false_information">خلاف واقع</option><option value="illegal">محتوای غیرمجاز</option><option value="inappropriate">محتوای نامناسب</option><option value="overpriced">گران‌فروشی</option><option value="other">سایر</option></select></label><label class="field-label">توضیح (اختیاری)<textarea name="description" rows="3" maxlength="2000"></textarea></label><button class="button button-small" type="submit">ثبت گزارش</button></form></section>
</article>
<section class="section related-section" aria-labelledby="related-title"><div class="section-heading"><div><h2 id="related-title">آگهی‌های مرتبط</h2><p>گزینه‌های مشابهی که ممکن است برای شما مناسب باشند.</p></div></div><div class="ad-grid"><?php $__empty_1 = true; $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedAd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ad-card','data' => ['ad' => $relatedAd]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ad-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ad' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relatedAd)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $attributes = $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $component = $__componentOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">آگهی مرتبطی یافت نشد.</p><?php endif; ?></div></section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>$ad->title.' | '.config('app.name'),'description'=>\Illuminate\Support\Str::limit($ad->description,160),'canonical'=>$ad->publicUrl(),'openGraph'=>['image'=>optional($ad->images->firstWhere('is_primary',true) ?? $ad->images->first())->displayUrl()]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/public/ads/show.blade.php ENDPATH**/ ?>