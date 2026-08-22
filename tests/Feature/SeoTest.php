<?php

declare(strict_types=1);
namespace Tests\Feature;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SeoTest extends TestCase { use RefreshDatabase; public function test_active_ad_has_canonical_and_is_in_sitemap(): void { $user=User::factory()->create(); $country=Country::query()->create(['name'=>'ایران','slug'=>'iran']); $province=Province::query()->create(['country_id'=>$country->id,'name'=>'تهران','slug'=>'tehran']); $city=City::query()->create(['province_id'=>$province->id,'name'=>'تهران','slug'=>'tehran']); $category=Category::query()->create(['title'=>'خدمات','slug'=>'services']); $ad=Ad::query()->create(['code'=>'SEO123456','slug'=>'seo-test','user_id'=>$user->id,'title'=>'آگهی سئو','normalized_title'=>'آگهی سئو','normalized_title_hash'=>hash('sha256','آگهی سئو'),'description'=>'توضیحات سئو برای آزمون.','normalized_description'=>'توضیحات سئو برای آزمون.','normalized_description_hash'=>hash('sha256','توضیحات سئو برای آزمون.'),'mobile_1'=>$user->mobile,'category_id'=>$category->id,'city_id'=>$city->id,'status'=>AdStatus::Active,'published_at'=>now(),'expires_at'=>now()->addDays(30)]); $this->get($ad->publicUrl())->assertOk()->assertSee('rel="canonical"',false)->assertSee('index, follow'); $this->get(route('sitemap.ads',1))->assertOk()->assertSee($ad->publicUrl()); $this->get(route('search',['q'=>'آگهی']))->assertOk()->assertSee('noindex, follow'); } }
