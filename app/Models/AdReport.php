<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdReport extends Model { protected $fillable=['ad_id','reason','description','reporter_user_id','reporter_ip','status','admin_note','reviewed_at']; protected function casts(): array { return ['reviewed_at'=>'datetime']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
