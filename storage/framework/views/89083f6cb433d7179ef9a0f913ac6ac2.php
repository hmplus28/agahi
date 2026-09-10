<?php $__env->startSection('content'); ?>
<article class="static-page">
    <h1>تماس با ما</h1>
    <p class="muted"><?php echo e(config('app.name')); ?> — بازار آگهی‌های محلی، ساده و مطمئن</p>

    <section>
        <h2>ارتباط با پشتیبانی</h2>
        <p>برای ارتباط با تیم پشتیبانی می‌توانید از طریق حساب کاربری خود تیکت ارسال کنید. همچنین برای سؤالات عمومی و پیشنهادات از طریق ایمیل زیر با ما در تماس باشید.</p>
        <ul>
            <li><strong>ایمیل:</strong> support@example.com</li>
            <li><strong>ساعت پاسخگویی:</strong> شنبه تا پنج‌شنبه، ۹ صبح تا ۶ بعدازظهر</li>
        </ul>
    </section>

    <section>
        <h2>پیشنهادات و انتقادات</h2>
        <p>نظرات شما برای ما ارزشمند است. اگر پیشنهاد یا انتقادی دارید، خوشحال می‌شویم آن را بشنویم.</p>
    </section>
</article>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'تماس با ما | '.config('app.name'),'robots'=>'index, follow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/public/pages/contact.blade.php ENDPATH**/ ?>