<article class="ad-card">
    <a class="card-image" href="<?php echo e($ad->publicUrl()); ?>" aria-label="مشاهدهٔ آگهی <?php echo e($ad->title); ?>">
        <?php ($image=$ad->images->firstWhere('is_primary',true) ?? $ad->images->first()); ?>
        <?php if($image): ?>
            <img src="<?php echo e($image->thumbUrl()); ?>" width="480" height="<?php echo e(max(1,(int) round(480 * $image->height / max(1,$image->width)))); ?>" loading="lazy" alt="<?php echo e($ad->title); ?>">
        <?php else: ?>
            <span class="image-placeholder card-placeholder"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg><span>بدون تصویر</span></span>
        <?php endif; ?>
    </a>
    <?php if($ad->is_urgent || $ad->is_featured): ?><div class="badge-row"><?php if($ad->is_urgent): ?><span class="badge badge-danger">فوری</span><?php endif; ?> <?php if($ad->is_featured): ?><span class="badge">ویژه</span><?php endif; ?></div><?php endif; ?>
    <div class="card-body">
        <h3><a href="<?php echo e($ad->publicUrl()); ?>"><?php echo e($ad->title); ?></a></h3>
        <p class="price" dir="auto"><?php echo e($ad->priceLabel()); ?></p>
        <div class="card-meta"><span class="muted"><?php echo e($ad->city?->name ?? '—'); ?></span><span class="muted"><?php echo e($ad->published_at?->diffForHumans()); ?></span></div>
    </div>
</article>
<?php /**PATH /home/z/my-project/agahi/resources/views/components/ad-card.blade.php ENDPATH**/ ?>