<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('ads:expire')->hourly()->withoutOverlapping();
Schedule::command('ads:process-auto-ladders')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('ads:create-expiry-reminders --limit=50')->dailyAt('09:30')->withoutOverlapping();
Schedule::command('ads:send-expiry-notifications --limit=50')->dailyAt('10:00')->withoutOverlapping();
Schedule::command('media:cleanup-temp --hours=24')->dailyAt('03:30')->withoutOverlapping();
// PRD §63 - backup database. داخل کانتینر app اگر pg_dump نباشد، فرمان با پیام
// راهنما خارج می‌شود؛ روی سرور اسکریپت host-side scripts/backup-db.sh هم موجود است.
Schedule::command('db:backup --keep=14')->dailyAt('03:00')->withoutOverlapping();
