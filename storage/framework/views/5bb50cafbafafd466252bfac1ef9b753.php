<?php $__env->startSection('content'); ?>
<section class="auth-card">
    <h1>بازیابی رمز عبور</h1>

    <div class="auth-info-note" role="note">
        <strong>دو حالت موجود است:</strong>
        <span>«یادآوری رمز»: رمز فعلیِ سامانه دوباره پیامک می‌شود (رمز تغییر نمی‌کند).</span>
        <span>«ساخت رمز جدید»: رمز قبلی غیرفعال می‌شود و رمز جدیدی برایتان پیامک می‌گردد.</span>
    </div>

    <form method="post">
        <?php echo csrf_field(); ?>
        <label>شمارهٔ موبایل
            <input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" placeholder="09123456789" required autocomplete="tel">
        </label>

        <fieldset class="action-choice">
            <legend>انتخاب نوع درخواست:</legend>
            <label class="check-label">
                <input type="radio" name="action" value="remind" checked>
                <span>یادآوری رمز فعلی (رمز تغییر نمی‌کند)</span>
            </label>
            <label class="check-label">
                <input type="radio" name="action" value="rotate">
                <span>ساخت رمز جدید (رمز قبلی غیرفعال می‌شود)</span>
            </label>
        </fieldset>

        <button class="button" type="submit">ارسال درخواست</button>
    </form>
    <p>رمز عبور دارید؟ <a href="<?php echo e(route('login')); ?>">وارد شوید.</a></p>
</section>

<style>
.auth-info-note{background:#f0fdfa;border:1px solid #99f6e4;border-radius:.5rem;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;color:#0f766e;}
.auth-info-note strong{display:block;margin-bottom:.25rem;}
.auth-info-note span{display:block;line-height:1.5;margin-top:.25rem;}
.action-choice{border:1px solid #e5e7eb;border-radius:.5rem;padding:.75rem 1rem;margin:.5rem 0 1.25rem;}
.action-choice legend{font-size:.75rem;font-weight:600;color:#374151;padding:0 .25rem;}
.check-label{display:flex;gap:.5rem;align-items:flex-start;font-size:.85rem;color:#374151;margin-top:.5rem;}
.check-label input{margin-top:.2rem;}
.check-label span{flex:1;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'بازیابی رمز عبور | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>