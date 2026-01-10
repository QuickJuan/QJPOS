<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function index()
    {
        $branches = Branch::query()->select('id', 'name', 'branch_code')->get();

        return Inertia::render('Auth/Login', [
            'branches' => $branches,
        ]);
    }

    public function checkBranch(string $id)
    {
        $branch = Branch::findOrFail($id);

        if (Auth::user()?->canLoginTo($branch)) {
            return response()->noContent();
        }

        abort(403);
    }

    public function login(Request $request)
    {

        // Debug logging
        \Log::info('Login attempt', [
            'session_id' => $request->session()->getId(),
            'csrf_token' => $request->session()->token(),
            'has_user' => (bool) $request->user(),
        ]);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'branch'   => 'required|exists:branches,id',
        ]);

        $this->ensureIsNotRateLimited($request);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        /**
         * if current branch id and selected branch id are not the same
         * check if user has a cashier session with closing time null with the current branch
         * if yes, prevent login to another branch
         * if no, allow login to another branch
         */

        $currentBranchId = $user->branch_id;
        $curentBranchName = $user->branch?->name;
        if ($currentBranchId && $currentBranchId != $request->branch) {
            $openCashierSession = $user->cashierSessions()
                ->where('branch_id', $currentBranchId)
                ->whereNull('closing_time')
                ->first();

            if ($openCashierSession) {
                throw ValidationException::withMessages([
                    'branch' => ["You have an open cashier session in your current branch ($curentBranchName). Please close it before switching branches."],
                ]);
            }
        }



        $branch = Branch::findOrFail($request->branch);

        if (! $user->canLoginTo($branch)) {
            throw ValidationException::withMessages([
                'branch' => ['You do not have access to this branch.'],
            ]);
        }

        // Invalidate all other sessions for this user (logout from other devices)
        // Commented out for file-based sessions - only works with database sessions
        // $this->invalidateUserSessions($user);

        // Update the user's current branch_id
        $user->update(['branch_id' => $branch->id]);

        Auth::login($user);

        RateLimiter::clear($this->throttleKey($request));

        // Save session explicitly before redirecting
        $request->session()->save();

        \Log::info('Login successful', [
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
        ]);

        // Use standard redirect - session is already persisted
        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        auth()->logout();

        // Clear session data
        $request->session()->flush();

        // Regenerate the CSRF token
        $request->session()->regenerateToken();

        // Redirect to login with query parameter to force fresh page load
        return redirect()->route('login', ['_fresh' => time()]);
    }

    /**
     * Invalidate all other sessions for a user.
     * This ensures only one active session per user at a time.
     * When a user logs in from a new device, their previous sessions are deleted,
     * effectively logging them out from other locations.
     */
    private function invalidateUserSessions(User $user): void
    {
        // Delete all sessions for this user from the sessions table
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
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
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
