<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * PRD §63 «Backup Database» - dumps the PostgreSQL database into
 * storage/app/backups using pg_dump (custom format, already compressed).
 *
 * The app container does not ship a PostgreSQL client (kept deliberately
 * small); on the server, either install postgresql-client-16 inside the app
 * image, or run the host-side helper scripts/backup-db.sh from crontab which
 * uses the pg_dump binary of the postgres container itself:
 *
 *   30 2 * * * /www/wwwroot/agahi/scripts/backup-db.sh
 */
final class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : تعداد فایل‌های بکاپ نگهداری‌شده} {--dir= : مسیر ذخیره (پیش‌فرض: storage/app/backups)}';

    protected $description = 'تهیهٔ بکاپ از دیتابیس PostgreSQL با pg_dump و حذف بکاپ‌های قدیمی';

    public function handle(): int
    {
        if ((string) config('database.default') !== 'pgsql') {
            $this->error('این فرمان فقط برای PostgreSQL پشتیبانی می‌شود.');

            return self::FAILURE;
        }

        $pgDump = trim((string) shell_exec('command -v pg_dump 2>/dev/null'));
        if ($pgDump === '') {
            $this->error('pg_dump در این کانتینر نصب نیست.');
            $this->line('راه‌حل: اسکریپت scripts/backup-db.sh را از crontab هاست اجرا کنید (از pg_dump خود کانتینر db استفاده می‌کند).');

            return self::FAILURE;
        }

        $dir = $this->option('dir') ?: storage_path('app/backups');
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("ساخت پوشهٔ بکاپ ناموفق بود: {$dir}");

            return self::FAILURE;
        }

        $filename = 'agahi-'.now()->format('Ymd-His').'.dump';
        $target = $dir.'/'.$filename;

        $env = [
            'PGPASSWORD' => (string) config('database.connections.pgsql.password'),
            'PATH'       => getenv('PATH') ?: '/usr/bin:/bin',
        ];

        $cmdParts = [
            escapeshellarg($pgDump),
            '--host='.escapeshellarg((string) config('database.connections.pgsql.host')),
            '--port='.escapeshellarg((string) config('database.connections.pgsql.port')),
            '--username='.escapeshellarg((string) config('database.connections.pgsql.username')),
            '--format=custom',
            '--no-owner',
            '--file='.escapeshellarg($target),
            escapeshellarg((string) config('database.connections.pgsql.database')),
        ];

        $proc = proc_open(implode(' ', $cmdParts), [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
        if (! is_resource($proc)) {
            $this->error('اجرای pg_dump ناموفق بود.');

            return self::FAILURE;
        }

        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        if ($code !== 0 || ! is_file($target) || filesize($target) === 0) {
            $this->error('بکاپ ناموفق بود: '.trim($stderr !== '' ? $stderr : $stdout));

            return self::FAILURE;
        }

        $this->info('بکاپ ساخته شد: '.$filename.' ('.number_format((float) filesize($target) / 1024, 1).' KB)');

        $this->pruneOld((string) $dir, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    private function pruneOld(string $dir, int $keep): void
    {
        $files = glob($dir.'/agahi-*.dump') ?: [];
        sort($files); // oldest first (name contains timestamp)
        $extra = count($files) - $keep;
        foreach (array_slice($files, 0, max(0, $extra)) as $old) {
            @unlink($old);
            $this->line('حذف بکاپ قدیمی: '.basename($old));
        }
    }
}
