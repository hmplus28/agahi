<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<sitemap><loc><?php echo e(route('sitemap.ads', ['page' => $page])); ?></loc></sitemap>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<sitemap><loc><?php echo e(route('sitemap.categories')); ?></loc></sitemap>
</sitemapindex>
<?php /**PATH /home/z/my-project/agahi/resources/views/seo/sitemap-index.blade.php ENDPATH**/ ?>