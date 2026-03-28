<?php

namespace App\Http\Middleware;

use App\Models\PageRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandlePageRedirects
{
    /**
     * Paths that should never be processed for redirects.
     * These are system/auth/admin paths that must not be hijacked.
     */
    private const SKIP_PREFIXES = [
        '/admin',
        '/waiter',
        '/login',
        '/logout',
        '/forgot-password',
        '/reset-password',
        '/user',
        '/api',
        '/storage',
        '/manifest.json',
        '/sitemap.xml',
        '/up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only process GET and HEAD requests
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = strtolower('/' . ltrim($request->getPathInfo(), '/'));

        // Skip system paths
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        // Look up the redirect map (cached)
        $map = PageRedirect::getActiveMap();

        if (isset($map[$path])) {
            $entry = $map[$path];
            $toUrl = $entry['to_url'];

            // If the destination is a relative path, make it absolute
            if (!str_starts_with($toUrl, 'http://') && !str_starts_with($toUrl, 'https://')) {
                $toUrl = url('/' . ltrim($toUrl, '/'));
            }

            return redirect($toUrl, $entry['type']);
        }

        return $next($request);
    }
}
