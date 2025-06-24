<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    //

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

        $user = \App\Models\User::where('email', $request->email)->first();

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

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        auth()->logout();

        return redirect()->route('login');
    }
}
