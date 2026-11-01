<?php

declare(strict_types=1);
namespace Tests\Feature;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdWorkflowTest extends TestCase { use RefreshDatabase;
    private function makeAd(AdStatus $status): Ad { $user=User::factory()->create(); $country=Country::query()->create(['name'=>'ایران','slug'=>'iran']); $province=Province::query()->create(['country_id'=>$country->id,'name'=>'تهران','slug'=>'tehran']); $city=City::query()->create(['province_id'=>$province->id,'name'=>'تهران','slug'=>'tehran']); $category=Category::query()->create(['title'=>'خدمات','slug'=>'services']); return Ad::query()->create(['code'=>'700110002','slug'=>'test','user_id'=>$user->id,'title'=>'آگهی آزمایشی','normalized_title'=>'آگهی آزمایشی','normalized_title_hash'=>hash('sha256','آگهی آزمایشی'),'description'=>'توضیحات آزمایشی','normalized_description'=>'توضیحات آزمایشی','normalized_description_hash'=>hash('sha256','توضیحات آزمایشی'),'mobile_1'=>$user->mobile,'category_id'=>$category->id,'city_id'=>$city->id,'status'=>$status]); }
    public function test_an_ad_follows_the_approval_and_expiration_workflow(): void { $ad=$this->makeAd(AdStatus::Draft); $workflow=app(AdWorkflow::class); $workflow->transition($ad,AdStatus::PendingApproval); $workflow->transition($ad->fresh(),AdStatus::Active); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'active']); $workflow->transition($ad->fresh(),AdStatus::Expired); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'expired']); $this->assertDatabaseCount('ad_status_histories',3); }
    public function test_disallowed_transition_without_force_throws(): void { $ad=$this->makeAd(AdStatus::NeedsPermit); $workflow=app(AdWorkflow::class); $this->expectException(\DomainException::class); $workflow->transition($ad,AdStatus::PendingPayment); }
    public function test_force_transition_allows_any_status_change(): void { $ad=$this->makeAd(AdStatus::NeedsPermit); $workflow=app(AdWorkflow::class); $workflow->transition($ad,AdStatus::PendingPayment,null,'جابجایی دستی مدیر',true); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'pending_payment']); $workflow->transition($ad->fresh(),AdStatus::Active,null,'فعال‌سازی مجدد',true); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'active']); $this->assertDatabaseCount('ad_status_histories',2); }
}
