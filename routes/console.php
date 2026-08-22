<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('ads:expire')->hourly()->withoutOverlapping();
Schedule::command('ads:process-auto-ladders')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('ads:send-expiry-notifications --limit=50')->dailyAt('10:00')->withoutOverlapping();
Schedule::command('media:cleanup-temp --hours=24')->dailyAt('03:30')->withoutOverlapping();
