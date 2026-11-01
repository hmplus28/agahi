<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Ticket extends Model {
    protected $fillable = ['user_id','subject','status','priority','closed_at'];
    protected function casts(): array { return ['closed_at'=>'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function messages(): HasMany { return $this->hasMany(TicketMessage::class); }

    public const STATUSES = ['open', 'waiting_user', 'waiting_support', 'closed'];
    public const PRIORITIES = ['normal', 'high', 'low', 'urgent'];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'            => 'باز',
            'waiting_user'    => 'در انتظار پاسخ شما',
            'waiting_support' => 'در انتظار پشتیبانی',
            'closed'          => 'بسته شده',
            default           => (string) $this->status,
        };
    }

    public function priorityLabel(): string
    {
        return match ($this->priority) {
            'normal' => 'عادی',
            'high'   => 'بالا',
            'low'    => 'پایین',
            'urgent' => 'فوری',
            default  => (string) $this->priority,
        };
    }
}
