<?php $__env->startSection('content'); ?>
<div class="page-top">
    <div>
        <h1>گزارش‌های تخلف</h1>
        <p>بررسی گزارش‌ها و اقدام سریع روی آگهی‌های گزارش‌شده.</p>
    </div>
    <a class="button button-outline back-button" href="<?php echo e(route('admin.dashboard')); ?>">بازگشت به داشبورد</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>آگهی</th>
                <th>گزارش‌دهنده</th>
                <th>دلیل / توضیح</th>
                <th>وضعیت</th>
                <th>اقدام سریع</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($ad = $report->ad); ?>
                <tr>
                    <td style="max-width:16rem;">
                        <?php if($ad): ?>
                            <a href="<?php echo e($ad->publicUrl()); ?>" target="_blank" rel="noopener" style="font-weight:600;text-decoration:none;color:inherit;">
                                <?php echo e(\Illuminate\Support\Str::limit($ad->title, 40)); ?>

                            </a>
                            <div class="muted" style="font-size:.75rem;">کد: <span dir="ltr"><?php echo e($ad->code); ?></span> · <?php echo e($ad->status->label()); ?></div>
                        <?php else: ?>
                            <span class="muted">آگهی حذف‌شده</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($report->reason); ?></td>
                    <td style="max-width:12rem;"><?php echo e(\Illuminate\Support\Str::limit($report->description ?? '—', 60)); ?></td>
                    <td><span class="badge <?php if($report->status==='resolved'): ?>badge-success <?php elseif($report->status==='rejected'): ?>badge-danger <?php endif; ?>"><?php echo e($report->status); ?></span></td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:.4rem;min-width:14rem;">
                            <form method="post" action="<?php echo e(route('admin.reports.update',$report)); ?>" style="display:flex;gap:.25rem;align-items:center;flex-wrap:wrap;">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <select name="status" aria-label="وضعیت گزارش">
                                    <option value="new" <?php if($report->status==='new'): echo 'selected'; endif; ?>>جدید</option>
                                    <option value="reviewing" <?php if($report->status==='reviewing'): echo 'selected'; endif; ?>>در حال بررسی</option>
                                    <option value="resolved" <?php if($report->status==='resolved'): echo 'selected'; endif; ?>>حل‌شده</option>
                                    <option value="rejected" <?php if($report->status==='rejected'): echo 'selected'; endif; ?>>ردشده</option>
                                </select>
                                <button class="button button-small">ذخیره</button>
                            </form>
                            <?php if($ad && !in_array($ad->status->value,['deleted'],true)): ?>
                                <form method="post" action="<?php echo e(route('admin.reports.deleteAd',$report)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="link-button danger">🗑 حذف آگهی</button>
                                </form>
                                <?php if($smsTemplates !== []): ?>
                                    <details>
                                        <summary class="link-button">✉ پیامک به کاربر</summary>
                                        <form method="post" action="<?php echo e(route('admin.reports.sms',$report)); ?>" style="margin-top:.35rem;display:flex;flex-direction:column;gap:.3rem;">
                                            <?php echo csrf_field(); ?>
                                            <select name="template_id" required onchange="const t=this.options[this.selectedIndex].dataset.text;this.closest('form').querySelector('[name=text]').value=t||''">
                                                <option value="">انتخاب قالب…</option>
                                                <?php $__currentLoopData = $smsTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tpl['id']); ?>" data-text="<?php echo e($tpl['text']); ?>"><?php echo e($tpl['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                            <textarea name="text" rows="2" maxlength="1000" placeholder="متن (اختیاری — از قالب پر می‌شود)" style="font-size:.75rem;"></textarea>
                                            <button class="button button-small button-outline">ارسال به <?php echo e($ad->mobile_1); ?></button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                                <?php if($ad->user): ?>
                                    <details>
                                        <summary class="link-button">🎫 تیکت به کاربر</summary>
                                        <form method="post" action="<?php echo e(route('admin.reports.ticket',$report)); ?>" style="margin-top:.35rem;display:flex;flex-direction:column;gap:.3rem;">
                                            <?php echo csrf_field(); ?>
                                            <textarea name="message" rows="2" maxlength="5000" placeholder="متن تیکت (خالی = متن پیش‌فرض)" style="font-size:.75rem;"></textarea>
                                            <button class="button button-small button-outline">ایجاد تیکت برای <?php echo e($ad->user->mobile); ?></button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5">گزارشی وجود ندارد.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php echo e($reports->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'گزارش‌های تخلف | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/admin/reports/index.blade.php ENDPATH**/ ?>