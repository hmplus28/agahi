<?php $__env->startSection('content'); ?>
<section class="form-page">
    <h1>ویرایش آگهی</h1>
    <p class="lead">ویرایش آگهی فعال، آن را برای بررسی مجدد ارسال می‌کند.</p>
    <form method="post" action="<?php echo e(route('user.ads.update', $ad)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-grid">
            <label class="wide">عنوان آگهی
                <input name="title" value="<?php echo e(old('title', $ad->title)); ?>" maxlength="300" required>
            </label>
            <label class="wide">توضیحات
                <textarea name="description" maxlength="6000" rows="8" required><?php echo e(old('description', $ad->description)); ?></textarea>
            </label>
            <div class="wide">
                <?php if (isset($component)) { $__componentOriginald3854c5ab0ac3ef82e05ea7740de3fae = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald3854c5ab0ac3ef82e05ea7740de3fae = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.category-modal','data' => ['categories' => $categories,'name' => 'category_id','selectedCategoryId' => old('category_id', $ad->category_id)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('category-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'name' => 'category_id','selected-category-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('category_id', $ad->category_id))]); ?>
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
                    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city->id); ?>" <?php if(old('city_id', $ad->city_id) == $city->id): echo 'selected'; endif; ?>><?php echo e($city->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>قیمت (ریال)
                <input type="number" name="price" min="0" value="<?php echo e(old('price', $ad->price)); ?>">
            </label>
            <label>نام و نام خانوادگی
                <input name="full_name" value="<?php echo e(old('full_name', $ad->full_name)); ?>">
            </label>
            <label>نام کسب‌وکار
                <input name="business_name" value="<?php echo e(old('business_name', $ad->business_name)); ?>">
            </label>
            <label>موبایل اصلی
                <input name="mobile_1" value="<?php echo e(old('mobile_1', $ad->mobile_1)); ?>" inputmode="numeric" required>
            </label>
            <label>موبایل همراه (تکراری برای تماس)
                <input name="hamrah_1" value="<?php echo e(old('hamrah_1', $ad->mobile_1)); ?>" inputmode="numeric">
            </label>
            <label>تلفن ثابت ثبت‌کننده
                <input name="sobit_1" value="<?php echo e(old('sobit_1')); ?>" inputmode="numeric">
            </label>
            <label>موبایل دوم
                <input name="mobile_2" value="<?php echo e(old('mobile_2', $ad->mobile_2)); ?>" inputmode="numeric">
            </label>
            <label>تلفن
                <input name="phone_1" value="<?php echo e(old('phone_1', $ad->phone_1)); ?>">
            </label>
            <label>ایمیل
                <input type="email" name="email" value="<?php echo e(old('email', $ad->email)); ?>">
            </label>
            <label class="wide">آدرس
                <input name="address" value="<?php echo e(old('address', $ad->address)); ?>" maxlength="500">
            </label>
            <label class="wide">افزودن تصویر جدید
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            </label>
            <input type="hidden" name="keywords_json" value="<?php echo e(old('keywords_json')); ?>">
        </div>
        <div class="image-strip">
            <?php $__currentLoopData = $ad->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <img src="<?php echo e($image->thumbUrl()); ?>" width="120" height="90" loading="lazy" alt="تصویر آگهی">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button class="button">ذخیرهٔ ویرایش</button>
    </form>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'ویرایش آگهی | ' . config('app.name'), 'robots' => 'noindex, nofollow'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/z/my-project/agahi/resources/views/user/ads/edit.blade.php ENDPATH**/ ?>