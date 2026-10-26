<?php

declare(strict_types=1);
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
class CleanupTemporaryFiles extends Command { protected $signature='media:cleanup-temp {--hours=24}'; protected $description='فایل‌های موقت قدیمی را با احتیاط پاک‌سازی می‌کند.'; public function handle(): int { $cutoff=now()->subHours((int)$this->option('hours'))->getTimestamp(); $count=0; foreach(Storage::disk('local')->files('temp') as $path){ if(Storage::disk('local')->lastModified($path)<=$cutoff){ Storage::disk('local')->delete($path); $count++; } } $this->info("{$count} فایل موقت پاک‌سازی شد."); return self::SUCCESS; } }
