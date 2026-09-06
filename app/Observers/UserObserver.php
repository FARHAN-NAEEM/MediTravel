<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AdminActivityLogger;
use Illuminate\Auth\Access\AuthorizationException;

class UserObserver
{
    public function creating(User $user): void
    {
        if ($user->is_owner) {
            $this->reject($user, 'owner_promotion_blocked', 'An attempt to create an additional owner account was blocked.');
        }
    }

    public function updating(User $user): void
    {
        $wasOwner = (bool) $user->getOriginal('is_owner');

        if (! $wasOwner && $user->isDirty('is_owner') && $user->is_owner) {
            $this->reject($user, 'owner_promotion_blocked', 'An attempt to promote an account to owner was blocked.');
        }

        if ($wasOwner && $user->isDirty('is_owner')) {
            $this->reject($user, 'owner_protection_triggered', 'An attempt to change the protected owner marker was blocked.');
        }

        if ($wasOwner && $user->isDirty('is_active') && ! $user->is_active) {
            $this->reject($user, 'owner_protection_triggered', 'An attempt to deactivate the owner account was blocked.');
        }
    }

    public function deleting(User $user): void
    {
        if ($user->isOwner()) {
            $this->reject($user, 'owner_protection_triggered', 'An attempt to remove the owner account was blocked.');
        }
    }

    private function reject(User $target, string $action, string $description): never
    {
        app(AdminActivityLogger::class)->log($action, $description, $target);

        throw new AuthorizationException('The protected owner account cannot be changed by this action.');
    }
}
