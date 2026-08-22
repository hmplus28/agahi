<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model { protected $fillable=['user_id','ad_id','invoice_id','amount','method','status','gateway','authority','reference_id','paid_at','verified_at','admin_note']; protected function casts(): array { return ['paid_at'=>'datetime','verified_at'=>'datetime']; } public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); } public function user(): BelongsTo { return $this->belongsTo(User::class); } }
