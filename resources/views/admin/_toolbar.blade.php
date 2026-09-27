@php($pendingReminders = $pendingReminders ?? \App\Models\AdExpiryReminder::query()->where('status', 'pending')->count())
<nav class="admin-toolbar" aria-label="ماژول‌های مدیریت">
    <div class="admin-toolbar__inner">
        <a href="{{ route('admin.ads.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.ads.*')) active @endif">📦 آگهی‌ها</a>
        <a href="{{ route('admin.catalog.index', 'categories') }}" class="admin-toolbar__link @if(request()->route('type') === 'categories') active @endif">📂 دسته‌بندی‌ها</a>
        <a href="{{ route('admin.catalog.index', 'countries') }}" class="admin-toolbar__link @if(request()->route('type') === 'countries') active @endif">🌍 مکان‌ها</a>
        <a href="{{ route('admin.catalog.index', 'tariffs') }}" class="admin-toolbar__link @if(request()->route('type') === 'tariffs') active @endif">💰 تعرفه‌ها</a>
        <a href="{{ route('admin.catalog.index', 'forbidden-words') }}" class="admin-toolbar__link @if(request()->route('type') === 'forbidden-words') active @endif">🚫 لغات غیرمجاز</a>
        <a href="{{ route('admin.permits.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.permits.*')) active @endif">📋 مجوزها</a>
        <a href="{{ route('admin.reports.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.reports.*')) active @endif">⚠️ گزارش‌ها</a>
        <a href="{{ route('admin.tickets.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.tickets.*')) active @endif">💬 تیکت‌ها</a>
        <a href="{{ route('admin.payments.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.payments.*')) active @endif">💳 پرداخت‌ها</a>
        @if($pendingReminders > 0)
            <a href="{{ route('admin.expiry-reminders.index') }}" class="admin-toolbar__link admin-toolbar__link--alert @if(request()->routeIs('admin.expiry-reminders.*')) active @endif">⏰ یادآوری انقضا <span class="badge badge-warning">{{ number_format($pendingReminders) }}</span></a>
        @else
            <a href="{{ route('admin.expiry-reminders.index') }}" class="admin-toolbar__link @if(request()->routeIs('admin.expiry-reminders.*')) active @endif">⏰ یادآوری انقضا</a>
        @endif
        <a href="{{ route('admin.settings.edit') }}" class="admin-toolbar__link @if(request()->routeIs('admin.settings.*')) active @endif">⚙️ تنظیمات</a>
        <form method="post" action="{{ route('admin.ads.ladder.refresh') }}" class="admin-toolbar__form">
            @csrf
            <button type="submit" class="admin-toolbar__link" title="نردبان همه آگهی‌ها را به‌روزرسانی کن و سایت‌مپ را بازسازی کن">
                🔄 بروزرسانی نردبان
            </button>
        </form>
    </div>
</nav>
