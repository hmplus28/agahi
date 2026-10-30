@props([
    'title' => '',
    'subtitle' => null,
    'features' => [],
    'footnote' => null,
])

<div class="auth-shell">
    <aside class="auth-aside">
        <div class="auth-aside-brand">
            <span class="auth-aside-logo" aria-hidden="true">{{ mb_substr(config('app.name'), 0, 1) }}</span>
            <span>{{ config('app.name') }}</span>
        </div>
        <h2 class="auth-aside-title">{{ $title }}</h2>
        @if(count($features))
            <ul class="auth-aside-list">
                @foreach($features as $feature)
                    <li>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
        @if($footnote)
            <p class="auth-aside-foot">{{ $footnote }}</p>
        @endif
    </aside>

    <div class="auth-panel">
        <header class="auth-panel-head">
            <h1>{{ $title }}</h1>
            @if($subtitle)
                <p>{{ $subtitle }}</p>
            @endif
        </header>

        <div class="auth-body">
            {{ $slot }}
        </div>

        @isset($links)
            <div class="auth-alt">
                {{ $links }}
            </div>
        @endisset
    </div>
</div>
