@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="pagination-wrap">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pg-btn pg-prev pg-disabled" aria-disabled="true" aria-label="صفحهٔ قبل">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pg-btn pg-prev" aria-label="صفحهٔ قبل">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg-ellipsis">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg-btn pg-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg-btn" aria-label="صفحهٔ {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pg-btn pg-next" aria-label="صفحهٔ بعد">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        @else
            <span class="pg-btn pg-next pg-disabled" aria-disabled="true" aria-label="صفحهٔ بعد">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        @endif
    </nav>
@endif
