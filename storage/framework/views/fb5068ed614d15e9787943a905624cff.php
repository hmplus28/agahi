<?php $__env->startSection('content'); ?>
<?php
    $labels = ['categories' => 'دسته‌بندی', 'countries' => 'کشور', 'provinces' => 'استان', 'cities' => 'شهر', 'tariffs' => 'تعرفه', 'forbidden-words' => 'لغت غیرمجاز'];
    $importHints = [
        'countries' => 'ستون‌ها: نام کشور، slug (اختیاری)',
        'provinces' => 'ستون‌ها: نام کشور، نام استان، slug (اختیاری)',
        'cities'    => 'ستون‌ها: نام استان، نام شهر، slug (اختیاری)',
    ];
?>

<div class="section-heading">
    <div>
        <h1>مدیریت <?php echo e($labels[$type]); ?></h1>
        <p class="muted">داده‌های عمومی و تنظیمات قابل مدیریت سامانه.</p>
    </div>
    <a href="<?php echo e(route('admin.dashboard')); ?>">داشبورد</a>
</div>

<?php if(!empty($importable)): ?>
<section class="panel import-panel">
    <h2>ورود از اکسل</h2>
    <div class="import-row">
        <form method="post" action="<?php echo e(route('admin.catalog.import', $type)); ?>" enctype="multipart/form-data" class="import-form">
            <?php echo csrf_field(); ?>
            <label class="field-label">
                <span>فایل اکسل (.xlsx)</span>
                <input type="file" name="file" accept=".xlsx,.xls" required>
            </label>
            <button class="button" type="submit">ورود از اکسل</button>
        </form>
        <a class="link-button" href="<?php echo e(route('admin.catalog.sample', $type)); ?>">دانلود فایل نمونه</a>
    </div>
    <?php if(isset($importHints[$type])): ?>
    <p class="field-hint"><?php echo e($importHints[$type]); ?></p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="panel">
    <h2>افزودن رکورد</h2>
    <form method="post" action="<?php echo e(route('admin.catalog.store', $type)); ?>" class="form-grid">
        <?php echo csrf_field(); ?>
        <?php echo $__env->make('admin.catalog.fields', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div><button class="button">ثبت</button></div>
    </form>
</section>

<section>
    <h2>رکوردها</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>شناسه / slug</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($record->title ?? $record->name ?? $record->word); ?></td>
                        <td><?php echo e($record->slug ?? $record->code ?? $record->normalized_word); ?></td>
                        <td><?php echo e($record->is_active ? 'فعال' : 'غیرفعال'); ?></td>
                        <td>
                            <form method="post" action="<?php echo e(route('admin.catalog.toggle', [$type, $record->id])); ?>">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button class="link-button"><?php echo e($record->is_active ? 'غیرفعال‌کردن' : 'فعال‌کردن'); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4">رکوردی وجود ندارد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php echo e($records->links()); ?>

</section>

<style>
.import-panel{margin-bottom:1.5rem;}
.import-row{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.import-form{display:flex;gap:.5rem;align-items:end;flex-wrap:wrap;}
.field-label span{display:block;font-size:.75rem;color:#374151;margin-bottom:.25rem;font-weight:600;}
.field-hint{font-size:.7rem;color:#9ca3af;margin-top:.5rem;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'مدیریت داده‌ها | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/admin/catalog/index.blade.php ENDPATH**/ ?>