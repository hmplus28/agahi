<?php

declare(strict_types=1);
namespace App\Domains\Notifications\Providers;
use App\Domains\Notifications\Contracts\SmsProvider;
use Illuminate\Support\Facades\Log;
final class LogSmsProvider implements SmsProvider { public function send(string $mobile,string $message): array { Log::channel('stack')->info('Development SMS simulated',['mobile'=>$mobile,'message'=>$message]); return ['provider_id'=>null,'response'=>['mode'=>'log']]; } }
