<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Accounts\Enums\UserRole;
use App\Support\PersianNormalizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = ['mobile', 'email', 'first_name', 'last_name', 'password', 'role', 'is_active', 'is_staff'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_staff' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'mobile';
    }

    public function setMobileAttribute(string $value): void
    {
        $this->attributes['mobile'] = PersianNormalizer::mobile($value);
    }

    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? '')) ?: $this->mobile;
    }

    public function profile(): HasOne { return $this->hasOne(Profile::class); }
    public function ads(): HasMany { return $this->hasMany(Ad::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }

    public function isModerator(): bool
    {
        return $this->is_staff && in_array($this->role, [UserRole::SuperAdmin, UserRole::Moderator], true);
    }
}
