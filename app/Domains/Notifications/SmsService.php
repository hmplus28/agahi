<?php

declare(strict_types=1);
namespace App\Domains\Notifications;
use App\Domains\Notifications\Contracts\SmsProvider;
use App\Models\Ad;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
final class SmsService { public function __construct(private readonly SmsProvider $provider) {} public function send(string $key,string $type,string $mobile,string $message,?User $user=null,?Ad $ad=null): SmsLog { return DB::transaction(function() use($key,$type,$mobile,$message,$user,$ad): SmsLog { $log=SmsLog::query()->firstOrCreate(['idempotency_key'=>$key],['type'=>$type,'mobile'=>$mobile,'user_id'=>$user?->id,'ad_id'=>$ad?->id,'status'=>'pending']); if($log->status==='sent') return $log; $result=$this->provider->send($mobile,$message); $log->forceFill(['status'=>'sent','provider_id'=>$result['provider_id'],'response'=>$result['response'],'sent_at'=>now()])->save(); return $log; }); } }
