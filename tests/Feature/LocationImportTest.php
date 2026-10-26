<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Accounts\Enums\UserRole;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['mobile' => '09120003333', 'role' => UserRole::SuperAdmin->value, 'is_staff' => true, 'is_active' => true]);
    }

    public function test_sample_files_are_available(): void
    {
        foreach (['countries', 'provinces', 'cities'] as $type) {
            $this->actingAs($this->admin)
                ->get(route('admin.catalog.sample', $type))
                ->assertOk()
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
    }

    public function test_import_countries_from_xlsx(): void
    {
        $file = storage_path('app/samples/countries.xlsx');

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.import', 'countries'), [
                'file' => new \Illuminate\Http\UploadedFile($file, 'countries.xlsx', null, null, true),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('countries', ['name' => 'ایران', 'slug' => 'iran']);
        $this->assertDatabaseHas('countries', ['name' => 'ترکیه', 'slug' => 'turkey']);
    }

    public function test_import_provinces_from_xlsx(): void
    {
        $iran = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $file = storage_path('app/samples/provinces.xlsx');

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.import', 'provinces'), [
                'file' => new \Illuminate\Http\UploadedFile($file, 'provinces.xlsx', null, null, true),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('provinces', ['country_id' => $iran->id, 'name' => 'تهران']);
        $this->assertDatabaseHas('provinces', ['country_id' => $iran->id, 'name' => 'فارس']);
    }

    public function test_import_cities_from_xlsx(): void
    {
        $iran = Country::query()->create(['name' => 'ایران', 'slug' => 'iran']);
        $tehran = Province::query()->create(['country_id' => $iran->id, 'name' => 'تهران', 'slug' => 'tehran']);
        $file = storage_path('app/samples/cities.xlsx');

        $this->actingAs($this->admin)
            ->post(route('admin.catalog.import', 'cities'), [
                'file' => new \Illuminate\Http\UploadedFile($file, 'cities.xlsx', null, null, true),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cities', ['province_id' => $tehran->id, 'name' => 'شهرری']);
        $this->assertDatabaseHas('cities', ['province_id' => $tehran->id, 'name' => 'اسلامشهر']);
    }

    public function test_catalog_index_shows_import_ui(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.catalog.index', 'countries'))
            ->assertOk()
            ->assertSee('ورود از اکسل', false)
            ->assertSee('دانلود فایل نمونه', false);
    }

    public function test_import_rejects_unsupported_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.catalog.import', 'tariffs'), [
                'file' => storage_path('app/samples/countries.xlsx'),
            ])
            ->assertNotFound();
    }
}
