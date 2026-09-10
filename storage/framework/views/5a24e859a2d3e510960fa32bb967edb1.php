<?php $__env->startSection('content'); ?>
<section class="auth-card">
    <h1>ورود به حساب کاربری</h1>
    <form method="post">
        <?php echo csrf_field(); ?>
        <label>شمارهٔ موبایل
            <input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" required autocomplete="tel">
        </label>
        <label>گذرواژه
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <label class="check">
            <input type="checkbox" name="remember" value="1"> ورود مرا به خاطر بسپار
        </label>
        <button class="button">ورود</button>
    </form>
    <p>حساب ندارید؟ <a href="<?php echo e(route('register')); ?>">ثبت‌نام کنید.</a></p>
    <p><a href="<?php echo e(route('password.request')); ?>">رمز خود را فراموش کرده‌اید؟</a></p>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ورود | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/auth/login.blade.php ENDPATH**/ ?>