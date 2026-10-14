<?php

/*
|--------------------------------------------------------------------------
| cPanel shared-hosting entry point (public_html/index.php)
|--------------------------------------------------------------------------
| Replace public_html/index.php with this file when deploying with the
| structure described in DEPLOYMENT.md section 3-4: the Laravel core lives
| in /home/USERNAME/laravel and the document root stays public_html.
| usePublicPath(__DIR__) makes the Vite manifest resolve from public_html.
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laravel/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel/bootstrap/app.php';

$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
