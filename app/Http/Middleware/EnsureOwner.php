<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\AdminActivityLogger;
use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission = AdminAccess::MANAGE_ADMINS_PERMISSION,
    ): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isOwner() || ! $user->can($permission)) {
            app(AdminActivityLogger::class)->log(
                'unauthorized_admin_management_attempt',
                'An unauthorized attempt to access an owner-only management area was blocked.',
                actor: $user instanceof User ? $user : null,
                metadata: [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'required_permission' => $permission,
                ],
            );

            abort(403);
        }

        return $next($request);
    }
}
