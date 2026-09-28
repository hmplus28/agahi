<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use Illuminate\Console\Command;
class ProcessAutoLadders extends Command {
    protected $signature='ads:process-auto-ladders {--dry-run}';
    protected $description='نردبان خودکار: هر روز تاریخ آگهی‌های فعال دارای نردبان (فلگ auto_ladder یا سرویس نردبان فعال) را به‌روزرسانی می‌کند تا در سایت‌مپ با تاریخ تازه به گوگل معرفی شوند.';
    public function handle(): int {
        // Covers both ladder flavors:
        //   1) ads flagged auto_ladder = true, and
        //   2) ads with an ACTIVE ladder/auto_ladder tariff service (purchased
        //      via the payment gateway) — so paid ladders keep getting bumped
        //      daily too, matching what the admin "بروزرسانی نردبان" button does.
        $query=Ad::query()
            ->where('status',AdStatus::Active)
            ->where(function($q){
                $q->where('auto_ladder',true)
                  ->orWhereHas('adServices',function($q2):void{
                      $q2->where('status','active')->whereHas('tariff',function($q3):void{
                          $q3->whereIn('service_type',['ladder','auto_ladder']);
                      });
                  });
            })
            ->where(fn($q)=>$q->whereNull('last_ladder_at')->orWhere('last_ladder_at','<=',now()->subDay()));
        if($this->option('dry-run')){ $this->info((string)$query->count()); return self::SUCCESS; }
        $count=$query->update(['last_ladder_at'=>now(),'sort_at'=>now()]);
        $this->info("{$count} آگهی به‌روزرسانی شد.");
        return self::SUCCESS;
    }
}
