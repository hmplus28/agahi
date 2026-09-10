<?php $__env->startSection('content'); ?>
<section class="auth-card">
    <h1>ورود به حساب کاربری</h1>
    <?php if($errors->any()): ?>
        <div style="display:flex;flex-direction:column;gap:.25rem;margin:0 0 1rem;padding:.75rem 1rem;border:1px solid #fecaca;border-radius:.625rem;background:#fef2f2;color:#991b1b;font-size:.875rem;line-height:1.7;" role="alert">
            <strong style="color:#b91c1c;font-size:.9rem;">ورود ناموفق بود</strong>
            <span style="color:#7f1d1d;"><?php echo e($errors->first()); ?></span>
        </div>
    <?php endif; ?>
    <form method="post" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <label>شمارهٔ موبایل
            <input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" pattern="09\d{9}" maxlength="11" placeholder="09123456789" required autocomplete="tel">
        </label>
        <label>گذرواژه
            <input name="password" type="password" required autocomplete="current-password">
        </label>
        <label class="check">
            <input type="checkbox" name="remember" value="1"> ورود مرا به خاطر بسپار
        </label>
        <button class="button">ورود</button>
    </form>
    <p><a href="<?php echo e(route('password.request')); ?>">رمز عبور را فراموش کرده‌اید؟</a></p>
</section>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ورود | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/auth/login.blade.php ENDPATH**/ ?>