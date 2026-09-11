<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Ads\Enums\AdStatus;
use App\Models\Ad;
use App\Models\AdExpiryReminder;
use Illuminate\Console\Command;

class CreateExpiryReminders extends Command
{
    protected $signature = 'ads:create-expiry-reminders {--limit=50} {--dry-run}';

    protected $description = 'آگهی‌های فعالی که ۱ سال از ثبت آنها گذشته را برای یادآوری انقضا شناسایی می‌کند.';

    public function handle(): int
    {
        $oneYearAgo = now()->subYear();
        $threeDaysAgo = now()->subDays(3);

        // آگهی‌های فعالی که ۱ سال پیش ثبت شدن و هنوز reminder ندارن
        $ads = Ad::query()
            ->where('status', AdStatus::Active)
            ->where('created_at', '<=', $oneYearAgo)
            ->whereDoesntHave('expiryReminders', function ($q) use ($threeDaysAgo) {
                $q->where('created_at', '>=', $threeDaysAgo);
            })
            ->with('user')
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($this->option('dry-run')) {
            $this->info((string) $ads->count());
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($ads as $ad) {
            AdExpiryReminder::create([
                'ad_id' => $ad->id,
                'scheduled_at' => now(),
                'status' => 'pending',
            ]);
            $count++;
        }

        $this->info("{$count} یادآوری انقضا ایجاد شد.");
        return self::SUCCESS;
    }
}
