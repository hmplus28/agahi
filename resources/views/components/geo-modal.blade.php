@php
    // Province/city picker popup - mirrors x-category-modal UX (two columns,
    // search box, confirm footer). Province and city stay together in ONE popup.
    $nameProvince = $nameProvince ?? 'province';
    $nameCity = $nameCity ?? 'city';
    $selectedProvinceId = $selectedProvinceId ?? null;
    $selectedCityId = $selectedCityId ?? null;
    $hideLabel = $hideLabel ?? false;
    $placeholder = $placeholder ?? 'استان / شهر';
    $selProvince = $provinces->firstWhere('id', $selectedProvinceId);
    $selCity = $cities->firstWhere('id', $selectedCityId);
    if ($selCity && ! $selProvince) {
        $selProvince = $provinces->firstWhere('id', data_get($selCity, 'province_id'));
    }
    $triggerText = trim((string) (data_get($selProvince, 'name') ?? '').($selProvince && $selCity ? '، ' : '').(data_get($selCity, 'name') ?? ''));
@endphp

<div class="geo-modal" data-geo-modal>
    @unless($hideLabel)
        <label>استان / شهر</label>
    @endunless
    <button type="button" class="geo-trigger" data-geo-open>
        <span class="geo-trigger-main">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <span data-geo-selected>{{ $triggerText !== '' ? $triggerText : $placeholder }}</span>
        </span>
        <svg class="geo-caret" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 9l-7 7-7-7"/></svg>
    </button>
    <input type="hidden" name="{{ $nameProvince }}" value="{{ $selProvince?->id ?? '' }}" data-geo-province-input>
    <input type="hidden" name="{{ $nameCity }}" value="{{ $selCity?->id ?? '' }}" data-geo-city-input>

    <div class="cat-overlay" data-geo-overlay>
        <div class="cat-modal-box" role="dialog" aria-modal="true" aria-label="انتخاب استان و شهر">
            <div class="cat-modal-head">
                <h3>انتخاب استان و شهر</h3>
                <button type="button" class="cat-close" data-geo-close aria-label="بستن">✕</button>
            </div>
            <div class="cat-search">
                <input type="text" placeholder="جست‌وجوی استان یا شهر…" data-geo-search>
            </div>
            <div class="cat-cols geo-cols">
                <div class="cat-col" data-geo-province-col>
                    <div class="cat-col-title">استان</div>
                    <div class="cat-list" data-geo-province-list></div>
                </div>
                <div class="cat-col" data-geo-city-col>
                    <div class="cat-col-title">شهر</div>
                    <div class="cat-list" data-geo-city-list></div>
                </div>
            </div>
            <div class="cat-modal-foot">
                <div class="cat-current" data-geo-current></div>
                <div class="geo-foot-actions">
                    <button type="button" class="geo-clear" data-geo-clear>حذف انتخاب</button>
                    <button type="button" class="cat-confirm" data-geo-confirm>تأیید</button>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $provincesJson = $provinces->map(fn ($p) => ['id' => (string) data_get($p, 'id'), 'name' => (string) data_get($p, 'name')])->values();
    $citiesJson = $cities->map(fn ($c) => ['id' => (string) data_get($c, 'id'), 'name' => (string) data_get($c, 'name'), 'province_id' => (string) data_get($c, 'province_id')])->values();
@endphp
<script>
(function(){
    var provinces = @json($provincesJson);
    var cities = @json($citiesJson);
    var root = document.querySelector('[data-geo-modal]');
    if(!root) return;
    var trigger = root.querySelector('[data-geo-open]');
    var selectedEl = root.querySelector('[data-geo-selected]');
    var provinceInput = root.querySelector('[data-geo-province-input]');
    var cityInput = root.querySelector('[data-geo-city-input]');
    var overlay = root.querySelector('[data-geo-overlay]');
    var provinceList = root.querySelector('[data-geo-province-list]');
    var cityList = root.querySelector('[data-geo-city-list]');
    var searchEl = root.querySelector('[data-geo-search]');
    var currentEl = root.querySelector('[data-geo-current]');
    var confirmBtn = root.querySelector('[data-geo-confirm]');
    var clearBtn = root.querySelector('[data-geo-clear]');

    // working selection inside the modal (strings for stable comparison)
    var pending = {
        province: provinceInput.value || null,
        city: cityInput.value || null
    };
    // pending city must belong to pending province on load
    if (pending.city) {
        var pc = cities.find(function (c) { return c.id === String(pending.city); });
        if (pc) pending.province = pc.province_id;
    }

    function findProvince(id) { return provinces.find(function (p) { return p.id === String(id); }); }
    function findCity(id) { return cities.find(function (c) { return c.id === String(id); }); }

    function renderProvinces(filter) {
        provinceList.innerHTML = '';
        provinces
            .filter(function (p) { return !filter || p.name.indexOf(filter) > -1; })
            .forEach(function (p) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'cat-item' + (String(pending.province) === p.id ? ' active' : '');
                b.textContent = p.name;
                b.addEventListener('click', function () {
                    pending.province = (String(pending.province) === p.id) ? null : p.id;
                    pending.city = null;
                    renderAll(searchEl.value.trim());
                });
                provinceList.appendChild(b);
            });
    }

    function renderCities(filter) {
        cityList.innerHTML = '';
        var base = pending.province
            ? cities.filter(function (c) { return c.province_id === String(pending.province); })
            : cities;
        base
            .filter(function (c) { return !filter || c.name.indexOf(filter) > -1; })
            .forEach(function (c) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'cat-item' + (String(pending.city) === c.id ? ' active' : '');
                b.textContent = c.name;
                b.addEventListener('click', function () {
                    if (String(pending.city) === c.id) {
                        pending.city = null;
                    } else {
                        pending.city = c.id;
                        pending.province = c.province_id; // auto-select parent province
                    }
                    renderAll(searchEl.value.trim());
                });
                cityList.appendChild(b);
            });
    }

    function renderAll(filter) {
        renderProvinces(filter);
        renderCities(filter);
        updateFoot();
    }

    function selectionText() {
        var p = pending.province ? findProvince(pending.province) : null;
        var c = pending.city ? findCity(pending.city) : null;
        var t = trim2((p ? p.name : '') + ((p && c) ? '، ' : '') + (c ? c.name : ''));
        return t;
    }
    function trim2(s) { return s.replace(/^\s+|\s+$/g, ''); }

    function updateFoot() {
        currentEl.textContent = selectionText() || 'همهٔ شهرها';
    }

    function applySelection() {
        provinceInput.value = pending.province || '';
        cityInput.value = pending.city || '';
        var t = selectionText();
        selectedEl.textContent = t || '{{ $placeholder }}';
        selectedEl.classList.toggle('has-value', t !== '');
    }

    function openOverlay() {
        renderAll('');
        overlay.classList.add('open');
        setTimeout(function () { searchEl.focus(); }, 50);
    }
    function closeOverlay() { overlay.classList.remove('open'); }

    var searchTimer;
    searchEl.addEventListener('input', function () {
        var q = this.value.trim();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { renderAll(q); }, 150);
    });

    trigger.addEventListener('click', openOverlay);
    root.querySelector('[data-geo-close]').addEventListener('click', closeOverlay);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) closeOverlay(); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('open')) closeOverlay();
    });

    confirmBtn.addEventListener('click', function () {
        applySelection();
        closeOverlay();
    });

    clearBtn.addEventListener('click', function () {
        pending.province = null;
        pending.city = null;
        searchEl.value = '';
        applySelection();
        renderAll('');
        closeOverlay();
    });

    applySelection();
})();
</script>
