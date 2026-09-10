<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create($request->safe()->only(['mobile', 'email', 'first_name', 'last_name', 'password']));
        Profile::query()->create(['user_id' => $user->id]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('success', 'حساب کاربری شما ایجاد شد.');
    }

    public function loginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $key = 'login:'.$request->ip().'|'.$request->input('mobile');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['mobile' => 'تلاش‌های ناموفق زیاد است؛ چند دقیقه دیگر امتحان کنید.']);
        }

        if (!Auth::attempt(['mobile' => $request->input('mobile'), 'password' => $request->input('password'), 'is_active' => true], $request->boolean('remember'))) {
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

    public function forgotSend(Request $request): RedirectResponse
    {
        $request->validate(['mobile' => ['required', 'regex:/^09\d{9}$/']]);

        $user = User::query()->where('mobile', $request->input('mobile'))->first();

        // Always return a positive message even when the user is not found, to
        // avoid leaking which mobile numbers are registered.
        if ($user) {
            // Reset link delivery would be wired to the SMS provider here; in
            // the current sandbox the LogSmsProvider is bound so we just log
            // the request via the notifications channel.
            app(\App\Domains\Notifications\Contracts\SmsProvider::class)
                ->send($user->mobile, 'رمز عبور جدید شما: TEST-RESET-CODE');
        }

        return back()->with('success', 'در صورت وجود حساب، رمز جدید پیامک خواهد شد.');
    }
}
