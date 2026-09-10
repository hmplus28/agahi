<?php $__env->startSection('content'); ?>
<h1>گزارش‌های تخلف</h1>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>دلیل</th>
                <th>توضیح</th>
                <th>وضعیت</th>
                <th>اقدام سریع</th>
                <th>تغییر وضعیت</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($ad = $report->ad); ?>
                <tr>
                    <td>
                        <?php if($ad): ?>
                            <a href="<?php echo e($ad->publicUrl()); ?>" target="_blank" rel="noopener"><?php echo e($ad->title); ?></a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($report->reason); ?></td>
                    <td><?php echo e($report->description ?? '—'); ?></td>
                    <td>
                        <span class="badge badge-<?php echo e($report->status === 'resolved' ? 'success' : ($report->status === 'reviewing' ? 'warning' : '')); ?>">
                            <?php echo e($report->status); ?>

                        </span>
                    </td>
                    <td>
                        <div class="quick-actions">
                            <?php if($ad && $ad->status !== \App\Domains\Ads\Enums\AdStatus::Deleted): ?>
                                <form method="post" action="<?php echo e(route('admin.reports.deleteAd', $report)); ?>" onsubmit="return confirm('آگهی حذف شود؟')">
                                    <?php echo csrf_field(); ?>
                                    <button class="button button-small button-danger" type="submit">حذف آگهی</button>
                                </form>
                            <?php endif; ?>

                            <?php if($templates->isNotEmpty() && $ad?->user): ?>
                                <form method="post" action="<?php echo e(route('admin.reports.sms', $report)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <select name="template_id" required>
                                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($tpl['id']); ?>"><?php echo e($tpl['label']); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <button class="button button-small" type="submit">پیامک به کاربر</button>
                                </form>
                            <?php endif; ?>

                            <?php if($ad?->user): ?>
                                <form method="post" action="<?php echo e(route('admin.reports.ticket', $report)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="button button-small" type="submit">تیکت به کاربر</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <form method="post" action="<?php echo e(route('admin.reports.update', $report)); ?>">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <select name="status">
                                <option value="new" <?php if($report->status === 'new'): echo 'selected'; endif; ?>>جدید</option>
                                <option value="reviewing" <?php if($report->status === 'reviewing'): echo 'selected'; endif; ?>>در حال بررسی</option>
                                <option value="resolved" <?php if($report->status === 'resolved'): echo 'selected'; endif; ?>>حل‌شده</option>
                                <option value="rejected" <?php if($report->status === 'rejected'): echo 'selected'; endif; ?>>ردشده</option>
                            </select>
                            <input name="admin_note" placeholder="یادداشت" value="<?php echo e($report->admin_note); ?>">
                            <button class="button button-small">ذخیره</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6">گزارشی وجود ندارد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php echo e($reports->links()); ?>


<style>
.quick-actions{display:flex;gap:.375rem;flex-wrap:wrap;align-items:center;}
.quick-actions select{font-size:.7rem;padding:.2rem .3rem;border:1px solid #e5e7eb;border-radius:6px;}
.button-small{font-size:.7rem;padding:.2rem .5rem;}
.button-danger{background:#dc2626;color:#fff;border:none;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'گزارش‌های تخلف | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/admin/reports/index.blade.php ENDPATH**/ ?>