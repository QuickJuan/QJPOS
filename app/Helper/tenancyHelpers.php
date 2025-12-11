<?php

if (!function_exists('isCentralDomain')) {
    function isCentralDomain(): bool
    {
        $currentHost = $_SERVER['HTTP_HOST'] ?? null;
        $centralDomains = config('tenancy.central_domains', []);

        // If it's a string (for backward compatibility)
        if (is_string($centralDomains)) {
            return $currentHost === $centralDomains;
        }

        // If it's an array of central domains
        return in_array($currentHost, $centralDomains, true);
    }
}
