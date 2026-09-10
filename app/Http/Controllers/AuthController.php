<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Accounts\Enums\UserRole;
use App\Domains\Notifications\SmsService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use App\Support\PasswordService;
use App\Support\PersianNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly PasswordService $passwords,
        private readonly SmsService $sms,
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Self-service registration. We generate ONE permanent password,
     * hash it for storage, keep a plaintext copy for re-SMS purposes,
     * SMS it to the user, and log them in immediately.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $mobile = $request->safe()['mobile'];
        $existing = User::query()->where('mobile', $mobile)->first();
        if ($existing) {
            return back()->withErrors(['mobile' => 'این شمارهٔ موبایل قبلاً ثبت شده است.'])->onlyInput('mobile');
        }

        $password = $this->passwords->generate();

        $user = User::query()->create([
            'mobile'              => $mobile,
            'email'               => $request->safe()['email'] ?? null,
            'first_name'          => $request->safe()['first_name'],
            'last_name'           => $request->safe()['last_name'],
            'password'            => $password,                  // hashed via model cast
            'plaintext_password' => $password,                  // for re-SMS only
            'role'                => UserRole::User ?? 'user',
            'is_active'           => true,
            'is_staff'            => false,
        ]);
        Profile::query()->create(['user_id' => $user->id]);

        // SMS the permanent password to the user. We use an idempotency key
        // scoped to (user, registration) so a double-submit doesn't double-send.
        $this->sms->send(
            key: 'register-password:'.$user->id,
            type: 'registration',
            mobile: $user->mobile,
            message: $this->passwords->smsBody($password),
            user: $user,
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'حساب کاربری شما ایجاد شد و رمز عبور به شمارهٔ موبایل شما پیامک شد.');
    }

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $mobile = PersianNormalizer::mobile($request->input('mobile'));
        $key = 'login:'.$request->ip().'|'.$mobile;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['mobile' => 'تلاش‌های ناموفق زیاد است؛ چند دقیقه دیگر امتحان کنید.']);
        }

        if (!Auth::attempt(['mobile' => $mobile, 'password' => $request->input('password'), 'is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($key, 300);
            return back()->withErrors(['mobile' => 'شمارهٔ موبایل یا گذرواژه نادرست است.'])->onlyInput('mobile');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('user.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function forgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Forgot-password flow. Two modes:
     *
     *   • action=remind (default) — re-SMS the user's EXISTING permanent
     *     password. The credential does not change; the user just gets
     *     the same one again because they forgot what it was.
     *
     *   • action=rotate — generate a NEW permanent password, invalidate
     *     the old one, and SMS the new one. This is the equivalent of a
     *     "force password change" without the user being logged in.
     */
    public function forgotSend(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'action' => ['nullable', 'in:remind,rotate'],
        ]);
        $mobile = PersianNormalizer::mobile($data['mobile']);
        $action = $data['action'] ?? 'remind';

        $user = User::query()->where('mobile', $mobile)->first();

        // Always return a positive message even when the user is not found,
        // to avoid leaking which mobile numbers are registered.
        if ($user) {
            if ($action === 'rotate') {
                // Generate a fresh permanent password and replace both the
                // bcrypt hash and the plaintext copy.
                $newPassword = $this->passwords->generate();
                $user->forceFill([
                    'password'            => $newPassword,
                    'plaintext_password' => $newPassword,
                ])->save();

                $this->sms->send(
                    key:     'forgot-rotate:' . $user->id . ':' . Str::random(8),
                    type:    'password_changed',
                    mobile:  $user->mobile,
                    message: $this->passwords->smsBody($newPassword, isReset: true),
                    user:    $user,
                );
            } else {
                // Remind mode: re-SMS the existing permanent password.
                // For legacy users without a plaintext_password (e.g. seeded),
                // generate one now and SMS it — effectively rotating ONCE
                // so subsequent reminders work normally.
                $password = $user->plaintext_password ?: $this->passwords->generate();
                if (!$user->plaintext_password) {
                    $user->forceFill([
                        'password'            => $password,
                        'plaintext_password' => $password,
                    ])->save();
                }

                $this->sms->send(
                    key:     'forgot-remind:' . $user->id . ':' . Str::random(8),
                    type:    'forgot_password',
                    mobile:  $user->mobile,
                    message: $this->passwords->smsBody($password, isReset: true),
                    user:    $user,
                );
            }
        }

        $success = $action === 'rotate'
            ? 'در صورت وجود حساب، رمز عبور جدید ساخته و پیامک خواهد شد.'
            : 'در صورت وجود حساب، رمز عبور ثابت شما پیامک خواهد شد.';

        return back()->with('success', $success);
    }
}
