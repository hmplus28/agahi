<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'config/agahi.php' => <<<'PHP'
<?php

return [
    'free_ad_duration_days' => (int) env('FREE_AD_DURATION_DAYS', 30),
    'max_images' => (int) env('MAX_AD_IMAGES', 5),
    'max_links' => (int) env('MAX_AD_LINKS', 5),
    'image_max_bytes' => (int) env('IMAGE_MAX_BYTES', 5 * 1024 * 1024),
    'image_max_pixels' => (int) env('IMAGE_MAX_PIXELS', 24_000_000),
    'site_description' => env('SITE_DESCRIPTION', 'سامانهٔ سریع و امن ثبت آگهی در ایران'),
];
PHP,
'routes/web.php' => <<<'PHP'
<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Public\AdController as PublicAdController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\User\AdController as UserAdController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/search', SearchController::class)->name('search');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/ad/{ad}/{slug}', [PublicAdController::class, 'show'])->where('ad', '[A-Za-z0-9]+')->name('ads.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('user')->as('user.')->group(function (): void {
    Route::get('/', UserDashboardController::class)->name('dashboard');
    Route::get('/ads/create', [UserAdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [UserAdController::class, 'store'])->middleware('throttle:10,1')->name('ads.store');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:5,1')->name('tickets.store');
});

Route::middleware(['auth', 'staff'])->prefix('admin')->as('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/ads', [ModerationController::class, 'index'])->name('ads.index');
    Route::patch('/ads/{ad}/status', [ModerationController::class, 'transition'])->name('ads.transition');
});

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/sitemaps/ads-{page}.xml', [SeoController::class, 'adsSitemap'])->whereNumber('page')->name('sitemap.ads');
Route::get('/sitemaps/categories.xml', [SeoController::class, 'categoriesSitemap'])->name('sitemap.categories');
PHP,
'bootstrap/app.php' => <<<'PHP'
<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['staff' => EnsureStaff::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Error rendering uses Laravel's production-safe exception handling.
    })->create();
PHP,
'resources/views/layouts/app.blade.php' => <<<'BLADE'
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? config('agahi.site_description') }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}">
    @isset($openGraph)
        <meta property="og:type" content="website"><meta property="og:title" content="{{ $title ?? config('app.name') }}"><meta property="og:description" content="{{ $description ?? config('agahi.site_description') }}"><meta property="og:url" content="{{ $canonical ?? url()->current() }}">
        @if(!empty($openGraph['image']))<meta property="og:image" content="{{ $openGraph['image'] }}">@endif
        <meta name="twitter:card" content="summary_large_image">
    @endisset
    @stack('head')
</head>
<body>
<header class="site-header"><div class="container nav"><a class="brand" href="{{ route('home') }}">{{ config('app.name') }}</a><nav aria-label="ناوبری اصلی"><a href="{{ route('search') }}">جست‌وجو</a><a href="{{ route('user.ads.create') }}">ثبت آگهی</a>@auth<a href="{{ route('user.dashboard') }}">پنل کاربری</a>@if(auth()->user()->is_staff)<a href="{{ route('admin.dashboard') }}">مدیریت</a>@endif<form class="inline-form" method="post" action="{{ route('logout') }}">@csrf<button class="link-button">خروج</button></form>@else<a href="{{ route('login') }}">ورود</a><a class="button button-small" href="{{ route('register') }}">ثبت‌نام</a>@endauth</nav></div></header>
<main class="container">@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif@if($errors->any())<div class="alert alert-error" role="alert"><strong>لطفاً موارد زیر را اصلاح کنید:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</main>
<footer class="site-footer"><div class="container"><p>© {{ now()->year }} {{ config('app.name') }} — آگهی‌ها با رعایت حریم خصوصی و قوانین منتشر می‌شوند.</p></div></footer>
</body>
</html>
BLADE,
'resources/views/components/ad-card.blade.php' => <<<'BLADE'
@props(['ad'])
<article class="ad-card"><a class="card-image" href="{{ $ad->publicUrl() }}" aria-label="مشاهدهٔ {{ $ad->title }}">@php($image=$ad->images->firstWhere('is_primary',true) ?? $ad->images->first())@if($image)<img src="{{ $image->thumbUrl() }}" width="480" height="{{ max(1,(int) round(480 * $image->height / max(1,$image->width))) }}" loading="lazy" alt="{{ $ad->title }}">@else<span class="image-placeholder">بدون تصویر</span>@endif</a><div class="card-body"><h3><a href="{{ $ad->publicUrl() }}">{{ $ad->title }}</a></h3><p class="price">{{ $ad->priceLabel() }}</p><p class="muted">{{ $ad->city?->name }} · {{ $ad->published_at?->diffForHumans() }}</p>@if($ad->is_urgent)<span class="badge badge-danger">فوری</span>@endif @if($ad->is_featured)<span class="badge">ویژه</span>@endif</div></article>
BLADE,
'resources/views/public/home.blade.php' => <<<'BLADE'
@extends('layouts.app')
@section('content')
<section class="hero"><div><p class="eyebrow">سامانهٔ آگهی سریع و قابل اعتماد</p><h1>آنچه می‌خواهید را پیدا کنید یا آگهی خود را ثبت کنید.</h1><p>جست‌وجوی ساده، انتشار شفاف و نمایش سریع در همهٔ دستگاه‌ها.</p></div><a class="button" href="{{ route('user.ads.create') }}">ثبت آگهی رایگان</a></section>
<form class="search-panel" method="get" action="{{ route('search') }}"><label>عبارت جست‌وجو<input type="search" name="q" placeholder="مثلاً تعمیرات کولر"></label><button class="button" type="submit">جست‌وجو</button></form>
<section><div class="section-heading"><h2>دسته‌بندی‌ها</h2></div><div class="category-grid">@forelse($categories as $category)<article class="category-card"><h3><a href="{{ route('categories.show',$category) }}">{{ $category->title }}</a></h3>@if($category->children->isNotEmpty())<ul>@foreach($category->children->take(5) as $child)<li><a href="{{ route('categories.show',$child) }}">{{ $child->title }}</a></li>@endforeach</ul>@endif</article>@empty<p>دسته‌بندی فعالی برای نمایش وجود ندارد.</p>@endforelse</div></section>
@if($featured->isNotEmpty())<section><div class="section-heading"><h2>آگهی‌های ویژه</h2></div><div class="ad-grid">@foreach($featured as $ad)<x-ad-card :ad="$ad" />@endforeach</div></section>@endif
<section><div class="section-heading"><h2>تازه‌ترین آگهی‌ها</h2><a href="{{ route('search') }}">مشاهدهٔ همه</a></div><div class="ad-grid">@forelse($latest as $ad)<x-ad-card :ad="$ad" />@empty<p>هنوز آگهی فعالی ثبت نشده است.</p>@endforelse</div></section>
@endsection
BLADE,
'resources/views/public/search.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'جست‌وجوی آگهی | '.config('app.name'),'robots'=>'noindex, follow'])
@section('content')
<h1>جست‌وجوی آگهی</h1><form class="filter-form" method="get"><label>عبارت<input name="q" value="{{ request('q') }}"></label><label>دسته<select name="category"><option value="">همهٔ دسته‌ها</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category')==$category->id)>{{ $category->title }}</option>@endforeach</select></label><label>شهر<select name="city"><option value="">همهٔ شهرها</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(request('city')==$city->id)>{{ $city->name }}</option>@endforeach</select></label><label>حداقل قیمت<input type="number" min="0" name="min_price" value="{{ request('min_price') }}"></label><label>حداکثر قیمت<input type="number" min="0" name="max_price" value="{{ request('max_price') }}"></label><button class="button">اعمال فیلتر</button></form><p class="muted">{{ $ads->total() }} نتیجه پیدا شد.</p><div class="ad-grid">@forelse($ads as $ad)<x-ad-card :ad="$ad" />@empty<p>نتیجه‌ای مطابق جست‌وجوی شما پیدا نشد.</p>@endforelse</div>{{ $ads->links() }}
@endsection
BLADE,
'resources/views/public/categories/show.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>($category->seo_title ?: 'آگهی '.$category->title).' | '.config('app.name'),'description'=>$category->seo_description ?: ($category->description ?: config('agahi.site_description')),'robots'=>$ads->total() ? 'index, follow' : 'noindex, follow'])
@section('content')
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span><span>{{ $category->title }}</span></nav><h1>آگهی‌های {{ $category->title }}</h1>@if($category->description)<p class="lead">{{ $category->description }}</p>@endif@if($category->children->isNotEmpty())<section class="subcategories"><h2>زیر‌دسته‌ها</h2>@foreach($category->children as $child)<a href="{{ route('categories.show',$child) }}">{{ $child->title }}</a>@endforeach</section>@endif<div class="ad-grid">@forelse($ads as $ad)<x-ad-card :ad="$ad" />@empty<p>در این دسته هنوز آگهی فعالی وجود ندارد.</p>@endforelse</div>{{ $ads->links() }}
@endsection
BLADE,
'resources/views/public/ads/show.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>$seo->titleForAd($ad),'description'=>$seo->descriptionForAd($ad),'canonical'=>$seo->canonicalForAd($ad),'robots'=>$seo->robotsForAd($ad),'openGraph'=>['image'=>optional($ad->images->first())->displayUrl()]])
@push('head')<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>collect($seo->breadcrumbForAd($ad))->values()->map(fn($item,$index)=>['@type'=>'ListItem','position'=>$index+1,'name'=>$item['name'],'item'=>$item['url']])->all()],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>@endpush
@section('content')
<nav class="breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">خانه</a><span>/</span>@if($ad->category)<a href="{{ route('categories.show',$ad->category) }}">{{ $ad->category->title }}</a><span>/</span>@endif<span>{{ $ad->title }}</span></nav><article class="ad-detail"><section class="gallery">@php($first=true)@forelse($ad->images as $image)<img src="{{ $image->displayUrl() }}" width="{{ $image->width }}" height="{{ $image->height }}" @if($first) fetchpriority="high" @else loading="lazy" @endif alt="{{ $ad->title }}">@php($first=false)@empty<div class="image-placeholder">تصویری برای این آگهی ثبت نشده است.</div>@endforelse</section><section><div class="ad-detail-heading"><h1>{{ $ad->title }}</h1>@if($ad->is_featured)<span class="badge">ویژه</span>@endif</div><p class="detail-price">{{ $ad->priceLabel() }}</p><dl class="details"><dt>شهر</dt><dd>{{ $ad->city?->name ?? '—' }}</dd><dt>تاریخ انتشار</dt><dd>{{ $ad->published_at?->format('Y/m/d') }}</dd><dt>کد آگهی</dt><dd>{{ $ad->code }}</dd><dt>بازدید</dt><dd>{{ number_format($ad->views_count) }}</dd></dl><a class="button" href="tel:{{ $ad->mobile_1 }}">تماس با آگهی‌دهنده</a></section><section class="description"><h2>توضیحات</h2><p>{!! nl2br(e($ad->description)) !!}</p></section>@if($ad->links->isNotEmpty())<section><h2>لینک‌ها</h2><ul>@foreach($ad->links as $link)<li><a rel="ugc" target="_blank" href="{{ $link->url }}">{{ $link->url }}</a></li>@endforeach</ul></section>@endif</article><section><div class="section-heading"><h2>آگهی‌های مرتبط</h2></div><div class="ad-grid">@forelse($related as $relatedAd)<x-ad-card :ad="$relatedAd" />@empty<p>آگهی مرتبطی یافت نشد.</p>@endforelse</div></section>
@endsection
BLADE,
'resources/views/auth/register.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'ثبت‌نام | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<section class="auth-card"><h1>ایجاد حساب کاربری</h1><form method="post">@csrf<label>نام<input name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name"></label><label>نام خانوادگی<input name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name"></label><label>شمارهٔ موبایل<input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" placeholder="09123456789" required autocomplete="tel"></label><label>ایمیل (اختیاری)<input name="email" value="{{ old('email') }}" type="email" autocomplete="email"></label><label>گذرواژه<input name="password" type="password" required autocomplete="new-password"></label><label>تکرار گذرواژه<input name="password_confirmation" type="password" required autocomplete="new-password"></label><button class="button">ثبت‌نام</button></form><p>حساب دارید؟ <a href="{{ route('login') }}">وارد شوید.</a></p></section>@endsection
BLADE,
'resources/views/auth/login.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'ورود | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<section class="auth-card"><h1>ورود به حساب کاربری</h1><form method="post">@csrf<label>شمارهٔ موبایل<input name="mobile" value="{{ old('mobile') }}" inputmode="numeric" required autocomplete="tel"></label><label>گذرواژه<input name="password" type="password" required autocomplete="current-password"></label><label class="check"><input type="checkbox" name="remember" value="1"> ورود مرا به خاطر بسپار</label><button class="button">ورود</button></form><p>حساب ندارید؟ <a href="{{ route('register') }}">ثبت‌نام کنید.</a></p></section>@endsection
BLADE,
'resources/views/user/dashboard.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'پنل کاربری | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<div class="section-heading"><div><h1>آگهی‌های من</h1><p class="muted">وضعیت آگهی‌ها و بازدید آن‌ها را مدیریت کنید.</p></div><a class="button" href="{{ route('user.ads.create') }}">ثبت آگهی</a></div><div class="tabs"><a class="@if(!$status)active@endif" href="{{ route('user.dashboard') }}">همه</a>@foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item)<a class="@if($status===$item->value)active@endif" href="{{ route('user.dashboard',['status'=>$item->value]) }}">{{ $item->label() }}</a>@endforeach</div><div class="table-wrap"><table><thead><tr><th>آگهی</th><th>کد</th><th>شهر</th><th>بازدید</th><th>وضعیت</th></tr></thead><tbody>@forelse($ads as $ad)<tr><td>{{ $ad->title }}</td><td>{{ $ad->code }}</td><td>{{ $ad->city?->name }}</td><td>{{ number_format($ad->views_count) }}</td><td><span class="badge">{{ $ad->status->label() }}</span></td></tr>@empty<tr><td colspan="5">آگهی‌ای وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $ads->links() }}<p><a href="{{ route('user.tickets.index') }}">تیکت‌های پشتیبانی</a></p>@endsection
BLADE,
'resources/views/user/ads/create.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'ثبت آگهی | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<section class="form-page"><h1>ثبت آگهی</h1><p class="lead">آگهی پس از بررسی مدیر منتشر خواهد شد. از ثبت اطلاعات حساس خودداری کنید.</p><form method="post" enctype="multipart/form-data">@csrf<div class="form-grid"><label class="wide">عنوان آگهی<input name="title" value="{{ old('title') }}" maxlength="300" required></label><label class="wide">توضیحات<textarea name="description" maxlength="6000" rows="8" required>{{ old('description') }}</textarea></label><label>دسته‌بندی<select name="category_id" required><option value="">انتخاب کنید</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id')==$category->id)>{{ $category->title }}</option>@endforeach</select></label><label>شهر<select name="city_id" required><option value="">انتخاب کنید</option>@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id')==$city->id)>{{ $city->name }}</option>@endforeach</select></label><label>قیمت (ریال)<input type="number" name="price" min="0" value="{{ old('price') }}"></label><label>نام و نام خانوادگی<input name="full_name" value="{{ old('full_name',auth()->user()->name) }}"></label><label>نام کسب‌وکار<input name="business_name" value="{{ old('business_name') }}"></label><label>موبایل اصلی<input name="mobile_1" value="{{ old('mobile_1',auth()->user()->mobile) }}" inputmode="numeric" required></label><label>موبایل دوم<input name="mobile_2" value="{{ old('mobile_2') }}" inputmode="numeric"></label><label>تلفن<input name="phone_1" value="{{ old('phone_1') }}"></label><label>ایمیل<input type="email" name="email" value="{{ old('email',auth()->user()->email) }}"></label><label class="wide">آدرس<input name="address" value="{{ old('address') }}" maxlength="500"></label><label class="wide">تصاویر (حداکثر ۵ فایل، JPG/PNG/WebP)<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple></label></div><button class="button">ثبت و ارسال برای تأیید</button></form></section>@endsection
BLADE,
'resources/views/user/tickets/index.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'تیکت‌ها | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<div class="two-column"><section><h1>تیکت‌های پشتیبانی</h1><div class="table-wrap"><table><thead><tr><th>موضوع</th><th>اولویت</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>@forelse($tickets as $ticket)<tr><td>{{ $ticket->subject }}</td><td>{{ $ticket->priority }}</td><td>{{ $ticket->status }}</td><td>{{ $ticket->created_at->format('Y/m/d') }}</td></tr>@empty<tr><td colspan="4">تیکتی ثبت نشده است.</td></tr>@endforelse</tbody></table></div>{{ $tickets->links() }}</section><section class="panel"><h2>ارسال تیکت</h2><form method="post" action="{{ route('user.tickets.store') }}">@csrf<label>موضوع<input name="subject" required></label><label>اولویت<select name="priority"><option value="normal">عادی</option><option value="high">بالا</option><option value="low">پایین</option></select></label><label>پیام<textarea name="message" rows="6" required></textarea></label><button class="button">ارسال</button></form></section></div>@endsection
BLADE,
'resources/views/admin/dashboard.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'مدیریت | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<div class="section-heading"><div><h1>داشبورد مدیریت</h1><p class="muted">نمای کلی وضعیت تعدیل و پشتیبانی.</p></div><a class="button" href="{{ route('admin.ads.index') }}">مدیریت آگهی‌ها</a></div><div class="stat-grid">@foreach($counts as $status=>$count)<article class="stat"><span>{{ \App\Domains\Ads\Enums\AdStatus::from($status)->label() }}</span><strong>{{ number_format($count) }}</strong></article>@endforeach<article class="stat"><span>تیکت باز</span><strong>{{ number_format($openTickets) }}</strong></article></div>@endsection
BLADE,
'resources/views/admin/ads/index.blade.php' => <<<'BLADE'
@extends('layouts.app',['title'=>'مدیریت آگهی‌ها | '.config('app.name'),'robots'=>'noindex, nofollow'])
@section('content')<h1>مدیریت آگهی‌ها</h1><form class="filter-form" method="get"><label>جست‌وجو<input name="q" value="{{ $term }}" placeholder="کد، موبایل یا عنوان"></label><label>وضعیت<select name="status"><option value="">همه</option>@foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item)<option value="{{ $item->value }}" @selected($status===$item->value)>{{ $item->label() }}</option>@endforeach</select></label><button class="button">فیلتر</button></form><div class="table-wrap"><table><thead><tr><th>تاریخ</th><th>عنوان / کد</th><th>نویسنده</th><th>شهر</th><th>بازدید</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>@forelse($ads as $ad)<tr><td>{{ $ad->created_at->format('Y/m/d') }}</td><td>{{ $ad->title }}<small>{{ $ad->code }}</small></td><td>{{ $ad->user->mobile }}</td><td>{{ $ad->city?->name }}</td><td>{{ number_format($ad->views_count) }}</td><td>{{ $ad->status->label() }}</td><td><form method="post" action="{{ route('admin.ads.transition',$ad) }}">@csrf @method('PATCH')<select name="status">@foreach(\App\Domains\Ads\Enums\AdStatus::cases() as $item)<option value="{{ $item->value }}" @selected($ad->status===$item)>{{ $item->label() }}</option>@endforeach</select><input name="reason" aria-label="علت" placeholder="علت (اختیاری)"><button class="button button-small">ذخیره</button></form></td></tr>@empty<tr><td colspan="7">موردی برای نمایش وجود ندارد.</td></tr>@endforelse</tbody></table></div>{{ $ads->links() }}@endsection
BLADE,
'resources/views/seo/sitemap-index.blade.php' => <<<'BLADE'
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
<sitemap><loc>{{ route('sitemap.ads',['page'=>$page]) }}</loc></sitemap>
@endforeach
<sitemap><loc>{{ route('sitemap.categories') }}</loc></sitemap>
</sitemapindex>
BLADE,
'resources/views/seo/ads-sitemap.blade.php' => <<<'BLADE'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($ads as $ad)
<url><loc>{{ $seo->canonicalForAd($ad) }}</loc><lastmod>{{ $ad->updated_at->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
BLADE,
'resources/views/seo/categories-sitemap.blade.php' => <<<'BLADE'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($categories as $category)
<url><loc>{{ route('categories.show',$category) }}</loc><lastmod>{{ $category->updated_at->toAtomString() }}</lastmod></url>
@endforeach
</urlset>
BLADE,
'public/assets/app.css' => <<<'CSS'
:root{--ink:#152034;--muted:#64748b;--brand:#0f766e;--brand-dark:#115e59;--surface:#fff;--canvas:#f8fafc;--line:#e2e8f0;--danger:#b91c1c;--radius:14px}*{box-sizing:border-box}html{font-family:Tahoma,Arial,sans-serif;background:var(--canvas);color:var(--ink)}body{margin:0;line-height:1.8}.container{width:min(1160px,calc(100% - 32px));margin-inline:auto}.site-header{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:10}.nav{min-height:68px;display:flex;justify-content:space-between;gap:20px;align-items:center}.brand{font-weight:800;font-size:1.2rem;color:var(--brand);text-decoration:none}.nav nav{display:flex;gap:14px;align-items:center;flex-wrap:wrap}.nav a,.link-button{color:var(--ink);text-decoration:none;font:inherit}.nav a:hover,.nav a:focus{color:var(--brand)}main.container{padding-block:36px;min-height:70vh}.site-footer{border-top:1px solid var(--line);color:var(--muted);font-size:.9rem;margin-top:50px}.button{display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:9px;background:var(--brand);color:#fff;text-decoration:none;font:inherit;padding:9px 16px;cursor:pointer}.button:hover,.button:focus{background:var(--brand-dark)}.button-small{font-size:.85rem;padding:6px 10px}.inline-form{display:inline}.link-button{border:0;background:transparent;cursor:pointer;padding:0}.hero{background:linear-gradient(130deg,#0f766e,#164e63);border-radius:var(--radius);padding:clamp(30px,7vw,76px);color:#fff;display:flex;align-items:end;justify-content:space-between;gap:24px}.hero h1{font-size:clamp(1.75rem,4vw,3.2rem);line-height:1.4;max-width:720px;margin:.2em 0}.eyebrow{font-weight:bold;color:#ccfbf1}.hero .button{background:#fff;color:var(--brand);white-space:nowrap}.search-panel,.filter-form{background:#fff;border:1px solid var(--line);box-shadow:0 6px 24px #0f172a0d;border-radius:var(--radius);padding:18px;margin-block:26px;display:flex;gap:12px;align-items:end;flex-wrap:wrap}.search-panel label{flex:1}.search-panel input{width:100%}label{display:grid;gap:5px;font-weight:700;color:#334155}input,select,textarea{border:1px solid #cbd5e1;background:#fff;border-radius:8px;padding:9px 10px;font:inherit;color:var(--ink);width:100%}textarea{resize:vertical}.section-heading{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-top:34px}.section-heading h1,.section-heading h2{margin:0}.category-grid,.ad-grid,.stat-grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))}.category-card,.ad-card,.panel,.stat{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden}.category-card{padding:18px}.category-card h3{margin:0 0 8px}.category-card ul{padding:0;list-style:none;margin:0}.category-card a{color:var(--brand)}.ad-card{display:flex;flex-direction:column}.card-image{aspect-ratio:1.4;display:grid;place-items:center;background:#e2e8f0;overflow:hidden}.card-image img{width:100%;height:100%;object-fit:cover}.image-placeholder{color:var(--muted);padding:18px;text-align:center}.card-body{padding:13px}.card-body h3{font-size:1rem;line-height:1.6;margin:0}.card-body h3 a{color:var(--ink);text-decoration:none}.price{font-weight:800;margin:8px 0 0}.muted{color:var(--muted);font-size:.9rem}.badge{display:inline-block;background:#ccfbf1;color:#115e59;border-radius:999px;padding:1px 9px;font-size:.78rem;font-weight:bold}.badge-danger{background:#fee2e2;color:var(--danger)}.alert{padding:12px 16px;border-radius:10px;margin-bottom:20px}.alert-success{color:#065f46;background:#d1fae5}.alert-error{color:#991b1b;background:#fee2e2}.alert ul{margin:4px 0}.breadcrumb{font-size:.9rem;color:var(--muted);display:flex;gap:9px;flex-wrap:wrap;margin-bottom:12px}.breadcrumb a{color:var(--brand)}.ad-detail{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(260px,.8fr);gap:28px}.gallery{display:grid;gap:10px}.gallery img{width:100%;height:auto;border-radius:var(--radius);background:#e2e8f0}.ad-detail-heading{display:flex;gap:10px;align-items:center}.ad-detail h1{font-size:clamp(1.65rem,3vw,2.5rem);line-height:1.4}.detail-price{font-size:1.4rem;font-weight:800;color:var(--brand)}.details{display:grid;grid-template-columns:1fr 2fr;border-top:1px solid var(--line)}.details dt,.details dd{padding:8px;border-bottom:1px solid var(--line);margin:0}.details dt{font-weight:bold;color:var(--muted)}.description{grid-column:1/-1}.lead{color:#475569}.auth-card,.form-page{max-width:680px;background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:clamp(20px,4vw,36px);margin-inline:auto}.auth-card form,.panel form{display:grid;gap:15px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:22px}.wide{grid-column:1/-1}.tabs{display:flex;flex-wrap:wrap;gap:7px;margin:16px 0}.tabs a{padding:5px 10px;border:1px solid var(--line);border-radius:999px;text-decoration:none;color:var(--ink);font-size:.85rem}.tabs a.active{background:var(--brand);color:#fff;border-color:var(--brand)}.table-wrap{overflow:auto;background:#fff;border:1px solid var(--line);border-radius:var(--radius)}table{border-collapse:collapse;width:100%;min-width:700px}th,td{padding:10px 12px;text-align:right;border-bottom:1px solid var(--line);vertical-align:top}th{font-size:.85rem;background:#f8fafc}td small{display:block;color:var(--muted)}td form{display:flex;gap:5px;min-width:340px}.two-column{display:grid;grid-template-columns:1.4fr .8fr;gap:24px}.panel{padding:20px}.stat{padding:20px}.stat span{display:block;color:var(--muted)}.stat strong{font-size:2rem}.subcategories{display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:#fff;border:1px solid var(--line);padding:16px;border-radius:var(--radius);margin-bottom:20px}.subcategories h2{font-size:1rem;margin:0}.subcategories a{color:var(--brand)}.check{display:flex;grid-template-columns:auto 1fr;align-items:center}.check input{width:auto}@media(max-width:720px){.nav{padding-block:10px;align-items:start;flex-direction:column}.hero{align-items:start;flex-direction:column}.ad-detail,.two-column{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.wide{grid-column:auto}.search-panel,.filter-form{align-items:stretch;flex-direction:column}.section-heading{align-items:start;flex-direction:column}}
CSS,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Cannot create {$relative}");
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." UI, routes, and configuration files.\n";
