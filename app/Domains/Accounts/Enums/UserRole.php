<?php

declare(strict_types=1);

namespace App\Domains\Accounts\Enums;

enum UserRole: string
{
    case User = 'user';
    case SuperAdmin = 'super_admin';
    case Moderator = 'moderator';
    case Support = 'support';
    case Accounting = 'accounting';

    public function isStaff(): bool
    {
        return $this !== self::User;
    }
}
