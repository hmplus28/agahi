<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>تغییر رمز عبور</h1>

    <div class="auth-info-note" role="note">
        <strong>رمز جدید تولید و پیامک می‌شود.</strong>
        <span>با تأیید این فرم، یک رمز جدید تصادفی برای شما ساخته می‌شود و به شمارهٔ موبایلتان پیامک خواهد شد. رمز قبلی بلافاصله غیرفعال می‌شود و در سایر دستگاه‌ها از حساب خارج می‌شوید.</span>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success" role="status">
            <span aria-hidden="true">✓</span>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-error" role="alert">
            <span aria-hidden="true">!</span>
            <div>
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo e(route('user.password.update')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <label class="check-label">
            <input type="checkbox" name="confirm" value="1" required>
            <span>می‌دانم که رمز قبلی غیرفعال می‌شود و رمز جدید پیامک می‌شود. تأیید می‌کنم.</span>
        </label>

        <button class="button" type="submit">تولید رمز جدید و ارسال پیامک</button>
    </form>

    <p><a href="<?php echo e(route('user.profile.edit')); ?>">بازگشت به پروفایل</a></p>
</section>

<style>
.auth-info-note{background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#0f766e;}
.auth-info-note strong{display:block;margin-bottom:.25rem;}
.auth-info-note span{display:block;line-height:1.5;}
.check-label{display:flex;gap:.5rem;align-items:flex-start;margin-bottom:1rem;font-size:.875rem;color:#374151;}
.check-label input{margin-top:.2rem;}
.check-label span{flex:1;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'تغییر رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/user/password/edit.blade.php ENDPATH**/ ?>