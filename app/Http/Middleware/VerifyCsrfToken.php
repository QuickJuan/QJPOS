<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // Log CSRF token debugging info
        \Log::info('CSRF Token Check', [
            'url' => $request->url(),
            'method' => $request->method(),
            'session_token' => $request->session()->token(),
            'header_token' => $request->header('X-CSRF-TOKEN'),
            'input_token' => $request->input('_token'),
            'session_id' => $request->session()->getId(),
        ]);

        return parent::handle($request, $next);
    }
}
