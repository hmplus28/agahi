<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {

        $response = $next($request);




        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');


        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);




        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload', true);
        }








        $host = $request->getHost();
        $hostDirective = $host === 'localhost' ? '' : " {$host}";


        $cfTunnel = ' http://*.trycloudflare.com https://*.trycloudflare.com';
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net{$hostDirective}{$cfTunnel}",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net{$hostDirective}{$cfTunnel}",
                "img-src 'self' data: https: http:{$hostDirective}{$cfTunnel}",
                "font-src 'self' data:",
                "connect-src 'self' https://cdn.jsdelivr.net{$hostDirective}{$cfTunnel}",
                "frame-ancestors 'self'",
                "form-action 'self'",
                "base-uri 'self'",
                "object-src 'none'",
            ]),
            true,
        );

        return $response;
    }
}
