<?php

declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvoiceItem extends Model { protected $fillable=['invoice_id','title','quantity','unit_price','total_price','metadata']; protected function casts(): array { return ['metadata'=>'array']; } public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); } }
