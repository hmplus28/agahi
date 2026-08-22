<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Profile extends Model { protected $fillable = ['user_id','business_name','address','province_id','city_id','postal_code','avatar_path']; public function user(): BelongsTo { return $this->belongsTo(User::class); } public function province(): BelongsTo { return $this->belongsTo(Province::class); } public function city(): BelongsTo { return $this->belongsTo(City::class); } }
