<?php
    // Build 3-level tree from flat $categories (id, parent_id, title)
    $groups = $categories->whereNull('parent_id')->values();
    // selected ids
    $selectedCategoryId = $selectedCategoryId ?? null;
    $hideLabel = $hideLabel ?? false;
    $selectedCat = $categories->firstWhere('id', $selectedCategoryId);
    // compute pre-selected group/collection/sub ids
    $preGroupId = null; $preCollectionId = null; $preSubId = null;
    if ($selectedCat) {
        if ($selectedCat->parent_id) {
            $parent = $categories->firstWhere('id', $selectedCat->parent_id);
            if ($parent && $parent->parent_id) {
                $preGroupId = $parent->parent_id;
                $preCollectionId = $parent->id;
                $preSubId = $selectedCat->id;
            } else {
                $preGroupId = $parent ? $parent->id : null;
                $preCollectionId = $selectedCat->id;
            }
        } else {
            $preGroupId = $selectedCat->id;
        }
    }
?>

<div class="cat-modal" data-cat-modal data-name="<?php echo e($name ?? 'category_id'); ?>" data-selected="<?php echo e($selectedCategoryId ?? ''); ?>">
    <?php if (! ($hideLabel)): ?>
        <label>دسته‌بندی <span class="req">*</span></label>
    <?php endif; ?>
    <button type="button" class="cat-trigger" data-cat-open>
        <span data-cat-selected><?php echo e($selectedCat->title ?? 'انتخاب دسته‌بندی'); ?></span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
    </button>
    <input type="hidden" name="<?php echo e($name ?? 'category_id'); ?>" value="<?php echo e($selectedCategoryId ?? ''); ?>" data-cat-input>

    <div class="cat-overlay" data-cat-overlay>
        <div class="cat-modal-box" role="dialog" aria-modal="true">
            <div class="cat-modal-head">
                <h3>انتخاب دسته‌بندی</h3>
                <button type="button" class="cat-close" data-cat-close aria-label="بستن">✕</button>
            </div>
            <div class="cat-search">
                <input type="text" placeholder="جست‌وجوی دسته‌بندی…" data-cat-search>
            </div>
            <div class="cat-cols">
                <div class="cat-col" data-cat-group>
                    <div class="cat-col-title">گروه</div>
                    <div class="cat-list">
                        <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="cat-item<?php echo e($preGroupId===$g->id?' active':''); ?>" data-group="<?php echo e($g->id); ?>" data-title="<?php echo e($g->title); ?>"><?php echo e($g->title); ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="cat-col" data-cat-collection>
                    <div class="cat-col-title">مجموعه</div>
                    <div class="cat-list"></div>
                </div>
                <div class="cat-col" data-cat-sub>
                    <div class="cat-col-title">زیرمجموعه</div>
                    <div class="cat-list"></div>
                </div>
            </div>
            <div class="cat-modal-foot">
                <div class="cat-current" data-cat-current></div>
                <button type="button" class="button cat-confirm" data-cat-confirm disabled>تأیید</button>
            </div>
        </div>
    </div>
</div>

<?php
    $treeJson = $categories->map(fn($c) => [
        'id' => $c->id,
        'parent_id' => $c->parent_id,
        'title' => $c->title,
    ])->values();
?>
<script>
(function(){
    var tree = <?php echo json_encode($treeJson, 15, 512) ?>.filter(c=>c.id);
    var root = document.querySelector('[data-cat-modal]');
    if(!root) return;
    var trigger=root.querySelector('[data-cat-open]');
    var selectedEl=root.querySelector('[data-cat-selected]');
    var input=root.querySelector('[data-cat-input]');
    var overlay=root.querySelector('[data-cat-overlay]');
    var groupCol=root.querySelector('[data-cat-group] .cat-list');
    var collectionCol=root.querySelector('[data-cat-collection] .cat-list');
    var subCol=root.querySelector('[data-cat-sub] .cat-list');
    var searchEl=root.querySelector('[data-cat-search]');
    var currentEl=root.querySelector('[data-cat-current]');
    var confirmBtn=root.querySelector('[data-cat-confirm]');

    var byParent = {};
    tree.forEach(function(c){ (byParent[c.parent_id||'0']=byParent[c.parent_id||'0']||[]).push(c); });
    function childrenOf(pid){ return byParent[pid||'0']||[]; }
    function allCollections(){ var r=[]; childrenOf('0').forEach(function(g){ r=r.concat(childrenOf(g.id)); }); return r; }
    function allSubs(){ var r=[]; allCollections().forEach(function(c){ r=r.concat(childrenOf(c.id)); }); return r; }

    var selected={group:null,collection:null,sub:null};
    // initial selection
    <?php if($preGroupId): ?>
    selected.group=<?php echo json_encode((string)$preGroupId, 15, 512) ?>;
    <?php endif; ?>
    <?php if($preCollectionId): ?>
    selected.collection=<?php echo json_encode((string)$preCollectionId, 15, 512) ?>;
    <?php endif; ?>
    <?php if($preSubId): ?>
    selected.sub=<?php echo json_encode((string)$preSubId, 15, 512) ?>;
    <?php endif; ?>

    function findCat(id){ return tree.find(c=>String(c.id)===String(id)); }
    function setHidden(){
        var id=selected.sub||selected.collection||selected.group;
        if(id){ input.value=id; var cat=findCat(id); selectedEl.textContent=(cat?cat.title:'')||'انتخاب دسته‌بندی'; }
        else { input.value=''; }
    }
    function renderGroups(filter){
        groupCol.innerHTML='';
        var list=filter?childrenOf('0').filter(c=>c.title.indexOf(filter)>-1):childrenOf('0');
        list.forEach(function(g){
            var b=document.createElement('button');
            b.type='button'; b.className='cat-item'+(String(selected.group)===String(g.id)?' active':'');
            b.dataset.group=g.id; b.textContent=g.title;
            b.addEventListener('click',function(){ selectGroup(g.id); });
            groupCol.appendChild(b);
        });
    }
    function renderCollections(base, filter){
        collectionCol.innerHTML='';
        var list=base ? childrenOf(base) : allCollections();
        if(filter) list=list.filter(c=>c.title.indexOf(filter)>-1);
        list.forEach(function(c){
            var b=document.createElement('button');
            b.type='button'; b.className='cat-item'+(String(selected.collection)===String(c.id)?' active':'');
            b.dataset.collection=c.id; b.textContent=c.title;
            b.addEventListener('click',function(){ selectCollection(c.id); });
            collectionCol.appendChild(b);
        });
    }
    function renderSubs(base, filter){
        subCol.innerHTML='';
        var list=base ? childrenOf(base) : allSubs();
        if(filter) list=list.filter(c=>c.title.indexOf(filter)>-1);
        list.forEach(function(s){
            var b=document.createElement('button');
            b.type='button'; b.className='cat-item'+(String(selected.sub)===String(s.id)?' active':'');
            b.dataset.sub=s.id; b.textContent=s.title;
            b.addEventListener('click',function(){ selectSub(s.id); });
            subCol.appendChild(b);
        });
    }
    function selectGroup(id){
        selected.group=String(id); selected.collection=null; selected.sub=null;
        renderGroups(); renderCollections(id); subCol.innerHTML='';
        updateFoot(); updateActive();
        clearSearch();
    }
    function selectCollection(id){
        selected.collection=String(id); selected.sub=null;
        renderCollections(selected.group); renderSubs(id);
        updateFoot(); updateActive();
        clearSearch();
    }
    function selectSub(id){
        selected.sub=String(id);
        renderSubs(selected.collection);
        updateFoot(); updateActive();
        clearSearch();
    }
    function updateFoot(){
        var parts=[];
        if(selected.group){ var g=findCat(selected.group); if(g) parts.push(g.title); }
        if(selected.collection){ var c=findCat(selected.collection); if(c) parts.push(c.title); }
        if(selected.sub){ var s=findCat(selected.sub); if(s) parts.push(s.title); }
        currentEl.textContent=parts.join(' / ');
        confirmBtn.disabled=!parts.length;
    }
    function updateActive(){
        groupCol.querySelectorAll('.cat-item').forEach(function(b){ b.classList.toggle('active', String(selected.group)===b.dataset.group); });
        collectionCol.querySelectorAll('.cat-item').forEach(function(b){ b.classList.toggle('active', String(selected.collection)===b.dataset.collection); });
        subCol.querySelectorAll('.cat-item').forEach(function(b){ b.classList.toggle('active', String(selected.sub)===b.dataset.sub); });
    }
    function clearSearch(){ searchEl.value=''; }

    function openOverlay(){
        renderGroups();
        if(selected.group){ renderCollections(selected.group); } else { collectionCol.innerHTML=''; subCol.innerHTML=''; }
        if(selected.collection){ renderSubs(selected.collection); } else if(selected.group){ subCol.innerHTML=''; }
        updateFoot(); updateActive();
        overlay.classList.add('open');
    }
    function closeOverlay(){ overlay.classList.remove('open'); }

    var searchTimer;
    searchEl.addEventListener('input',function(){
        var q=this.value.trim();
        clearTimeout(searchTimer);
        searchTimer=setTimeout(function(){ applySearch(q); },200);
    });
    function applySearch(q){
        if(!q){ renderGroups(); if(selected.collection) renderCollections(selected.group); if(selected.sub) renderSubs(selected.collection); else if(selected.collection) renderSubs(selected.collection); return; }
        // search across all three columns simultaneously
        renderGroups(q);
        renderCollections(null, q);
        renderSubs(null, q);
    }

    trigger.addEventListener('click',function(){ openOverlay(); });
    root.querySelector('[data-cat-close]').addEventListener('click',function(){ closeOverlay(); });
    overlay.addEventListener('click',function(e){ if(e.target===overlay) closeOverlay(); });

    confirmBtn.addEventListener('click',function(){
        setHidden();
        closeOverlay();
        selectedEl.style.color='';
    });

    setHidden();
})();
</script>
<?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/components/category-modal.blade.php ENDPATH**/ ?>