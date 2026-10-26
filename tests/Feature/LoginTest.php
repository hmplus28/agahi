<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_09120000001_Admin123456(): void
    {
        $user = User::query()->create([
            'mobile' => '09120000001',
            'password' => 'Admin@123456',
            'first_name' => 'ادمین',
            'last_name' => 'سایت',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'mobile' => '09120000001',
            'password' => 'Admin@123456',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }
}
