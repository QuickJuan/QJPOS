<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\CurrentRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController
{
    public function index()
    {
        $branches = Branch::query()->select('id', 'name', 'branch_code')->get();
        $loginMethod = $this->loginMethod();

        $codeLength = $loginMethod === 'pincode'
            ? (int) config('waiter.pincode_length', 4)
            : (int) config('waiter.otp_length', 6);

        return Inertia::render('Waiter/Auth/Login', [
            'branches' => $branches,
            'loginMethod' => $loginMethod,
            'codeLength' => $codeLength,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $loginMethod = $this->loginMethod();
        $codeLength = $loginMethod === 'pincode'
            ? (int) config('waiter.pincode_length', 4)
            : (int) config('waiter.otp_length', 6);

        $rules = [
            'identifier' => 'required|string',
            'branch' => 'required|exists:branches,id',
        ];

        if ($loginMethod === 'pincode') {
            $rules['pincode'] = ['required', 'digits:' . $codeLength];
        } else {
            $rules['otp'] = ['required', 'string', 'size:' . $codeLength];
        }

        $request->validate($rules);

        $this->ensureIsNotRateLimited($request);

        $identifier = Str::lower($request->input('identifier'));

        $user = User::where(function ($query) use ($identifier) {
            $query->whereRaw('LOWER(email) = ?', [$identifier])
                ->orWhereRaw('LOWER(username) = ?', [$identifier]);
        })->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'identifier' => 'No user found with this username or email address.',
            ]);
        }

        if ($loginMethod === 'pincode') {
            if (! $user->pincode || ! Hash::check($request->pincode, $user->pincode)) {
                RateLimiter::hit($this->throttleKey($request));
                throw ValidationException::withMessages([
                    'pincode' => 'Invalid pincode. Please try again.',
                ]);
            }
        } else {
            if (! $user->otp_secret || ! \App\Services\OtpSecretService::verifyCode($user->otp_secret, $request->otp, 2)) {
                RateLimiter::hit($this->throttleKey($request));
                throw ValidationException::withMessages([
                    'otp' => 'Invalid OTP. Please check your OTP and try again.',
                ]);
            }
        }

        $userRoles = $user->roles->pluck('name')->map(fn ($role) => strtolower($role))->toArray();
        if (! in_array('waiter', $userRoles) && ! in_array('server', $userRoles)) {
            throw ValidationException::withMessages([
                'identifier' => 'You do not have permission to access the waiter terminal.',
            ]);
        }

        $branch = Branch::findOrFail($request->branch);

        if (! $user->canLoginTo($branch)) {
            throw ValidationException::withMessages([
                'branch' => 'You do not have access to this branch.',
            ]);
        }

        $user->branch_id = $branch->id;
        $user->user_interface = CurrentRole::ORDER_TAKING->value;
        $user->save();
        $user->refresh();

        Auth::login($user);

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();

        \Log::info('Waiter login successful', [
            'user_id' => $user->id,
            'user_interface' => $user->user_interface,
            'session_id' => $request->session()->getId(),
            'role' => CurrentRole::ORDER_TAKING->value,
            'login_method' => $loginMethod,
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
        $currentInterface = $request->user()?->user_interface;
        $redirectUrl = $currentInterface === CurrentRole::CASHIERING->value
            ? route('login', ['_fresh' => time()])
            : route('waiter.login', ['_fresh' => time()]);

        auth()->logout();
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect()->to($redirectUrl);
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
        return Str::lower((string) $request->input('identifier')) . '|waiter|' . $this->loginMethod() . '|' . $request->ip();
    }

    private function loginMethod(): string
    {
        $method = strtolower(trim((string) config('waiter.login_method', 'otp')));

        return in_array($method, ['otp', 'pincode'], true) ? $method : 'otp';
    }
}
