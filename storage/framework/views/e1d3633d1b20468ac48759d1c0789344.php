<?php $__env->startPush('head'); ?>
<meta name="article:published_time" content="<?php echo e($ad->published_at?->toAtomString()); ?>">
<meta name="article:modified_time" content="<?php echo e($ad->updated_at?->toAtomString()); ?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?php echo e($ad->title); ?> در <?php echo e($ad->city?->name ?? 'ایران'); ?>">
<meta property="og:description" content="<?php echo e(\Illuminate\Support\Str::limit(strip_tags($ad->description),200)); ?>">
<?php
    $breadcrumbItems = collect($seo->breadcrumbForAd($ad))->values()->map(function ($item, $index) {
        return ['@type' => 'ListItem', 'position' => $index + 1, 'name' => $item['name'], 'item' => $item['url']];
    })->all();
    $breadcrumbLd = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];

    $productLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $ad->title,
        'description' => \Illuminate\Support\Str::limit(strip_tags($ad->description), 500),
        'url' => $ad->publicUrl(),
        'image' => optional($ad->images->firstWhere('is_primary', true) ?? $ad->images->first())->displayUrl() ?? null,
        'datePublished' => $ad->published_at?->toAtomString(),
        'dateModified' => $ad->updated_at?->toAtomString(),
        'offers' => [
            '@type' => 'Offer',
            'price' => $ad->price > 0 ? number_format($ad->priceInToman(), 0, '.', '') : '0',
            'priceCurrency' => 'IRT',
            'availability' => $ad->isExpired() ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
            'url' => $ad->publicUrl(),
            'seller' => [
                '@type' => 'Person',
                'name' => $ad->full_name,
            ],
        ],
        'identifier' => $ad->code,
        'category' => $ad->category?->title,
        'brand' => [
            '@type' => 'Organization',
            'name' => $ad->business_name ?? config('app.name'),
        ],
    ];
?>
<script type="application/ld+json"><?php echo json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES); ?></script>
<script type="application/ld+json"><?php echo json_encode($productLd, JSON_UNESCAPED_SLASHES); ?></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $contactLocked = $ad->isOlderThanOneYear() || $ad->isExpired();
?>
<section class="page-top">
    <a class="button button-outline back-button" href="<?php echo e(url()->previous()); ?>">بازگشت</a>
</section>

<nav class="breadcrumb" aria-label="مسیر صفحه">
    <a href="<?php echo e(route('home')); ?>">خانه</a><span aria-hidden="true">/</span>
    <?php if($ad->category): ?>
        <a href="<?php echo e(route('categories.show',$ad->category)); ?>"><?php echo e($ad->category->title); ?></a><span aria-hidden="true">/</span>
    <?php endif; ?>
    <span><?php echo e($ad->title); ?></span>
</nav>

<?php if($ad->isOlderThanOneYear() || $ad->isExpired()): ?>
    <div class="expired-banner expired-banner--big" role="alert">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <div>
            <strong>این آگهی منقضی شده است.</strong>
            <p>بیش از یک سال از انتشار این آگهی گذشته است و دیگر قابل استفاده نیست. برای فعال‌سازی مجدد، هزینهٔ تمدید را پرداخت کنید.</p>
        </div>
        <?php if(auth()->guard()->check()): ?>
            <form method="post" action="<?php echo e(route('payments.purchase')); ?>" class="expired-banner__actions">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="ad" value="<?php echo e($ad->code); ?>">
                <button class="button button-renew" type="submit">پرداخت و تمدید آگهی</button>
            </form>
        <?php else: ?>
            <div class="expired-banner__actions">
                <a class="button button-renew" href="<?php echo e(route('login')); ?>">ورود برای تمدید آگهی</a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="share-bar" aria-label="اشتراک‌گذاری">
    <span class="share-label">اشتراک‌گذاری:</span>
    <a class="share-btn share-whatsapp" href="https://wa.me/?text=<?php echo e(urlencode($ad->title.' '.$ad->publicUrl())); ?>" target="_blank" rel="noopener" aria-label="اشتراک در واتساپ">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
    </a>
    <a class="share-btn share-telegram" href="https://t.me/share/url?url=<?php echo e(urlencode($ad->publicUrl())); ?>&text=<?php echo e(urlencode($ad->title)); ?>" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
    </a>
    <button class="share-btn share-copy" type="button" data-copy-url="<?php echo e($ad->publicUrl()); ?>" aria-label="کپی لینک">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
    </button>
    <span id="copy-msg" class="copy-success" style="display:none">کپی شد!</span>
</div>

<article class="ad-detail" itemscope itemtype="https://schema.org/Product">
    
    <aside class="ad-sidecard">
        <div class="ad-detail-heading">
            <h1 itemprop="name"><?php echo e($ad->title); ?></h1>
            <?php if($ad->is_featured): ?><span class="badge">ویژه</span><?php endif; ?>
        </div>

        <p class="detail-price" dir="auto" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
            <meta itemprop="priceCurrency" content="IRT">
            <meta itemprop="price" content="<?php echo e($ad->priceInToman()); ?>">
            <?php echo e($ad->priceLabel()); ?>

        </p>

        <p class="ad-date">
            <?php
                $displayDate = $ad->updated_at && $ad->updated_at->gt($ad->created_at) ? $ad->updated_at : $ad->created_at;
            ?>
            <?php echo e(jdate_human($displayDate)); ?> در <?php echo e($ad->city?->name ?? '—'); ?>

        </p>

        <dl class="details">
            <dt>دسته‌بندی</dt>
            <dd><?php echo e($ad->category?->title ?? '—'); ?></dd>
            <dt>کد آگهی</dt>
            <dd dir="ltr" itemprop="identifier"><?php echo e($ad->code); ?></dd>
            <dt>بازدید</dt>
            <dd><?php echo e(number_format($ad->views_count)); ?></dd>
            <dt>تاریخ انتشار</dt>
            <dd><?php echo e(jdate($ad->published_at)); ?></dd>
            <?php if($ad->updated_at && $ad->updated_at->gt($ad->created_at)): ?>
                <dt>تاریخ بروزرسانی</dt>
                <dd><?php echo e(jdate($ad->updated_at)); ?></dd>
            <?php endif; ?>
            <?php if($ad->business_name): ?>
                <dt>کسب‌وکار</dt>
                <dd><?php echo e($ad->business_name); ?></dd>
            <?php endif; ?>
        </dl>

        <?php if($ad->keywords && count($ad->keywords) > 0): ?>
            <div class="sidecard-keywords">
                <h3>کلمات کلیدی</h3>
                <div class="keyword-tags">
                    <?php $__currentLoopData = $ad->keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="keyword-badge"><?php echo e($keyword); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(($ad->mobile_1 && $ad->show_mobile_1) || $ad->mobile_2 || $ad->phone_1 || $ad->phone_2 || $ad->email): ?>
            <?php if($contactLocked): ?>
                <div class="contact-locked" aria-label="اطلاعات تماس به دلیل انقضای آگهی در دسترس نیست">
                    <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                    <span>این آگهی منقضی شده و اطلاعات تماس آن قابل مشاهده نیست.</span>
                </div>
            <?php else: ?>
                <button class="button contact-action" type="button" onclick="document.getElementById('contact-dialog').showModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    تماس با آگهی‌دهنده
                </button>
            <?php endif; ?>
        <?php endif; ?>
    </aside>

    
    <section class="gallery" aria-label="تصاویر آگهی">
        <?php $__empty_1 = true; $__currentLoopData = $ad->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <img
                src="<?php echo e($image->displayUrl()); ?>"
                width="<?php echo e($image->width); ?>"
                height="<?php echo e($image->height); ?>"
                <?php if($loop->first): ?> fetchpriority="high" <?php else: ?> loading="lazy" <?php endif; ?>
                alt="<?php echo e($ad->title); ?> — تصویر <?php echo e($loop->iteration); ?>"
                itemprop="image"
            >
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="image-placeholder gallery-placeholder">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m5.5 17 4.5-4 3 2.7 2.5-2.2 3 3.5"/></svg>
                <span>تصویری برای این آگهی ثبت نشده است.</span>
            </div>
        <?php endif; ?>
    </section>

    
    <section class="description" itemprop="description">
        <h2>توضیحات آگهی</h2>
        <p><?php echo nl2br(e($ad->description)); ?></p>

        <?php if(in_array($ad->category?->title, ['خوراکی', 'مواد غذایی'], true) || in_array($ad->category?->id, (array) config('agahi.food_warning_categories'), true)): ?>
            <div class="food-warning" style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:1rem;margin:1rem 0;">
                <strong style="color:#92400e;">هشدار مهم:</strong>
                <p style="margin:.5rem 0 0;color:#92400e;"><?php echo e(config('agahi.food_warning_text')); ?></p>
            </div>
        <?php endif; ?>

        <?php if($ad->keywords && count($ad->keywords) > 0): ?>
            <div class="ad-keywords">
                <h3>کلمات کلیدی</h3>
                <div class="keyword-tags">
                    <?php $__currentLoopData = $ad->keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="keyword-badge"><?php echo e($keyword); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if(!$contactLocked && $ad->links->isNotEmpty()): ?>
            <?php
                $linkLabels = [
                    'site' => 'سایت', 'aparat' => 'ویدیو آپارات', 'eitaa' => 'کانال ایتا',
                    'bale' => 'کانال بله', 'rubika' => 'کانال روبیکا', 'whatsapp' => 'واتس‌آپ',
                    'telegram' => 'تلگرام', 'instagram' => 'اینستاگرام',
                ];
            ?>
            <h2 style="margin-top:22px">لینک‌های مرتبط</h2>
            <ul>
                <?php $__currentLoopData = $ad->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><strong><?php echo e($linkLabels[$link->type] ?? 'لینک'); ?>:</strong>
                        <?php if(in_array(parse_url($link->url, PHP_URL_SCHEME) ?? '', ['http', 'https'], true)): ?>
                            <a rel="ugc nofollow" target="_blank" href="<?php echo e($link->url); ?>"><?php echo e($link->url); ?></a>
                        <?php else: ?>
                            <span dir="ltr"><?php echo e(e($link->url)); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
    </section>

    
    <section class="report-box" aria-labelledby="report-title">
        <h2 id="report-title">گزارش تخلف</h2>
        <p class="muted" style="font-size:.85rem;">آگهی خلاف قوانین است؟ با ثبت گزارش، تیم مدیریت آن را بررسی می‌کند.</p>
        <button class="button button-outline" type="button" onclick="document.getElementById('report-dialog').showModal()">ثبت گزارش تخلف</button>
    </section>
</article>

<dialog id="report-dialog" class="modal" aria-labelledby="report-dialog-title">
    <div class="modal__header">
        <h2 id="report-dialog-title">گزارش تخلف آگهی</h2>
        <button class="link-button" type="button" onclick="document.getElementById('report-dialog').close()" aria-label="بستن">✕</button>
    </div>
    <form method="post" action="<?php echo e(route('ads.reports.store',$ad->code)); ?>">
        <?php echo csrf_field(); ?>
        <label class="field-label">
            دلیل گزارش
            <select name="reason" required>
                <option value="">انتخاب کنید</option>
                <option value="no_response">عدم پاسخگویی</option>
                <option value="fraud">کلاهبرداری</option>
                <option value="false_information">خلاف واقع</option>
                <option value="illegal">محتوای غیرمجاز</option>
                <option value="inappropriate">محتوای نامناسب</option>
                <option value="overpriced">گران‌فروشی</option>
                <option value="other">سایر</option>
            </select>
        </label>
        <label class="field-label">
            توضیح (اختیاری)
            <textarea name="description" rows="3" maxlength="2000" placeholder="در صورت نیاز توضیح دهید…"></textarea>
        </label>
        <div style="display:flex;gap:.5rem;justify-content:flex-start;">
            <button class="button" type="submit">ثبت گزارش</button>
            <button class="button button-outline" type="button" onclick="document.getElementById('report-dialog').close()">انصراف</button>
        </div>
    </form>
</dialog>

<?php if(!$contactLocked): ?>
<dialog id="contact-dialog" class="modal" aria-labelledby="contact-dialog-title">
    <div class="modal__header">
        <h2 id="contact-dialog-title">اطلاعات تماس آگهی‌دهنده</h2>
        <button class="link-button" type="button" onclick="document.getElementById('contact-dialog').close()" aria-label="بستن">✕</button>
    </div>
    <div class="contact-modal-body">
            <?php if($ad->mobile_1 && $ad->show_mobile_1): ?>
                <div class="contact-row">
                    <span class="contact-label">موبایل اصلی</span>
                    <a class="contact-value" dir="ltr" href="tel:<?php echo e($ad->mobile_1); ?>"><?php echo e($ad->mobile_1); ?></a>
                </div>
            <?php endif; ?>
        <?php if($ad->mobile_2): ?>
            <div class="contact-row">
                <span class="contact-label">موبایل دوم</span>
                <a class="contact-value" dir="ltr" href="tel:<?php echo e($ad->mobile_2); ?>"><?php echo e($ad->mobile_2); ?></a>
            </div>
        <?php endif; ?>
        <?php if($ad->phone_1): ?>
            <div class="contact-row">
                <span class="contact-label">تلفن ثابت</span>
                <a class="contact-value" dir="ltr" href="tel:<?php echo e($ad->phone_1); ?>"><?php echo e($ad->phone_1); ?></a>
            </div>
        <?php endif; ?>
        <?php if($ad->phone_2): ?>
            <div class="contact-row">
                <span class="contact-label">تلفن ثابت ۲</span>
                <a class="contact-value" dir="ltr" href="tel:<?php echo e($ad->phone_2); ?>"><?php echo e($ad->phone_2); ?></a>
            </div>
        <?php endif; ?>
        <?php if($ad->email): ?>
            <div class="contact-row">
                <span class="contact-label">ایمیل</span>
                <a class="contact-value" dir="ltr" href="mailto:<?php echo e($ad->email); ?>"><?php echo e($ad->email); ?></a>
            </div>
        <?php endif; ?>
        <button class="button" style="width:100%;margin-top:.5rem;" type="button" onclick="document.getElementById('contact-dialog').close()">بستن</button>
    </div>
</dialog>
<?php endif; ?>

<?php if($related->isNotEmpty()): ?>
<section class="section related-section" aria-labelledby="related-title">
    <div class="section-heading">
        <div>
            <h2 id="related-title">آگهی‌های مرتبط</h2>
            <p>گزینه‌های مشابهی که ممکن است برای شما مناسب باشند.</p>
        </div>
    </div>
    <div class="ad-grid">
        <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedAd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginal0d17d925a6f3d01382ddadaafaf99443 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ad-card','data' => ['ad' => $relatedAd]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ad-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['ad' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relatedAd)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $attributes = $__attributesOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__attributesOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443)): ?>
<?php $component = $__componentOriginal0d17d925a6f3d01382ddadaafaf99443; ?>
<?php unset($__componentOriginal0d17d925a6f3d01382ddadaafaf99443); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('head'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtn = document.querySelector('.share-copy');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var url = this.dataset.copyUrl;
            if (url && navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function() {
                    var el = document.getElementById('copy-msg');
                    if (el) { el.style.display = 'inline'; setTimeout(function() { el.style.display = 'none'; }, 2000); }
                });
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app',['title'=>$ad->title.' در '.($ad->city?->name ?? 'ایران').' | '.config('app.name'),'description'=>\Illuminate\Support\Str::limit(strip_tags($ad->description),160),'canonical'=>$ad->publicUrl(),'openGraph'=>['image'=>optional($ad->images->firstWhere('is_primary',true) ?? $ad->images->first())->displayUrl()]], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/public/ads/show.blade.php ENDPATH**/ ?>