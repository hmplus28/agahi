<?php $__env->startSection('content'); ?>
<div class="section-heading">
    <div>
        <h1>آگهی‌های من</h1>
        <p class="muted">وضعیت، بازدید و عملیات مجاز آگهی‌ها را مدیریت کنید.</p>
    </div>
</div>


<nav class="dashboard-links" aria-label="بخش‌های پنل کاربری">
    <a href="<?php echo e(route('user.payments.index')); ?>">پرداخت‌ها و تمدید</a>
    <a href="<?php echo e(route('user.tickets.index')); ?>">تیکت‌های پشتیبانی</a>
    <a href="<?php echo e(route('user.profile.edit')); ?>">پروفایل کاربری</a>
</nav>


<nav class="tabs" aria-label="فیلتر وضعیت آگهی">
    <a class="<?php echo e(!$status ? 'active' : ''); ?>" href="<?php echo e(route('user.dashboard')); ?>">همه</a>
    <?php $__currentLoopData = \App\Domains\Ads\Enums\AdStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="<?php echo e($status === $item->value ? 'active' : ''); ?>" href="<?php echo e(route('user.dashboard',['status'=>$item->value])); ?>">
            <?php echo e($item->label()); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>


<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>کد</th>
                <th>شهر</th>
                <th>بازدید</th>
                <th>آیپی</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $ads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <a href="<?php echo e($ad->publicUrl()); ?>" class="font-medium text-gray-900 hover:text-brand-600" style="text-decoration:none;">
                            <?php echo e($ad->title); ?>

                        </a>
                    </td>
                    <td dir="ltr" class="text-xs"><?php echo e($ad->code); ?></td>
                    <td><?php echo e($ad->city?->name ?? '—'); ?></td>
                    <td><?php echo e(number_format($ad->views_count)); ?></td>
                    <td dir="ltr" class="text-xs"><?php echo e($ad->submit_ip ?? '—'); ?></td>
                    <td>
                        <span class="badge <?php if($ad->status===\App\Domains\Ads\Enums\AdStatus::Active): ?>badge-success <?php elseif($ad->status===\App\Domains\Ads\Enums\AdStatus::Expired||$ad->status===\App\Domains\Ads\Enums\AdStatus::Deleted): ?>badge-danger <?php endif; ?>">
                            <?php echo e($ad->status->label()); ?>

                        </span>
                    </td>
                    <td>
                        <div class="row-actions">
                            <?php if(!in_array($ad->status,[\App\Domains\Ads\Enums\AdStatus::Expired,\App\Domains\Ads\Enums\AdStatus::Deleted],true)): ?>
                                <a href="<?php echo e(route('user.ads.edit',$ad)); ?>">ویرایش</a>
                            <?php endif; ?>
                            <?php if($ad->status===\App\Domains\Ads\Enums\AdStatus::Expired): ?>
                                <a href="<?php echo e(route('user.payments.index')); ?>">تمدید</a>
                            <?php endif; ?>
                            <?php if($ad->status===\App\Domains\Ads\Enums\AdStatus::PendingPayment): ?>
                                <a href="<?php echo e(route('user.payments.checkout')); ?>?ad=<?php echo e($ad->code); ?>">پرداخت</a>
                            <?php endif; ?>
                            <?php if($ad->status===\App\Domains\Ads\Enums\AdStatus::NeedsPermit): ?>
                                <form method="post" action="<?php echo e(route('user.ads.permits.store',$ad)); ?>" enctype="multipart/form-data">
                                    <?php echo csrf_field(); ?>
                                    <input type="file" name="image" accept="image/*" aria-label="تصویر مجوز">
                                    <button class="link-button" type="submit">ارسال مجوز</button>
                                </form>
                            <?php endif; ?>
                            <?php if($ad->status!==\App\Domains\Ads\Enums\AdStatus::Deleted): ?>
                                <form method="post" action="<?php echo e(route('user.ads.destroy',$ad)); ?>" onsubmit="return confirm('آگهی حذف شود؟ این عملیات قابل بازگشت نیست.')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button class="link-button danger" type="submit">حذف</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7">
                        <div style="text-align:center;padding:2rem;">
                            <p class="font-semibold text-gray-700 mb-2">هنوز هیچ آگهی‌ای ثبت نکرده‌اید.</p>
                            <a class="button button-small" href="<?php echo e(route('user.ads.create')); ?>">ثبت اولین آگهی</a>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo e($ads->links()); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app',['title'=>'پنل کاربری | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/user/dashboard.blade.php ENDPATH**/ ?>