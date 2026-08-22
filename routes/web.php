<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\BillingController as AdminBillingController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PermitController as AdminPermitController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Public\AdController as PublicAdController;
use App\Http\Controllers\Public\AdReportController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\User\AdController as UserAdController;
use App\Http\Controllers\User\BillingController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\PermitController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/search', SearchController::class)->name('search');
Route::get('/category/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/ad/{ad}/{slug}', [PublicAdController::class, 'show'])->where('ad', '[A-Za-z0-9]+')->name('ads.show');
Route::post('/ad/{ad}/report', [AdReportController::class, 'store'])->where('ad', '[A-Za-z0-9]+')->middleware('throttle:3,10')->name('ads.reports.store');

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
    Route::get('/ads/{ad}/edit', [UserAdController::class, 'edit'])->name('ads.edit');
    Route::put('/ads/{ad}', [UserAdController::class, 'update'])->middleware('throttle:10,1')->name('ads.update');
    Route::delete('/ads/{ad}', [UserAdController::class, 'destroy'])->name('ads.destroy');
    Route::post('/ads/{ad}/permit', [PermitController::class, 'store'])->middleware('throttle:5,10')->name('ads.permits.store');
    Route::get('/payments', [BillingController::class, 'index'])->name('payments.index');
    Route::post('/payments/purchase', [BillingController::class, 'purchase'])->middleware('throttle:5,1')->name('payments.purchase');
    Route::get('/payments/callback/{authority}', [BillingController::class, 'callback'])->name('payments.callback');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('throttle:5,1')->name('tickets.store');
});

Route::middleware(['auth', 'staff'])->prefix('admin')->as('admin.')->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/ads', [ModerationController::class, 'index'])->name('ads.index');
    Route::patch('/ads/{ad}/status', [ModerationController::class, 'transition'])->name('ads.transition');
    Route::get('/catalog/{type}', [CatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalog/{type}', [CatalogController::class, 'store'])->name('catalog.store');
    Route::patch('/catalog/{type}/{id}', [CatalogController::class, 'update'])->name('catalog.update');
    Route::patch('/catalog/{type}/{id}/toggle', [CatalogController::class, 'toggle'])->name('catalog.toggle');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::get('/permits', [AdminPermitController::class, 'index'])->name('permits.index');
    Route::patch('/permits/{permit}', [AdminPermitController::class, 'update'])->name('permits.update');
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    Route::get('/payments', [AdminBillingController::class, 'index'])->name('payments.index');
});

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/sitemaps/ads-{page}.xml', [SeoController::class, 'adsSitemap'])->whereNumber('page')->name('sitemap.ads');
Route::get('/sitemaps/categories.xml', [SeoController::class, 'categoriesSitemap'])->name('sitemap.categories');
