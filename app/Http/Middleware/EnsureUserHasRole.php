<?php

namespace App\Http\Middleware;

use App\Enums\CurrentRole;
use Closure;
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

        // Log for debugging
        \Log::info('Role check', [
            'user_id' => $request->user()->id,
            'current_role' => $currentRole,
            'required_role' => $role,
            'path' => $request->path(),
        ]);

        // Check if user has the required role
        if ($currentRole !== $role) {
            // Redirect based on current role
            if ($currentRole === CurrentRole::ORDER_TAKING->value) {
                abort(403, 'Access denied. You are logged in as a waiter and cannot access cashier features.');
            } elseif ($currentRole === CurrentRole::CASHIERING->value) {
                abort(403, 'Access denied. You are logged in as a cashier and cannot access waiter features.');
            }

            $currentRoleStr = is_object($currentRole) ? $currentRole->value : ($currentRole ?? 'none');
            abort(403, 'Access denied. You do not have the required role. Current: ' . $currentRoleStr . ', Required: ' . $role);
        }

        return $next($request);
    }
}

