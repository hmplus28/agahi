<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Ads\Services\AdWorkflow;
use App\Models\Ad;
use Illuminate\Console\Command;
class ExpireAds extends Command { protected $signature='ads:expire {--dry-run : فقط تعداد قابل انقضا را نشان بده}'; protected $description='آگهی‌های فعال منقضی‌شده را با روش idempotent منقضی می‌کند.'; public function handle(AdWorkflow $workflow): int { $query=Ad::query()->where('status',AdStatus::Active)->whereNotNull('expires_at')->where('expires_at','<=',now()); if ($this->option('dry-run')) { $this->info((string)$query->count()); return self::SUCCESS; } $count=0; $query->orderBy('id')->chunkById(100,function($ads) use(&$count,$workflow):void { foreach($ads as $ad){ $workflow->transition($ad,AdStatus::Expired,null,'انقضای زمان‌بندی‌شده'); $count++; } }); $this->info("{$count} آگهی منقضی شد."); return self::SUCCESS; } }
