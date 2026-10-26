<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Country extends Model { protected $fillable = ['name','slug','is_active','sort_order']; protected function casts(): array { return ['is_active'=>'boolean']; } public function provinces(): HasMany { return $this->hasMany(Province::class); } }
