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
    public function handle(Request $request, Closure $next, string $role, string ...$additionalRoles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $currentRole = $request->user()->current_role;

        $currentRoleValue = $currentRole instanceof CurrentRole
            ? $currentRole->value
            : (is_string($currentRole) ? $currentRole : (string) $currentRole);

        // Laravel splits middleware parameters by comma, so role:cashiering,order_taking
        // arrives as handle(..., 'cashiering', 'order_taking'). Still support a single
        // comma-separated string defensively.
        $rawAllowedRoles = collect(array_merge([$role], $additionalRoles))
            ->flatMap(fn (string $item) => explode(',', $item))
            ->map(fn ($item) => trim((string) $item))
            ->filter(fn ($item) => $item !== '')
            ->values();

        $allowedRoleValues = $rawAllowedRoles
            ->map(function (string $item) {
                return Str::of($item)
                    ->lower()
                    ->replace(' ', '_')
                    ->replace('-', '_')
                    ->value();
            })
            ->values();

        $currentRoleValue = Str::of($currentRoleValue)
            ->lower()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->value();

        // TEMPORARY DEBUG LOGGING
        \Log::info('🔍 ROLE MIDDLEWARE DEBUG', [
            'url' => $request->url(),
            'current_role_raw' => $request->user()->current_role,
            'current_role_processed' => $currentRoleValue,
            'allowed_roles' => $allowedRoleValues->toArray(),
            'middleware_params' => ['role' => $role, 'additional' => $additionalRoles],
        ]);

        // Check if user has any allowed role
        if (!$allowedRoleValues->contains($currentRoleValue)) {
            \Log::warning('🚫 ROLE CHECK FAILED', [
                'current' => $currentRoleValue,
                'allowed' => $allowedRoleValues->toArray(),
            ]);
            abort(403, 'Access denied. You do not have the required role.');
        }

        \Log::info('✅ ROLE CHECK PASSED');

        return $next($request);
    }
}

