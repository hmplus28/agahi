<?php

declare(strict_types=1);
namespace App\Console\Commands;
use App\Domains\Ads\Enums\AdStatus;
use App\Domains\Notifications\SmsService;
use App\Models\Ad;
use Illuminate\Console\Command;
class SendExpiryNotifications extends Command { protected $signature='ads:send-expiry-notifications {--limit=50} {--dry-run}'; protected $description='یادآوری انقضای آگهی را به‌صورت محدود و idempotent ارسال می‌کند.'; public function handle(SmsService $sms): int { $ads=Ad::query()->where('status',AdStatus::Expired)->whereNotNull('expires_at')->orderBy('id')->limit((int)$this->option('limit'))->get(); if($this->option('dry-run')){ $this->info((string)$ads->count()); return self::SUCCESS; } foreach($ads as $ad){ $key='expiry:'.$ad->id.':'.$ad->expires_at?->format('Ymd'); $sms->send($key,'expiry_reminder',$ad->mobile_1,'آگهی شما منقضی شده است. برای تمدید به پنل کاربری مراجعه کنید.',$ad->user,$ad); } $this->info($ads->count().' پیام یادآوری پردازش شد.'); return self::SUCCESS; } }
