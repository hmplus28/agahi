<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>ثبت آگهی</h1>
    <p class="lead">آگهی پس از بررسی مدیر منتشر خواهد شد. از ثبت اطلاعات حساس خودداری کنید.</p>
    <form method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-grid">
            <label class="wide">عنوان آگهی
                <input name="title" value="<?php echo e(old('title')); ?>" maxlength="300" required>
            </label>
            <label class="wide">توضیحات
                <textarea name="description" maxlength="6000" rows="8" required><?php echo e(old('description')); ?></textarea>
            </label>
            <div class="wide">
                <?php if (isset($component)) { $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.category-modal','data' => ['categories' => $categories,'name' => 'category_id','selectedCategoryId' => old('category_id')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('category-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'name' => 'category_id','selected-category-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('category_id'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae)): ?>
<?php $attributes = $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae; ?>
<?php unset($__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae)): ?>
<?php $component = $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae; ?>
<?php unset($__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae); ?>
<?php endif; ?>
            </div>
            <label>شهر
                <select name="city_id" required>
                    <option value="">انتخاب کنید</option>
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city->id); ?>" <?php if(old('city_id')==$city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>قیمت (ریال)
                <input type="number" name="price" min="0" value="<?php echo e(old('price')); ?>">
            </label>
            <label>نام و نام خانوادگی
                <input name="full_name" value="<?php echo e(old('full_name', auth()->user()->name)); ?>">
            </label>
            <label>نام کسب‌وکار
                <input name="business_name" value="<?php echo e(old('business_name')); ?>">
            </label>
            <label>موبایل اصلی
                <input name="mobile_1" value="<?php echo e(old('mobile_1', auth()->user()->mobile)); ?>" inputmode="numeric" required>
            </label>
            <label>موبایل همراه (تکراری برای تماس)
                <input name="hamrah_1" value="<?php echo e(old('hamrah_1', auth()->user()->mobile)); ?>" inputmode="numeric">
            </label>
            <label>تلفن ثابت ثبت‌کننده
                <input name="sobit_1" value="<?php echo e(old('sobit_1')); ?>" inputmode="numeric">
            </label>
            <label>موبایل دوم
                <input name="mobile_2" value="<?php echo e(old('mobile_2')); ?>" inputmode="numeric">
            </label>
            <label>تلفن
                <input name="phone_1" value="<?php echo e(old('phone_1')); ?>">
            </label>
            <label>ایمیل
                <input type="email" name="email" value="<?php echo e(old('email', auth()->user()->email)); ?>">
            </label>
            <label class="wide">آدرس
                <input name="address" value="<?php echo e(old('address')); ?>" maxlength="500">
            </label>
            <label class="wide">کلمات کلیدی (اختیاری)
                <input type="hidden" name="keywords_json" id="keywords-json" value="<?php echo e(old('keywords_json', is_array(old('keywords')) ? json_encode(old('keywords')) : '')); ?>">
            </label>
            <label class="wide">تصاویر (حداکثر ۵ فایل، JPG/PNG/WebP)
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            </label>
        </div>
        <button class="button">ثبت و ارسال برای تأیید</button>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app',['title'=>'ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/user/ads/create.blade.php ENDPATH**/ ?>