<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ProductionSecurity
{
    public function handle(Request $request, Closure $next)
    {
        $production = app()->environment('production');
        $root = rtrim(config('app.url'), '/');
        if ($production && str_starts_with($root, 'https://')) {
            URL::forceRootUrl($root);
            URL::forceScheme('https');
            if (! $request->isSecure()) {
                return redirect()->to($root.'/'.ltrim($request->getRequestUri(), '/'), 308);
            }
        }

        $response = $next($request);
        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        if ($production && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
