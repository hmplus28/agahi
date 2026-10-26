<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Province extends Model { protected $fillable = ['country_id','name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function country(): BelongsTo { return $this->belongsTo(Country::class); } public function cities(): HasMany { return $this->hasMany(City::class); } }
