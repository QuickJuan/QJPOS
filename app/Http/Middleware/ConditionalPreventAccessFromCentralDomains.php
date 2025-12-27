<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class ConditionalPreventAccessFromCentralDomains
{
    protected PreventAccessFromCentralDomains $middleware;

    public function __construct(PreventAccessFromCentralDomains $middleware)
    {
        $this->middleware = $middleware;
    }

    public function handle(Request $request, Closure $next)
    {
        // Check if this is a central domain request at REQUEST TIME
        $httpHost = $request->getHost();

        $centralDomains = config('tenancy.central_domains', []);
        if (is_string($centralDomains)) {
            $centralDomains = [$centralDomains];
        }

        // Normalize domains (strip ports)
        $centralDomains = array_map(function($domain) {
            return explode(':', $domain)[0];
        }, $centralDomains);

        // If this is a central domain, skip this middleware
        if (in_array($httpHost, $centralDomains, true)) {
            return $next($request);
        }

        // Otherwise, apply the middleware
        return $this->middleware->handle($request, $next);
    }
}
