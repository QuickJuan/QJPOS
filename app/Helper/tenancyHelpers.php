<?php

if (!function_exists('isCentralDomain')) {
    function isCentralDomain(): bool
    {
        $currentHost = $_SERVER['HTTP_HOST'] ?? null;

        // Strip port number if present (e.g., 'localhost:8000' becomes 'localhost')
        $currentHost = explode(':', $currentHost)[0] ?? $currentHost;

        $centralDomains = config('tenancy.central_domains', []);

        // If it's a string (for backward compatibility)
        if (is_string($centralDomains)) {
            $centralDomain = explode(':', $centralDomains)[0] ?? $centralDomains;
            return $currentHost === $centralDomain;
        }

        // If it's an array of central domains, strip ports from all
        $centralDomainsWithoutPorts = array_map(function($domain) {
            return explode(':', $domain)[0] ?? $domain;
        }, $centralDomains);

        return in_array($currentHost, $centralDomainsWithoutPorts, true);
    }
}
