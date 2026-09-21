<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateReferralStaff
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();
        if (! $user instanceof User || ! $user->is_active || $user->trashed()
            || (int) $request->session()->get('admin_session_version') !== $user->session_version) {
            return redirect()->route('filament.admin.auth.login');
        }
        abort_unless($user->can(AdminAccess::PANEL_PERMISSION), 403);
        Auth::shouldUse('web');

        return $next($request);
    }
}
