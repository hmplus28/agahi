<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="صفحه‌بندی" class="pagination-wrap">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="pg-btn pg-prev pg-disabled" aria-disabled="true" aria-label="صفحهٔ قبل">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" class="pg-btn pg-prev" aria-label="صفحهٔ قبل">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="pg-ellipsis"><?php echo e($element); ?></span>
            <?php endif; ?>

            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="pg-btn pg-active" aria-current="page"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" class="pg-btn" aria-label="صفحهٔ <?php echo e($page); ?>"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" class="pg-btn pg-next" aria-label="صفحهٔ بعد">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        <?php else: ?>
            <span class="pg-btn pg-next pg-disabled" aria-disabled="true" aria-label="صفحهٔ بعد">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        <?php endif; ?>
    </nav>
<?php endif; ?>
<?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/vendor/pagination/tailwind.blade.php ENDPATH**/ ?>