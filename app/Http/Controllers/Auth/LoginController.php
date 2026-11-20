<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Please contact an administrator.',
                ]);
            }

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $request->session()->regenerate();

            ActivityLog::record(
                action: 'auth.login',
                module: 'auth',
                description: "User {$user->name} logged in successfully.",
                record: $user
            );

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey);

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::record(
                action: 'auth.logout',
                module: 'auth',
                description: "User {$user->name} logged out.",
                record: $user
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
