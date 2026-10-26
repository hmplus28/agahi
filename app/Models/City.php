<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class City extends Model { protected $fillable = ['province_id','name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function province(): BelongsTo { return $this->belongsTo(Province::class); } public function ads(): HasMany { return $this->hasMany(Ad::class); } }
