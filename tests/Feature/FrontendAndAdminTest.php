<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Accounts\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAndAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_is_server_rendered_without_a_frontend_runtime(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('<h1>', false)
            ->assertSee('هر چیزی که نیاز دارید، نزدیک شما پیدا کنید', false)
            ->assertSee('name="q"', false)
            ->assertDontSee('vite/client', false)
            ->assertDontSee('react', false);
        $this->assertFileExists(public_path('assets/app.css'));
    }

    public function test_sitemap_uses_a_short_public_cache_header_and_robots_references_it(): void
    {
        Category::query()->create(['title' => 'خدمات', 'slug' => 'services']);

        $this->get(route('sitemap.index'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=900, public')
            ->assertSee(route('sitemap.categories'));

        $this->assertFileDoesNotExist(public_path('robots.txt'));
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee(route('sitemap.index'))
            ->assertSee('Disallow: /admin');
    }

    public function test_regular_user_cannot_open_admin_modules_but_staff_can(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();

        $staff = User::factory()->create([
            'role' => UserRole::Moderator,
            'is_staff' => true,
        ]);
        $this->actingAs($staff)->get(route('admin.catalog.index', 'categories'))->assertOk();
    }
}
