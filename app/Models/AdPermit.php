<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdPermit extends Model { protected $fillable=['ad_id','permit_number','issuer','issued_at','image_path','status','admin_note']; protected function casts(): array { return ['issued_at'=>'date']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
