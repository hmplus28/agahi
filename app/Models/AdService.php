<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdService extends Model { protected $fillable=['ad_id','tariff_id','starts_at','expires_at','status','metadata']; protected function casts(): array { return ['starts_at'=>'datetime','expires_at'=>'datetime','metadata'=>'array']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function tariff(): BelongsTo { return $this->belongsTo(Tariff::class); } }
