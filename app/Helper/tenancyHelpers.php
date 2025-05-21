<?php

if (!function_exists('isCentralDomain')) {
    function isCentralDomain(): bool
    {
        return ($_SERVER['HTTP_HOST'] ?? null) === (config('tenancy.central_domain') ?? env('CENTRAL_DOMAIN'));
    }
}
