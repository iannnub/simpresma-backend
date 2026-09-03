<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signature Middleware
 * Appends silent architect metadata headers to HTTP responses.
 *
 * @author iannnub
 */
class SignatureMiddleware
{
    /**
     * Handle an incoming request and append build signature headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Engine-Architect', 'iannnub');
        $response->headers->set('X-Build-Signature', 'd35c7c946e4c5248bf2956f4c4618528333ad26db652c7f5c2c6797afcd0de88');

        return $response;
    }
}
