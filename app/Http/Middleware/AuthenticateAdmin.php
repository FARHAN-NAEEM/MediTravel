<?php

namespace App\Http\Middleware;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\Authenticate;

class AuthenticateAdmin extends Authenticate
{
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);
        }

        $this->auth->shouldUse(Filament::getAuthGuard());
        $user = $guard->user();
        $sessionVersion = $request->session()->get('admin_session_version');

        if (
            ! $user instanceof User
            || ! $user->canAccessPanel(Filament::getCurrentPanel())
            || $sessionVersion === null
            || (int) $sessionVersion !== $user->session_version
        ) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $this->unauthenticated($request, $guards);
        }
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
