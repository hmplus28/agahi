<?php

declare(strict_types=1);

namespace App\Support;

final class PasswordService
{

    public const LENGTH = 8;


    public function generate(int $length = self::LENGTH): string
    {

        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }


    public function smsBody(string $password, bool $isReset = false): string
    {
        return $isReset
            ? "رمز عبور شما در سامانه آگهی: {$password}"
            : "ثبت‌نام شما در سامانه آگهی انجام شد. رمز عبور شما: {$password}";
    }
}
