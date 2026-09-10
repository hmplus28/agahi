<?php $__env->startSection('content'); ?>
<div class="section-heading">
    <div>
        <h1>داشبورد مدیریت</h1>
        <p class="muted">نمای کلی وضعیت تعدیل، داده‌های پایه، مالی و پشتیبانی.</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        <a class="button" href="<?php echo e(route('admin.ads.index')); ?>">مدیریت آگهی‌ها</a>
        <a class="button button-outline" href="<?php echo e(route('home')); ?>">مشاهدهٔ سایت</a>
    </div>
</div>


<nav class="dashboard-links" aria-label="ماژول‌های مدیریت">
    <a href="<?php echo e(route('admin.ads.index')); ?>">🗂 مدیریت آگهی‌ها (همه)</a>
    <a href="<?php echo e(route('admin.catalog.index','categories')); ?>">📁 دسته‌بندی‌ها</a>
    <a href="<?php echo e(route('admin.catalog.index','countries')); ?>">🌍 مکان‌ها</a>
    <a href="<?php echo e(route('admin.catalog.index','tariffs')); ?>">💰 تعرفه‌ها</a>
    <a href="<?php echo e(route('admin.catalog.index','forbidden-words')); ?>">🚫 لغات غیرمجاز</a>
    <a href="<?php echo e(route('admin.permits.index')); ?>">📄 مجوزها</a>
    <a href="<?php echo e(route('admin.reports.index')); ?>">⚠️ گزارش‌های تخلف</a>
    <a href="<?php echo e(route('admin.tickets.index')); ?>">🎫 تیکت‌ها</a>
    <a href="<?php echo e(route('admin.payments.index')); ?>">💳 پرداخت‌ها</a>
    <a href="<?php echo e(route('admin.banner.index')); ?>">🖼 بنر صفحهٔ اصلی</a>
</nav>


<div class="stat-grid">
    <?php $__currentLoopData = $counts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="stat">
            <span><?php echo e(\App\Domains\Ads\Enums\AdStatus::from($status)->label()); ?></span>
            <strong><?php echo e(number_format($count)); ?></strong>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <article class="stat">
        <span>تیکت‌های باز</span>
        <strong><?php echo e(number_format($openTickets)); ?></strong>
    </article>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'پنل مدیریت | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>