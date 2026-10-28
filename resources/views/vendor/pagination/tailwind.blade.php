@if ($paginator->hasPages())
    {{-- Single, self-contained pagination bar (RTL, Persian digits).
         All visual styles live in resources/css/app.css (.pagination-wrap*).
         Do NOT wrap this include in another .pagination-wrap element. --}}
    <nav role="navigation" aria-label="صفحه‌بندی" class="pagination-wrap">

        {{-- RTL: previous page lives to the RIGHT (start of reading flow),
             so the previous arrow must point RIGHT (toward page 1). --}}
        @if ($paginator->onFirstPage())
            <span class="pg-btn pg-nav pg-disabled" aria-disabled="true">
                <span class="pg-label">قبلی</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pg-btn pg-nav">
                <span class="pg-label">قبلی</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        @endif

        <span class="pg-pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pg-ellipsis">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pg-btn pg-active" aria-current="page">{{ to_persian_digits((string) $page) }}</span>
                        @else
                            <a href="{{ $url }}" class="pg-btn" aria-label="صفحهٔ {{ to_persian_digits((string) $page) }}">{{ to_persian_digits((string) $page) }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>

        {{-- RTL: next page lives to the LEFT (end of reading flow),
             so the next arrow must point LEFT (away from page 1). --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pg-btn pg-nav">
                <span class="pg-label">بعدی</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
        @else
            <span class="pg-btn pg-nav pg-disabled" aria-disabled="true">
                <span class="pg-label">بعدی</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            </span>
        @endif
    </nav>
@endif
