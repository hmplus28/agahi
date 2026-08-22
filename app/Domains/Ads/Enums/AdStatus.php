<?php

declare(strict_types=1);

namespace App\Domains\Ads\Enums;

enum AdStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Active = 'active';
    case NeedsPermit = 'needs_permit';
    case Inactive = 'inactive';
    case Expired = 'expired';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::PendingApproval => 'در انتظار تأیید',
            self::Active => 'فعال',
            self::NeedsPermit => 'نیازمند مجوز',
            self::Inactive => 'غیرفعال',
            self::Expired => 'منقضی',
            self::Deleted => 'حذف‌شده',
        };
    }
}
