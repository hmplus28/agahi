<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdLink extends Model { protected $fillable = ['ad_id','type','url','sort_order','is_active']; protected function casts(): array { return ['is_active'=>'boolean']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } }
