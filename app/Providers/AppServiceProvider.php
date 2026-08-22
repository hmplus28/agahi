<?php

declare(strict_types=1);
namespace App\Providers;
use App\Domains\Billing\Contracts\PaymentGateway;
use App\Domains\Billing\Gateways\FakePaymentGateway;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Domains\Notifications\Providers\LogSmsProvider;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider { public function register(): void { $this->app->bind(PaymentGateway::class,FakePaymentGateway::class); $this->app->bind(SmsProvider::class,LogSmsProvider::class); } public function boot(): void {} }
