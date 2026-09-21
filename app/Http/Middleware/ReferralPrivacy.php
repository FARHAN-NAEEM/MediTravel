<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReferralPrivacy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'; base-uri 'self'; object-src 'none'");

        return $response;
    }
}
