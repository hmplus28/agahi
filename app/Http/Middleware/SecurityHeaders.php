<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Adds a baseline set of security headers to every response. Headers
 * are conservative and safe for a server-rendered Laravel app with
 * no inline scripts/styles coming from user input.
 *
 * Notable choices:
 *   • X-Content-Type-Options: nosniff — prevents MIME sniffing attacks
 *   • X-Frame-Options: SAMEORIGIN — only our own pages can iframe us
 *   • Referrer-Policy: strict-origin-when-cross-origin — leak less referrer
 *   • Permissions-Policy: camera=(), microphone=(), geolocation=() —
 *     disables JS access to sensitive device APIs we don't use
 *   • Strict-Transport-Security — only added when request came over HTTPS
 *     so dev servers (HTTP) aren't accidentally locked in
 *   • X-Powered-By removed — don't advertise PHP version to fingerprinters
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        /** @var Response $response */
        $response = $next($request);

        // Strip the X-Powered-By header that PHP adds by default. This
        // reduces fingerprinting surface (attackers can't trivially tell
        // we're on PHP 8.4.24 or which extensions are loaded).
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');

        // Security headers — these are conservative and don't break the app.
        $response->headers->set('X-Content-Type-Options', 'nosniff', true);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', true);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', true);
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()', true);
        $response->headers->set('X-XSS-Protection', '1; mode=block', true);

        // Only add HSTS when the request actually came over HTTPS — never
        // over plain HTTP, because that would lock users out if they hit
        // the dev server before HTTPS is configured.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload', true);
        }

        // Content-Security-Policy — strict enough to block classic XSS vectors
        // but permissive enough for the existing inline <script> blocks and
        // inline <style> tags. Inline scripts are allowed via 'unsafe-inline'
        // because we use many small inline snippets (the layout, the city
        // picker, the keyword tag input, etc.). For production hardening
        // we should switch to nonces, but for now 'unsafe-inline' unblocks
        // everything without breaking functionality.
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "img-src 'self' data: https:",
                "font-src 'self' data:",
                "connect-src 'self'",
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
