<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Invoice extends Model { protected $fillable=['invoice_number','user_id','ad_id','status','subtotal','discount','total','paid_at']; protected function casts(): array { return ['paid_at'=>'datetime']; } public function user(): BelongsTo { return $this->belongsTo(User::class); } public function ad(): BelongsTo { return $this->belongsTo(Ad::class); } public function items(): HasMany { return $this->hasMany(InvoiceItem::class); } public function payments(): HasMany { return $this->hasMany(Payment::class); } }
