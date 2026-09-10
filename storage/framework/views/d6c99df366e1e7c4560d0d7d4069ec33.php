<?php $__env->startSection('content'); ?>
<article class="static-page">
    <h1>درباره ما</h1>
    <p class="muted"><?php echo e(config('app.name')); ?> — بازار آگهی‌های محلی، ساده و مطمئن</p>

    <section>
        <h2>ما که هستیم؟</h2>
        <p><?php echo e(config('app.name')); ?> یک سامانهٔ درج آگهی محلی است که با هدف ساده‌سازی خرید و فروش در سطح شهر طراحی شده است. ما معتقدیم پیدا کردن کالا، خدمت یا مشتری نباید پیچیده و پر هزینه باشد؛ به همین دلیل بستری سریع، امن و رایگان برای آگهی‌های شما فراهم کرده‌ایم.</p>
    </section>

    <section>
        <h2>چرا ما؟</h2>
        <ul>
            <li><strong>سادگی:</strong> ثبت آگهی در چند دقیقه، بدون مراحل پیچیده.</li>
            <li><strong>امنیت:</strong> بازبینی آگهی‌ها پیش از انتشار و امکان گزارش تخلف.</li>
            <li><strong>محلی:</strong> تمرکز بر آگهی‌های شهر و محلهٔ شما.</li>
            <li><strong>شفافیت:</strong> نمایش وضعیت آگهی و آمار بازدید در پنل کاربری.</li>
        </ul>
    </section>

    <section>
        <h2>پشتیبانی</h2>
        <p>تیم پشتیبانی ما پاسخگوی سؤالات و مشکلات شماست. کافی است وارد حساب کاربری خود شوید و از بخش «تیکت‌های پشتیبانی» پیام خود را ثبت کنید.</p>
    </section>
</article>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'درباره ما | '.config('app.name'),'robots'=>'index, follow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/public/pages/about.blade.php ENDPATH**/ ?>