<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use Illuminate\Console\Command;
class ProcessAutoLadders extends Command { protected $signature='ads:process-auto-ladders {--dry-run}'; protected $description='نردبان خودکار آگهی‌های فعال را بدون تغییر تاریخ ایجاد اعمال می‌کند.'; public function handle(): int { $query=Ad::query()->where('status',AdStatus::Active)->where('auto_ladder',true)->where(fn($q)=>$q->whereNull('last_ladder_at')->orWhere('last_ladder_at','<=',now()->subDay())); if($this->option('dry-run')){ $this->info((string)$query->count()); return self::SUCCESS; } $count=$query->update(['last_ladder_at'=>now(),'sort_at'=>now()]); $this->info("{$count} آگهی به‌روزرسانی شد."); return self::SUCCESS; } }
