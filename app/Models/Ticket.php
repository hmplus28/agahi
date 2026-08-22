<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Ticket extends Model { protected $fillable = ['user_id','subject','status','priority','closed_at']; protected function casts(): array { return ['closed_at'=>'datetime']; } public function user(): BelongsTo { return $this->belongsTo(User::class); } public function messages(): HasMany { return $this->hasMany(TicketMessage::class); } }
