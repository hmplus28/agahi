<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
class AdImage extends Model { protected $fillable = ['ad_id','image_thumb_path','image_display_path','width','height','sort_order','is_primary']; protected function casts(): array { return ['is_primary'=>'boolean']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function thumbUrl(): string { return Storage::disk('public')->url($this->image_thumb_path); } public function displayUrl(): string { return Storage::disk('public')->url($this->image_display_path); } }
