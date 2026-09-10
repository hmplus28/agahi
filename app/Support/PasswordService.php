<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Generates and renders the system's permanent-but-SMSed password.
 *
 * Password model:
 *   - On registration we generate ONE random alphanumeric password.
 *   - That password is the user's permanent credential — it is NOT a
 *     one-time OTP. It is stored (hashed) in the users.password column
 *     and the same plaintext is SMSed to the user the first time.
 *   - When the user forgets, we SMS them the same password again
 *     (re-derive from the same source — actually re-SMS the existing
 *     hashed password? We can't: hashing is one-way. So the user model
 *     also carries a plaintext_password column used ONLY for SMS
 *     re-issuance, never for authentication).
 *
 * The plaintext_password column is intentionally kept separate from
 * the bcrypt hash so a database read of one doesn't grant login.
 */
final class PasswordService
{
    /** Default length of the generated password (letters + digits). */
    public const LENGTH = 8;

    /** Generate a new human-friendly password (no ambiguous chars). */
    public function generate(int $length = self::LENGTH): string
    {
        // No 0/O/1/l/I/S/5 — they are easy to confuse over SMS.
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }

    /** Render the password SMS body. */
    public function smsBody(string $password, bool $isReset = false): string
    {
        return $isReset
            ? "رمز عبور شما در سامانه آگهی: {$password}"
            : "ثبت‌نام شما در سامانه آگهی انجام شد. رمز عبور شما: {$password}";
    }
}
