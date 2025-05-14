<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tenancy\Facades\Tenancy;

class BlockTenantAccessToCentral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block if request is coming from a tenant domain
        if (!in_array($request->getHost(), config('tenancy.central_domains', []))) {
            abort(403, 'Access denied! Tenant can visit this page.');
        }

        return $next($request);
    }
}
