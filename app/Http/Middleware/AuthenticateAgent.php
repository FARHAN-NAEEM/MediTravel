<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateAgent
{
    public function handle(Request $request, Closure $next)
    {
        $agent = Auth::guard('agent')->user();
        if (! $agent || ! $agent->is_active || (int) $request->session()->get('agent_session_version') !== $agent->session_version) {
            Auth::guard('agent')->logout();
            $request->session()->forget('agent_session_version');

            return redirect()->route('agent.login');
        }

        return $next($request);
    }
}
