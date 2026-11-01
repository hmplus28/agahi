<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
    /** PRD §27 - در انتظار، موفق، ناموفق، لغو شده، نیازمند بررسی */
    public const STATUSES = ['pending', 'successful', 'failed', 'canceled', 'needs_review'];
    public const METHODS  = ['free', 'online', 'card'];

    protected $fillable=['user_id','ad_id','invoice_id','amount','method','status','gateway','authority','reference_id','paid_at','verified_at','admin_note'];
    protected function casts(): array { return ['paid_at'=>'datetime','verified_at'=>'datetime']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function ad(): BelongsTo { return $this->belongsTo(Ad::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'      => 'در انتظار',
            'successful'   => 'موفق',
            'failed'       => 'ناموفق',
            'canceled'     => 'لغو شده',
            'needs_review' => 'نیازمند بررسی',
            default        => (string) $this->status,
        };
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'free'   => 'رایگان',
            'online' => 'آنلاین',
            'card'   => 'کارت به کارت',
            default  => (string) $this->method,
        };
    }
}
