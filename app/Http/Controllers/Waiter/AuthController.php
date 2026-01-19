<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\CurrentRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController
{
    public function index()
    {
        $branches = Branch::query()->select('id', 'name', 'branch_code')->get();

        return Inertia::render('Waiter/Auth/Login', [
            'branches' => $branches,
        ]);
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

        if (! $user) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => 'No user found with this email address.',
            ]);
        }

        if (! $user->otp_secret || ! \App\Services\OtpSecretService::verifyCode($user->otp_secret, $request->otp, 2)) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP. Please check your OTP and try again.',
            ]);
        }

        $userRoles = $user->roles->pluck('name')->map(fn ($role) => strtolower($role))->toArray();
        if (! in_array('waiter', $userRoles) && ! in_array('server', $userRoles)) {
            throw ValidationException::withMessages([
                'email' => 'You do not have permission to access the waiter terminal.',
            ]);
        }

        $branch = Branch::findOrFail($request->branch);

        if (! $user->canLoginTo($branch)) {
            throw ValidationException::withMessages([
                'branch' => 'You do not have access to this branch.',
            ]);
        }

        $user->branch_id = $branch->id;
        $user->current_role = CurrentRole::ORDER_TAKING->value;
        $user->save();
        $user->refresh();

        Auth::login($user);

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        \Log::info('Waiter login successful', [
            'user_id' => $user->id,
            'current_role' => $user->current_role,
            'session_id' => $request->session()->getId(),
            'role' => CurrentRole::ORDER_TAKING->value,
        ]);

        // Force a full reload so the regenerated CSRF token is reflected in the page.
        // This prevents 419 "Page Expired" on subsequent POSTs after login.
        return Inertia::location(route('table-rooms.index'));
    }

    public function getBranchUsers(Request $request, $branchId)
    {
        $request->validate([
            'branch_id' => 'sometimes|exists:branches,id',
        ]);

        $branch = Branch::findOrFail($branchId);

        $users = User::whereHas('branches', function ($query) use ($branchId) {
            $query->where('branches.id', $branchId);
        })
        ->whereHas('roles', function ($query) {
            $query->whereIn('name', ['Waiter', 'Server']);
        })
        ->select('id', 'name', 'email')
        ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->route('waiter.login', ['_fresh' => time()]);
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
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
