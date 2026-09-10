<?php $__env->startSection('content'); ?>
<section class="auth-card">
    <h1>ثبت‌نام با موبایل</h1>
    <p class="auth-lead">شماره موبایل خود را وارد کنید. رمز عبور برای شما پیامک خواهد شد.</p>
    <form method="post">
        <?php echo csrf_field(); ?>
        <label>شمارهٔ موبایل
            <input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>
        <button class="button">ارسال رمز عبور</button>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ثبت‌نام | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/auth/register.blade.php ENDPATH**/ ?>