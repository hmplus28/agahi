<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Billing\Contracts\PaymentGateway;
use App\Domains\Billing\Gateways\FakePaymentGateway;
use App\Domains\Billing\Gateways\SadadGateway;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Domains\Notifications\Providers\LogSmsProvider;
use App\Models\Country;
use App\Models\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Resolve the payment gateway based on config('payment.gateway').
        $this->app->bind(PaymentGateway::class, function ($app) {
            return match (config('payment.gateway', 'fake')) {
                'sadad'    => $app->make(SadadGateway::class),
                default    => $app->make(FakePaymentGateway::class),
            };
        });

        $this->app->bind(SmsProvider::class, LogSmsProvider::class);
    }

    public function boot(): void
    {
        // Share location data with all views that use the main layout.
        // The layout uses $layoutProvinces and $layoutCountries to render the
        // city-picker modal, so they must be available on every request.
        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $layoutProvinces = Cache::rememberForever('layout.provinces.v1', function () {
                return Province::query()
                    ->where('is_active', true)
                    ->with(['cities' => fn ($q) => $q->where('is_active', true)->select(['id', 'province_id', 'name', 'slug'])->orderBy('name')])
                    ->orderBy('name')
                    ->get(['id', 'country_id', 'name', 'slug']);
            });

            $layoutCountries = Cache::rememberForever('layout.countries.v1', function () {
                return Country::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
            });

            $view->with('layoutProvinces', $layoutProvinces);
            $view->with('layoutCountries', $layoutCountries);
        });
    }
}
