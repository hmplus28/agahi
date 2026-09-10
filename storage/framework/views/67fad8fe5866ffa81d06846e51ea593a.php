<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<url><loc><?php echo e(route('categories.show', ['category' => $category['slug']])); ?></loc><lastmod><?php echo e($category['lastmod']); ?></lastmod></url>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</urlset>
<?php /**PATH /home/z/my-project/agahi/resources/views/seo/categories-sitemap.blade.php ENDPATH**/ ?>