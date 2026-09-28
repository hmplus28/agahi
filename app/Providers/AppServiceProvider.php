<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Billing\Contracts\PaymentGateway;
use App\Domains\Billing\Gateways\FakePaymentGateway;
use App\Domains\Billing\Gateways\SadadGateway;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Domains\Notifications\Providers\IpPanelSmsProvider;
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
        require_once __DIR__ . '/../helpers.php';

        $this->app->bind(PaymentGateway::class, function ($app) {
            return match (config('payment.gateway', 'fake')) {
                'sadad'    => $app->make(SadadGateway::class),
                default    => $app->make(FakePaymentGateway::class),
            };
        });

        $this->app->bind(SmsProvider::class, function ($app) {
            return match (config('sms.provider', 'log')) {
                'ippanel' => $app->make(IpPanelSmsProvider::class),
                default   => $app->make(LogSmsProvider::class),
            };
        });
    }

    public function boot(): void
    {




        $host = request()->getHost();
        $isTunnel = str_ends_with($host, '.trycloudflare.com');
        $isHttps = request()->getScheme() === 'https'
            || request()->header('X-Forwarded-Proto') === 'https'
            || request()->header('CF-Connecting-IP') !== null;
        if ($isTunnel && $isHttps) {
            config(['app.asset_url' => "https://{$host}"]);
        }










        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $layoutProvinces = Cache::rememberForever('layout.provinces.v1', function (): array {
                return Province::query()
                    ->where('is_active', true)
                    ->with(['cities' => fn ($q) => $q->where('is_active', true)->select(['id', 'province_id', 'name', 'slug'])->orderBy('name')])
                    ->orderBy('name')
                    ->get(['id', 'country_id', 'name', 'slug'])
                    ->map(fn (Province $p): array => [
                        'id'         => $p->id,
                        'country_id' => $p->country_id,
                        'name'       => $p->name,
                        'slug'       => $p->slug,
                        'cities'     => $p->cities->map(fn ($c): array => [
                            'id'   => $c->id,
                            'name' => $c->name,
                            'slug' => $c->slug,
                        ])->all(),
                    ])
                    ->all();
            });

            $layoutCountries = Cache::rememberForever('layout.countries.v1', function (): array {
                return Country::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug'])
                    ->map(fn (Country $c): array => [
                        'id'   => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ])
                    ->all();
            });

            $view->with('layoutProvinces', $layoutProvinces);
            $view->with('layoutCountries', $layoutCountries);
        });
    }
}
