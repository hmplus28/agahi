<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Notifications\SmsService;
use Illuminate\Console\Command;

final class TestSms extends Command
{
    protected $signature = 'sms:test {mobile} {--pattern=} {--message=}';
    protected $description = 'Test SMS sending';

    public function handle(SmsService $sms): int
    {
        $mobile = $this->argument('mobile');
        $pattern = $this->option('pattern');
        $message = $this->option('message');

        if ($pattern) {
            $this->info("Sending pattern SMS to {$mobile} with pattern: {$pattern}");

            $params = match ($pattern) {
                'otp' => [
                    'username' => 'testuser',
                    'password' => 'TestPass12345678',
                ],
                default => [],
            };

            $log = $sms->sendPattern(
                'test:' . $mobile . ':' . time(),
                'otp',
                $mobile,
                $pattern,
                $params,
            );

            $this->info("SMS sent. Log ID: {$log->id}");
            $this->info("Response: " . json_encode($log->response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } elseif ($message) {
            $this->info("Sending SMS to {$mobile}: {$message}");

            $log = $sms->send(
                'test:' . $mobile . ':' . time(),
                'test',
                $mobile,
                $message,
            );

            $this->info("SMS sent. Log ID: {$log->id}");
            $this->info("Response: " . json_encode($log->response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->error('Please provide --pattern or --message option');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
