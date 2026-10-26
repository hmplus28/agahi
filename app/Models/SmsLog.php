<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SmsLog extends Model { protected $fillable=['user_id','ad_id','mobile','type','idempotency_key','provider_id','status','sent_at','response']; protected function casts(): array { return ['sent_at'=>'datetime','response'=>'array']; } }
