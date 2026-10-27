<?php

declare(strict_types=1);
namespace App\Support;

/**
 * Decides whether generated URLs should be forced to https.
 *
 * Forcing https on every production boot broke plain-http deployments (e.g.
 * Docker serving http://server-ip:8087 with no TLS in front): every link and
 * asset pointed to an https URL nothing answered. https is now forced only
 * when something actually speaks https - the configured APP_URL, the request
 * itself, or a TLS-terminating proxy announcing itself.
 */
final class HttpsPolicy {
    public static function shouldForce(bool $production, string $appUrl, string $scheme, ?string $forwardedProto): bool
    {
        if (!$production) {
            return false;
        }

        return str_starts_with($appUrl, 'https://')
            || $scheme === 'https'
            || $forwardedProto === 'https';
    }
}
