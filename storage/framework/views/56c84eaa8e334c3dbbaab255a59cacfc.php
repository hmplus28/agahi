<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>ثبت آگهی</h1>
    <div class="steps-bar" aria-label="مراحل ثبت آگهی">
        <div class="step done"><span class="step-num">✓</span> ثبت آگهی</div>
        <div class="step active"><span class="step-num">۲</span> ورود</div>
    </div>

    <div class="ad-summary" role="note">
        <strong>خلاصه آگهی شما:</strong>
        <span><?php echo e($adData['title'] ?? '—'); ?></span>
    </div>

    <div class="auth-tabs">
        <div id="tab-login" class="tab-content active">
            <form method="post" action="<?php echo e(route('guest.ad.process', $token)); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="login">
                <div class="form-grid">
                    <label class="wide">شماره موبایل
                        <input type="text" name="mobile" value="<?php echo e(old('mobile', $adData['mobile_1'] ?? '')); ?>" inputmode="numeric" placeholder="09123456789" required>
                    </label>
                    <label class="wide">گذرواژه
                        <input type="password" name="password" required placeholder="گذرواژه خود را وارد کنید">
                    </label>
                </div>
                <?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="form-error"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <button class="button" type="submit">ورود و ثبت آگهی</button>
                <p class="auth-hint">رمز عبور به شماره موبایل شما پیامک شده است.</p>
            </form>
        </div>
    </div>

    <p class="step2-note">با ورود، آگهی شما ثبت خواهد شد.</p>
</section>

<script>
function showTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
    document.getElementById('tab-' + tab).classList.add('active');
    if (btn) btn.classList.add('active');
}
</script>

<style>
.steps-bar{display:flex;gap:0;margin-bottom:1.5rem;border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;}
.step{flex:1;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.75rem 1rem;background:#f9fafb;color:#9ca3af;font-size:.875rem;font-weight:600;border-left:1px solid #e5e7eb;}
.step:last-child{border-left:none;}
.step.active{background:#f0fdfa;color:#0f766e;}
.step.done{background:#f0fdfa;color:#059669;}
.step-num{display:inline-flex;align-items:center;justify-content:center;width:1.5rem;height:1.5rem;border-radius:50%;background:#e5e7eb;color:#6b7280;font-size:.75rem;font-weight:700;}
.step.active .step-num{background:#14b8a6;color:#fff;}
.step.done .step-num{background:#10b981;color:#fff;}
.ad-summary{display:flex;align-items:center;gap:.75rem;margin:0 0 1.5rem;padding:.75rem 1rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:.625rem;font-size:.875rem;}
.ad-summary strong{color:#374151;white-space:nowrap;}
.auth-tabs{margin-bottom:1rem;}
.tab-buttons{display:flex;gap:0;margin-bottom:1rem;border:1px solid #e5e7eb;border-radius:.5rem;overflow:hidden;}
.tab-btn{flex:1;padding:.625rem;background:#f9fafb;border:0;font-size:.875rem;font-weight:600;color:#6b7280;cursor:pointer;transition:all .15s;}
.tab-btn:first-child{border-left:1px solid #e5e7eb;}
.tab-btn.active{background:#fff;color:#0f766e;}
.tab-btn:hover:not(.active){background:#f3f4f6;}
.tab-content{display:none;}
.tab-content.active{display:block;}
.auth-hint{font-size:.8rem;color:#6b7280;margin-top:.75rem;text-align:center;}
.form-error{color:#dc2626;font-size:.8rem;margin-top:.25rem;}
.step2-note{font-size:.8rem;color:#9ca3af;margin-top:1rem;text-align:center;}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'تأیید ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/guest/ad-confirm.blade.php ENDPATH**/ ?>