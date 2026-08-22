<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_without_client_side_data_loading(): void
    {
        $this->get('/')->assertOk()->assertSee('تازه‌ترین آگهی‌ها');
    }
}
