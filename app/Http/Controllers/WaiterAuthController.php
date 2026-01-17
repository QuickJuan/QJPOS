<?php

namespace App\Http\Controllers;

use App\Enums\CurrentRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class WaiterAuthController extends Controller
{
    public function index()
    {
        return Inertia::render('Waiter/Auth/Login');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
            'branch' => 'required|exists:branches,id',
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => 'No user found with this email address.',
            ]);
        }

        // Verify OTP using TOTP verification
        if (!$user->otp_secret || !\App\Services\OtpSecretService::verifyCode($user->otp_secret, $request->otp, 2)) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP. Please check your OTP and try again.',
            ]);
        }

        // Verify user has waiter or server role (case-insensitive)
        $userRoles = $user->roles->pluck('name')->map(fn($role) => strtolower($role))->toArray();
        if (!in_array('waiter', $userRoles) && !in_array('server', $userRoles)) {
            throw ValidationException::withMessages([
                'email' => 'You do not have permission to access the waiter terminal.',
            ]);
        }

        // Verify branch access
        $branch = Branch::findOrFail($request->branch);

        if (!$user->canLoginTo($branch)) {
            throw ValidationException::withMessages([
                'branch' => 'You do not have access to this branch.',
            ]);
        }

        // Update user's current branch and role
        $user->branch_id = $branch->id;
        $user->current_role = CurrentRole::ORDER_TAKING->value;
        $user->save();
        $user->refresh();

        Auth::login($user);

        RateLimiter::clear($this->throttleKey($request));

        // Regenerate session to prevent fixation
        $request->session()->regenerate();

        \Log::info('Waiter login successful', [
            'user_id' => $user->id,
            'current_role' => $user->current_role,
            'session_id' => $request->session()->getId(),
            'role' => CurrentRole::ORDER_TAKING->value,
        ]);

        return redirect()->intended(route('waiter.home'));
    }

    public function logout(Request $request)
    {
        auth()->logout();

        // Clear session data
        $request->session()->flush();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect to waiter login
        return redirect()->route('waiter.login', ['_fresh' => time()]);
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|waiter|' . $request->ip();
    }
}
