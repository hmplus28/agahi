<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<url><loc><?php echo e($seo->canonicalForAd($ad)); ?></loc><lastmod><?php echo e($ad->updated_at->toAtomString()); ?></lastmod></url>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /home/z/my-project/agahi/resources/views/seo/ads-sitemap.blade.php ENDPATH**/ ?>