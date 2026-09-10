<?php $__env->startSection('content'); ?>
<div class="section-heading">
    <div>
        <h1>آگهی‌های من</h1>
        <p class="muted">وضعیت، بازدید و عملیات مجاز آگهی‌ها را مدیریت کنید.</p>
    </div>
    <a class="button" href="<?php echo e(route('user.ads.create')); ?>">ثبت آگهی</a>
</div>

<div class="dashboard-links">
    <a href="<?php echo e(route('user.payments.index')); ?>">پرداخت‌ها و تمدید</a>
    <a href="<?php echo e(route('user.tickets.index')); ?>">تیکت‌ها</a>
    <a href="<?php echo e(route('user.profile.edit')); ?>">پروفایل</a>
</div>

<div class="tabs">
    <a class="<?php if(!$status): ?> active <?php endif; ?>" href="<?php echo e(route('user.dashboard')); ?>">همه</a>
    <?php $__currentLoopData = \App\Domains\Ads\Enums\AdStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="<?php if($status === $item->value): ?> active <?php endif; ?>" href="<?php echo e(route('user.dashboard', ['status' => $item->value])); ?>"><?php echo e($item->label()); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>کد</th>
                <th>شهر</th>
                <th>بازدید</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($ad->title); ?></td>
                    <td><?php echo e($ad->code); ?></td>
                    <td><?php echo e($ad->city?->name); ?></td>
                    <td><?php echo e(number_format($ad->views_count)); ?></td>
                    <td><span class="badge"><?php echo e($ad->status->label()); ?></span></td>
                    <td>
                        <div class="row-actions">
                            <?php if(!in_array($ad->status, [\App\Domains\Ads\Enums\AdStatus::Expired, \App\Domains\Ads\Enums\AdStatus::Deleted], true)): ?>
                                <a href="<?php echo e(route('user.ads.edit', $ad)); ?>">ویرایش</a>
                            <?php endif; ?>
                            <?php if($ad->status === \App\Domains\Ads\Enums\AdStatus::Expired): ?>
                                <a href="<?php echo e(route('user.payments.index')); ?>">تمدید</a>
                            <?php endif; ?>
                            <?php if($ad->status === \App\Domains\Ads\Enums\AdStatus::NeedsPermit): ?>
                                <form method="post" action="<?php echo e(route('user.ads.permits.store', $ad)); ?>" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <input type="file" name="image" accept="image/*">
                                    <button class="link-button">ارسال مجوز</button>
                                </form>
                            <?php endif; ?>
                            <?php if($ad->status !== \App\Domains\Ads\Enums\AdStatus::Deleted): ?>
                                <form method="post" action="<?php echo e(route('user.ads.destroy', $ad)); ?>" onsubmit="return confirm('آگهی حذف شود؟')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="link-button danger">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6">آگهی‌ای وجود ندارد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo e($ads->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'پنل کاربری | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/user/dashboard.blade.php ENDPATH**/ ?>