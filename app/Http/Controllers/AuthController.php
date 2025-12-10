<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'branch'   => 'required|exists:branches,id',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $branch = Branch::findOrFail($request->branch);

        if (! $user->canLoginTo($branch)) {
            throw ValidationException::withMessages([
                'branch' => ['You do not have access to this branch.'],
            ]);
        }

        // Invalidate all other sessions for this user (logout from other devices)
        $this->invalidateUserSessions($user);

        // Update the user's current branch_id
        $user->update(['branch_id' => $branch->id]);

        Auth::login($user);

        // // Regenerate session to prevent session fixation and refresh CSRF token
        // session()->regenerate();

        // // Save the selected branch ID in the session
        // session(['active_branch' => $branch]);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return redirect()->route('login');
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
}
