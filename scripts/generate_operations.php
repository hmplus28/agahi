<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$files = [
'app/Console/Commands/ExpireAds.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Models\Ad;
use Illuminate\Console\Command;
class ExpireAds extends Command { protected $signature='ads:expire {--dry-run : فقط تعداد قابل انقضا را نشان بده}'; protected $description='آگهی‌های فعال منقضی‌شده را با روش idempotent منقضی می‌کند.'; public function handle(AdWorkflow $workflow): int { $query=Ad::query()->where('status',AdStatus::Active)->whereNotNull('expires_at')->where('expires_at','<=',now()); if ($this->option('dry-run')) { $this->info((string)$query->count()); return self::SUCCESS; } $count=0; $query->orderBy('id')->chunkById(100,function($ads) use(&$count,$workflow):void { foreach($ads as $ad){ $workflow->transition($ad,AdStatus::Expired,null,'انقضای زمان‌بندی‌شده'); $count++; } }); $this->info("{$count} آگهی منقضی شد."); return self::SUCCESS; } }
PHP,
'app/Console/Commands/ProcessAutoLadders.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use Illuminate\Console\Command;
class ProcessAutoLadders extends Command { protected $signature='ads:process-auto-ladders {--dry-run}'; protected $description='نردبان خودکار آگهی‌های فعال را بدون تغییر تاریخ ایجاد اعمال می‌کند.'; public function handle(): int { $query=Ad::query()->where('status',AdStatus::Active)->where('auto_ladder',true)->where(fn($q)=>$q->whereNull('last_ladder_at')->orWhere('last_ladder_at','<=',now()->subDay())); if($this->option('dry-run')){ $this->info((string)$query->count()); return self::SUCCESS; } $count=$query->update(['last_ladder_at'=>now(),'sort_at'=>now()]); $this->info("{$count} آگهی به‌روزرسانی شد."); return self::SUCCESS; } }
PHP,
'app/Console/Commands/CleanupTemporaryFiles.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class CleanupTemporaryFiles extends Command { protected $signature='media:cleanup-temp {--hours=24}'; protected $description='فایل‌های موقت قدیمی را با احتیاط پاک‌سازی می‌کند.'; public function handle(): int { $cutoff=now()->subHours((int)$this->option('hours'))->getTimestamp(); $count=0; foreach(Storage::disk('local')->files('temp') as $path){ if(Storage::disk('local')->lastModified($path)<=$cutoff){ Storage::disk('local')->delete($path); $count++; } } $this->info("{$count} فایل موقت پاک‌سازی شد."); return self::SUCCESS; } }
PHP,
'routes/console.php' => <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('ads:expire')->hourly()->withoutOverlapping();
Schedule::command('ads:process-auto-ladders')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('media:cleanup-temp --hours=24')->dailyAt('03:30')->withoutOverlapping();
PHP,
'database/factories/UserFactory.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace Database\Factories;
use App\Domains\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
/** @extends Factory<User> */
class UserFactory extends Factory { protected static ?string $password=null; public function definition(): array { return ['mobile'=>'09'.$this->faker->unique()->numerify('#########'),'email'=>$this->faker->unique()->safeEmail(),'first_name'=>$this->faker->firstName(),'last_name'=>$this->faker->lastName(),'password'=>static::$password ??= Hash::make('password'),'role'=>UserRole::User,'is_active'=>true,'is_staff'=>false]; } public function unverified(): static { return $this->state(fn()=>['email_verified_at'=>null]); } }
PHP,
'database/seeders/DatabaseSeeder.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace Database\Seeders;
use App\Domains\Accounts\Enums\UserRole;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Tariff;
use App\Models\User;
use App\Support\PersianNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder { public function run(): void { $iran=Country::query()->firstOrCreate(['slug'=>'iran'],['name'=>'ایران','is_active'=>true]); $tehran=Province::query()->firstOrCreate(['country_id'=>$iran->id,'slug'=>'tehran'],['name'=>'تهران','is_active'=>true]); $city=City::query()->firstOrCreate(['province_id'=>$tehran->id,'slug'=>'tehran'],['name'=>'تهران','is_active'=>true]); $services=Category::query()->firstOrCreate(['slug'=>'services'],['title'=>'خدمات','is_active'=>true]); $repair=Category::query()->firstOrCreate(['slug'=>'repair'],['parent_id'=>$services->id,'title'=>'تعمیرات','is_active'=>true]); $admin=User::query()->firstOrCreate(['mobile'=>'09120000000'],['email'=>'admin@example.test','first_name'=>'مدیر','last_name'=>'سامانه','password'=>Hash::make('ChangeMe123!'),'role'=>UserRole::SuperAdmin,'is_active'=>true,'is_staff'=>true]); Tariff::query()->firstOrCreate(['code'=>'FREE_30'],['title'=>'آگهی رایگان ۳۰ روزه','price'=>0,'service_type'=>'ad','duration_days'=>30,'is_active'=>true]); $title='تعمیرات تخصصی لوازم خانگی در تهران'; $normalizedTitle=PersianNormalizer::text($title); $description='خدمات تعمیر و سرویس لوازم خانگی با هماهنگی تلفنی در شهر تهران.'; $normalizedDescription=PersianNormalizer::text($description); Ad::query()->firstOrCreate(['code'=>'DEMO123456'],['slug'=>'tamirat-lavazem-khanegi','user_id'=>$admin->id,'title'=>$title,'normalized_title'=>$normalizedTitle,'normalized_title_hash'=>hash('sha256',$normalizedTitle),'description'=>$description,'normalized_description'=>$normalizedDescription,'normalized_description_hash'=>hash('sha256',$normalizedDescription),'price'=>null,'business_name'=>'خدمات نمونه','country_id'=>$iran->id,'province_id'=>$tehran->id,'city_id'=>$city->id,'mobile_1'=>$admin->mobile,'category_id'=>$repair->id,'source'=>'admin','status'=>AdStatus::Active,'published_at'=>now(),'expires_at'=>now()->addDays(30),'sort_at'=>now(),'is_featured'=>true]); } }
PHP,
'tests/Unit/PersianNormalizerTest.php' => <<<'PHP'
<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Support\PersianNormalizer;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
class PersianNormalizerTest extends TestCase { #[Test] public function it_normalizes_arabic_letters_and_whitespace(): void { $this->assertSame('کالا ی تست',PersianNormalizer::text(" كالا   ي\u{200C}تست ")); } #[Test] public function it_normalizes_mobile_digits(): void { $this->assertSame('09123456789',PersianNormalizer::mobile('۰۹۱۲۳۴۵۶۷۸۹')); } #[Test] public function it_rejects_an_invalid_mobile(): void { $this->expectException(InvalidArgumentException::class); PersianNormalizer::mobile('123'); } }
PHP,
'tests/Feature/AdWorkflowTest.php' => <<<'PHP'
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
class AdWorkflowTest extends TestCase { use RefreshDatabase; public function test_an_ad_follows_the_approval_and_expiration_workflow(): void { $user=User::factory()->create(); $country=Country::query()->create(['name'=>'ایران','slug'=>'iran']); $province=Province::query()->create(['country_id'=>$country->id,'name'=>'تهران','slug'=>'tehran']); $city=City::query()->create(['province_id'=>$province->id,'name'=>'تهران','slug'=>'tehran']); $category=Category::query()->create(['title'=>'خدمات','slug'=>'services']); $ad=Ad::query()->create(['code'=>'TEST123456','slug'=>'test','user_id'=>$user->id,'title'=>'آگهی آزمایشی','normalized_title'=>'آگهی آزمایشی','normalized_title_hash'=>hash('sha256','آگهی آزمایشی'),'description'=>'توضیحات آزمایشی','normalized_description'=>'توضیحات آزمایشی','normalized_description_hash'=>hash('sha256','توضیحات آزمایشی'),'mobile_1'=>$user->mobile,'category_id'=>$category->id,'city_id'=>$city->id,'status'=>AdStatus::Draft]); $workflow=app(AdWorkflow::class); $workflow->transition($ad,AdStatus::PendingApproval,$user); $workflow->transition($ad,AdStatus::Active,$user); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'active']); $workflow->transition($ad->fresh(),AdStatus::Expired); $this->assertDatabaseHas('ads',['id'=>$ad->id,'status'=>'expired']); $this->assertDatabaseCount('ad_status_histories',3); } }
PHP,
'tests/Feature/SeoTest.php' => <<<'PHP'
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
PHP,
];

foreach ($files as $relative => $content) {
    $path = $root.'/'.$relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) throw new RuntimeException("Cannot create {$relative}");
    file_put_contents($path, $content."\n");
}

echo 'Generated '.count($files)." operational and test files.\n";
