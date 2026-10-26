<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdStatusHistory extends Model { public $timestamps = false; protected $fillable = ['ad_id','from_status','to_status','changed_by','reason','created_at']; protected function casts(): array { return ['created_at'=>'datetime']; } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function actor(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); } }
