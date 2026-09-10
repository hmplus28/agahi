<?php $__env->startSection('content'); ?>
<?php ($labels=['categories'=>'دسته‌بندی','countries'=>'کشور','provinces'=>'استان','cities'=>'شهر','tariffs'=>'تعرفه','forbidden-words'=>'لغت غیرمجاز']); ?>
<?php ($editing = request('edit')); ?>
<div class="section-heading"><div><h1>مدیریت <?php echo e($labels[$type]); ?></h1><p class="muted">داده‌های عمومی و تنظیمات قابل مدیریت سامانه.</p></div><a class="button button-outline back-button" href="<?php echo e(route('admin.dashboard')); ?>">بازگشت به داشبورد</a></div>
<section class="panel"><h2>افزودن رکورد</h2><form method="post" action="<?php echo e(route('admin.catalog.store',$type)); ?>" class="form-grid"><?php echo csrf_field(); ?> <?php echo $__env->make('admin.catalog.fields',['record'=>null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><div><button class="button">ثبت</button></div></form></section>
<?php if(in_array($type,['countries','provinces','cities'],true)): ?>
<section class="panel">
    <h2>ورود از اکسل</h2>
    <p class="muted" style="margin-bottom:.75rem;">فایل <?php echo e($labels[$type]); ?> را با فرمت .xlsx آپلود کنید. سطر اول باید عنوان باشد و ردیف‌های بعدی داده. ستون‌ها:
        <?php if($type==='countries'): ?> <code>نام</code> - <code>slug</code>
        <?php elseif($type==='provinces'): ?> <code>کشور</code> - <code>نام</code> - <code>slug</code>
        <?php else: ?> <code>استان</code> - <code>نام</code> - <code>slug</code><?php endif; ?>
        . برای کشور/استان ارجاع‌شده باید قبلاً ثبت شده باشد.
    </p>
    <form method="post" action="<?php echo e(route('admin.catalog.import',$type)); ?>" enctype="multipart/form-data" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:end;">
        <?php echo csrf_field(); ?>
        <label class="field-label" style="min-width:220px;">فایل اکسل
            <input type="file" name="file" accept=".xlsx" required>
        </label>
        <button class="button">ورود اطلاعات</button>
        <a class="button button-outline" href="<?php echo e(route('admin.catalog.sample',$type)); ?>">دانلود فایل نمونه</a>
    </form>
</section>
<?php endif; ?>
<?php if($editing && $records->contains('id',(int)$editing)): ?>
<?php ($rec=$records->firstWhere('id',(int)$editing)); ?>
<section class="panel"><h2>ویرایش: <?php echo e($rec->title ?? $rec->name ?? $rec->word); ?></h2><form method="post" action="<?php echo e(route('admin.catalog.update',[$type,$rec->id])); ?>" class="form-grid"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?> <?php echo $__env->make('admin.catalog.fields',['record'=>$rec], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><div><button class="button">ذخیره</button><a class="button button-outline" href="<?php echo e(route('admin.catalog.index',$type)); ?>">انصراف</a></div></form></section>
<?php endif; ?>
<section>
    <h2>رکوردها</h2>
    <form class="filter-form" method="get" style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap;align-items:end;">
        <label class="field-label" style="min-width:200px;">جست‌وجو
            <input name="q" value="<?php echo e($search ?? ''); ?>" placeholder="عنوان...">
        </label>
        <button class="button" type="submit">جست‌وجو</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>عنوان</th><th>مسیر / شناسه</th><?php if($type==='tariffs'): ?><th>قیمت (تومان)</th><th>نوع</th><?php endif; ?><th>وضعیت</th><th>عملیات</th></tr></thead><tbody><?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($record->title ?? $record->name ?? $record->word); ?></td><td><small><?php echo e($type==='categories' ? ($catPath[$record->id] ?? $record->slug) : ($record->slug ?? $record->code ?? $record->normalized_word)); ?></small></td><?php if($type==='tariffs'): ?><td><?php echo e(number_format($record->price / 10)); ?> تومان</td><td><small><?php echo e($record->service_type); ?></small></td><?php endif; ?><td><?php echo e($record->is_active ? 'فعال' : 'غیرفعال'); ?></td><td style="display:flex;gap:.5rem;white-space:nowrap;"><a class="link-button" href="<?php echo e(route('admin.catalog.index',[$type,'edit'=>$record->id,'q'=>$search])); ?>">ویرایش</a><form method="post" action="<?php echo e(route('admin.catalog.toggle',[$type,$record->id])); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><button class="link-button"><?php echo e($record->is_active ? 'غیرفعال‌کردن' : 'فعال‌کردن'); ?></button></form></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="<?php echo e($type==='tariffs' ? 6 : 4); ?>">رکوردی وجود ندارد.</td></tr><?php endif; ?></tbody></table></div><?php echo e($records->links()); ?>

</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'مدیریت داده‌ها | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/admin/catalog/index.blade.php ENDPATH**/ ?>