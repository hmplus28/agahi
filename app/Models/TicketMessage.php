<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TicketMessage extends Model { public $timestamps = false; protected $fillable = ['ticket_id','sender_id','message','created_at']; public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); } public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); } }
