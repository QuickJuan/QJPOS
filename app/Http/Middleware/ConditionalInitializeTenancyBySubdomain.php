<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;

class ConditionalInitializeTenancyBySubdomain
{
    protected InitializeTenancyBySubdomain $initializeTenancy;

    public function __construct(InitializeTenancyBySubdomain $initializeTenancy)
    {
        $this->initializeTenancy = $initializeTenancy;
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

        // If this is a central domain, skip tenancy initialization
        if (in_array($httpHost, $centralDomains, true)) {
            return $next($request);
        }

        // Otherwise, initialize tenancy
        return $this->initializeTenancy->handle($request, $next);
    }
}
