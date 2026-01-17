<?php

namespace App\Http\Middleware;

use App\Enums\CurrentRole;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  The required role (e.g., 'order_taking', 'cashiering')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $currentRole = $request->user()->current_role;

        $currentRoleValue = $currentRole instanceof CurrentRole
            ? $currentRole->value
            : (is_string($currentRole) ? $currentRole : (string) $currentRole);

        $requiredRoleValue = Str::of($role)
            ->lower()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->value();

        $currentRoleValue = Str::of($currentRoleValue)
            ->lower()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->value();

        // Log for debugging
        \Log::info('Role check', [
            'user_id' => $request->user()->id,
            'current_role' => $currentRoleValue,
            'required_role' => $requiredRoleValue,
            'path' => $request->path(),
        ]);

        // Check if user has the required role
        if ($currentRoleValue !== $requiredRoleValue) {
            // Redirect based on current role
            if ($currentRoleValue === CurrentRole::ORDER_TAKING->value) {
                abort(403, 'Access denied. You are logged in as a waiter and cannot access cashier features.');
            } elseif ($currentRoleValue === CurrentRole::CASHIERING->value) {
                abort(403, 'Access denied. You are logged in as a cashier and cannot access waiter features.');
            }

            abort(403, 'Access denied. You do not have the required role. Current: ' . $currentRoleValue . ', Required: ' . $requiredRoleValue);
        }

        return $next($request);
    }
}

