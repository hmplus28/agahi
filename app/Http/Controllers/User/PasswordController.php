<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Domains\Notifications\SmsService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Lets an authenticated user rotate their password.
 *
 * Flow:
 *   1. User opens /user/password/change
 *   2. Confirms they want a new password (no old password required — we
 *      trust the session)
 *   3. We generate a new permanent password, replace both the bcrypt
 *      hash and the plaintext_password copy, SMS it to the user, and
 *      force-logout everywhere else.
 *
 * Rate limited to 3 changes per hour per user to prevent SMS abuse.
 */
class PasswordController extends Controller
{
    public function __construct(
        private readonly PasswordService $passwords,
        private readonly SmsService $sms,
    ) {}

    public function edit(): View
    {
        return view('user.password.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $key  = 'change-password:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, (int) ceil($seconds / 60));
            return back()->withErrors(['password' => "تلاش‌های تغییر رمز بیش از حد بوده است؛ {$minutes} دقیقه دیگر امتحان کنید."]);
        }

        $data = $request->validate([
            'confirm' => ['required', 'accepted'],
        ], [
            'confirm.accepted' => 'برای تغییر رمز باید تأیید کنید.',
        ]);

        // Generate a brand-new permanent password and persist both the
        // hash and the plaintext copy. The plaintext copy is what gets
        // re-SMSed if the user forgets again in the future.
        $newPassword = $this->passwords->generate();
        $user->forceFill([
            'password'            => $newPassword,
            'plaintext_password' => $newPassword,
        ])->save();

        RateLimiter::hit($key, 3600);

        // SMS the new password to the user.
        $this->sms->send(
            key:     'change-password:' . $user->id . ':' . Str::random(8),
            type:    'password_changed',
            mobile:  $user->mobile,
            message: $this->passwords->smsBody($newPassword, isReset: true),
            user:    $user,
        );

        // Keep the current session but invalidate other sessions so any
        // stolen/old sessions on other devices stop working immediately.
        Auth::logoutOtherDevices($newPassword);

        return back()->with('success', 'رمز عبور جدید برای شما پیامک شد و در سایر دستگاه‌ها اعمال شد.');
    }
}
