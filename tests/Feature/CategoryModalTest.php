<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModalTest extends TestCase
{
    use RefreshDatabase;


    private function seedCategories(): array
    {
        $group = Category::query()->create(['title' => 'خدمات', 'slug' => 'services', 'is_active' => true]);
        $collection = Category::query()->create(['parent_id' => $group->id, 'title' => 'پزشکی و زیبایی', 'slug' => 'medical', 'is_active' => true]);
        $sub = Category::query()->create(['parent_id' => $collection->id, 'title' => 'آرایشی و بهداشتی', 'slug' => 'cosmetic', 'is_active' => true]);
        return compact('group', 'collection', 'sub');
    }

    private function seedGeo()
    {
        $country = Country::query()->create(['name' => 'ایران', 'slug' => 'iran', 'is_active' => true]);
        $province = Province::query()->create(['country_id' => $country->id, 'name' => 'تهران', 'slug' => 'tehran', 'is_active' => true]);
        $city = City::query()->create(['province_id' => $province->id, 'name' => 'تهران', 'slug' => 'tehran', 'is_active' => true]);
        return compact('country', 'province', 'city');
    }

    public function test_guest_ad_create_page_renders_three_level_category_modal(): void
    {
        $c = $this->seedCategories();

        $response = $this->get(route('guest.ad.create'));

        $response->assertOk()
            ->assertSee('data-cat-modal', false)
            ->assertSee('data-cat-group', false)
            ->assertSee('data-cat-collection', false)
            ->assertSee('data-cat-sub', false)
            ->assertSee('گروه', false)
            ->assertSee('مجموعه', false)
            ->assertSee('زیرمجموعه', false)

            ->assertSee('data-group="' . $c['group']->id . '"', false)
            ->assertSee($c['group']->title);


        $response->assertSee('"id":' . $c['sub']->id, false)
            ->assertSee('"parent_id":' . $c['collection']->id, false);
    }

    public function test_user_ad_create_page_renders_three_level_category_modal(): void
    {
        $c = $this->seedCategories();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.ads.create'));

        $response->assertOk()
            ->assertSee('data-cat-modal', false)
            ->assertSee('data-cat-group', false)
            ->assertSee('data-cat-collection', false)
            ->assertSee('data-cat-sub', false)
            ->assertSee('data-group="' . $c['group']->id . '"', false);
    }

    public function test_ad_edit_page_preselected_leaf_subcategory_is_marked_active(): void
    {
        $c = $this->seedCategories();
        $geo = $this->seedGeo();
        $user = User::factory()->create();
        $service = app(\App\Domains\Ads\Services\AdSubmissionService::class);
        $ad = $service->create($user, [
            'title' => 'خدمات آرایشی نوین',
            'description' => 'ارائه انواع خدمات آرایشی و زیبایی با کادر مجرب و حرفه‌ای در سراسر ایران.',
            'category_id' => $c['sub']->id,
            'city_id' => $geo['city']->id,
            'mobile_1' => '09123456789',
            'price' => 0,
        ], [], '127.0.0.1');

        $response = $this->actingAs($user)->get(route('user.ads.edit', $ad));

        $response->assertOk()

            ->assertSee('name="category_id" value="' . $c['sub']->id . '"', false)

            ->assertSee('class="cat-item active" data-group="' . $c['group']->id . '"', false)

            ->assertSee('selected.group', false)
            ->assertSee('selected.collection', false)
            ->assertSee('selected.sub', false);
    }

    public function test_submitting_ad_with_leaf_subcategory_is_stored(): void
    {
        $c = $this->seedCategories();
        $geo = $this->seedGeo();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('user.ads.store'), [
            '_token' => 'test-token',
            'title' => 'خدمات آرایشی نوین',
            'description' => 'ارائه انواع خدمات آرایشی و زیبایی با کادر مجرب و حرفه‌ای در سراسر ایران.',
            'category_id' => $c['sub']->id,
            'city_id' => $geo['city']->id,
            'mobile_1' => '۰۹۱۲۳۴۵۶۷۸۹',
            'price' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ads', [
            'title' => 'خدمات آرایشی نوین',
            'category_id' => $c['sub']->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_category_modal_static_markup_includes_correct_three_level_ordering(): void
    {

        $c = $this->seedCategories();
        $cats = Category::query()->orderBy('sort_order')->get(['id', 'parent_id', 'title']);

        $html = view('components.category-modal', [
            'categories' => $cats,
            'name' => 'category_id',
            'selectedCategoryId' => null,
        ])->render();

        $this->assertStringContainsString('data-cat-modal', $html);

        $this->assertStringContainsString('data-group="' . $c['group']->id . '"', $html);
        $this->assertStringContainsString('var tree = ', $html);

        $this->assertStringContainsString('"id":' . $c['sub']->id, $html);
    }

    public function test_modal_overlay_starts_closed_and_is_opened_via_class_not_hidden(): void
    {


        $this->seedCategories();
        $cats = Category::query()->orderBy('sort_order')->get(['id', 'parent_id', 'title']);

        $html = view('components.category-modal', [
            'categories' => $cats,
            'name' => 'category_id',
            'selectedCategoryId' => null,
        ])->render();


        $this->assertStringContainsString('data-cat-overlay', $html);
        $this->assertStringContainsString("overlay.classList.add('open')", $html);
        $this->assertStringContainsString("overlay.classList.remove('open')", $html);
    }
}
