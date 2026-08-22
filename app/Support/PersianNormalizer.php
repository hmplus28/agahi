<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class PersianNormalizer
{
    public static function text(?string $value): string
    {
        $value = trim((string) $value);
        $value = str_replace(['ي', 'ى', 'ك', "\u{00A0}", "\u{200C}"], ['ی', 'ی', 'ک', ' ', ' '], $value);
        return (string) preg_replace('/\s+/u', ' ', $value);
    }

    public static function mobile(string $value): string
    {
        $value = strtr(self::text($value), ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
        $value = preg_replace('/[^0-9+]/', '', $value) ?? '';
        $value = str_starts_with($value, '+98') ? '0'.substr($value, 3) : $value;
        $value = str_starts_with($value, '0098') ? '0'.substr($value, 4) : $value;
        if (!preg_match('/^09\d{9}$/', $value)) {
            throw new InvalidArgumentException('شمارهٔ موبایل معتبر نیست.');
        }
        return $value;
    }
}
