<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Support\HttpsPolicy;
use PHPUnit\Framework\TestCase;

class HttpsPolicyTest extends TestCase
{
    public function test_https_is_never_forced_outside_production(): void
    {
        $this->assertFalse(HttpsPolicy::shouldForce(false, 'https://example.com', 'http', null));
    }

    public function test_production_with_http_app_url_and_plain_request_stays_http(): void
    {
        $this->assertFalse(HttpsPolicy::shouldForce(true, 'http://localhost:8087', 'http', null));
    }

    public function test_https_app_url_forces_https_in_production(): void
    {
        $this->assertTrue(HttpsPolicy::shouldForce(true, 'https://agahi.example.com', 'http', null));
    }

    public function test_https_request_forces_https_in_production(): void
    {
        $this->assertTrue(HttpsPolicy::shouldForce(true, 'http://localhost:8087', 'https', null));
    }

    public function test_tls_terminating_proxy_forces_https(): void
    {
        $this->assertTrue(HttpsPolicy::shouldForce(true, 'http://localhost:8087', 'http', 'https'));
    }
}
