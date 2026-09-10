<?php $__env->startSection('content'); ?>
<section class="auth-card">
    <h1>بازیابی رمز عبور</h1>
    <p class="auth-lead">شماره موبایل خود را وارد کنید. رمز عبور جدیدی برای شما پیامک خواهد شد.</p>
    <form method="post">
        <?php echo csrf_field(); ?>
        <label>شمارهٔ موبایل
            <input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>
        <button class="button">ارسال رمز عبور جدید</button>
    </form>
    <p>رمز عبور دارید؟ <a href="<?php echo e(route('login')); ?>">وارد شوید.</a></p>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'بازیابی رمز عبور | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>