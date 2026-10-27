<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Ad;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Console\Command;

/**
 * php artisan demo:seed          => create/refresh the demo dataset (default 250 active ads)
 * php artisan demo:seed --count=400
 * php artisan demo:seed --flush  => ONLY delete demo-tagged ads (source='demo')
 *
 * Demo rows are strictly separated from real data, so this command is safe
 * to run on the production server while testing; real ads, users, invoices
 * and settings are never touched.
 */
final class DemoSeed extends Command
{
    protected $signature = 'demo:seed
                            {--count=250 : تعداد آگهی‌های فعال نمونه}
                            {--flush : فقط حذف آگهی‌های نمونه (بدون ساخت داده جدید)}';

    protected $description = 'ساخت/به‌روزرسانی داده‌های نمونه (دمو) برای تست - بدون دست‌زدن به داده‌های واقعی';

    public function handle(): int
    {
        $seeder = new DemoContentSeeder;
        $seeder->setCommand($this);

        if ((bool) $this->option('flush')) {
            $seeder->flushDemoData();
            $remaining = Ad::query()->where('source', DemoContentSeeder::DEMO_SOURCE)->count();

            $this->info('همه داده‌های نمونه حذف شدند. داده‌های واقعی دست‌نخورده باقی ماندند.');
            $this->line(sprintf('آگهی نمونه باقی‌مانده: %d', $remaining));

            return self::SUCCESS;
        }

        $seeder->activeCount = max(1, (int) $this->option('count'));
        $seeder->run();

        $active = Ad::query()->where('source', DemoContentSeeder::DEMO_SOURCE)->where('status', 'active')->count();
        $total = Ad::query()->where('source', DemoContentSeeder::DEMO_SOURCE)->count();

        $this->newLine();
        $this->info(sprintf('خلاصه دیتای دمو: %d آگهی فعال (صفحه اصلی: %d صفحه) از مجموع %d آگهی نمونه.', $active, (int) ceil($active / 100), $total));
        $this->line('کاربران نمونه: 09300000001 تا 09300000008 - رمز عبور: '.DemoContentSeeder::DEMO_PASSWORD);
        $this->line('برای پاک‌سازی کامل دیتای دمو: php artisan demo:seed --flush');

        return self::SUCCESS;
    }
}
