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
