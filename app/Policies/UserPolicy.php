<?php

namespace App\Policies;

use App\Models\User;
use App\Support\AdminAccess;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function view(User $user, User $target): bool
    {
        return $this->canManage($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, User $target): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, User $target): bool
    {
        return $this->canManage($user) && ! $target->isOwner();
    }

    public function restore(User $user, User $target): bool
    {
        return $this->canManage($user) && ! $target->isOwner();
    }

    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }

    private function canManage(User $user): bool
    {
        return $user->isOwner() && $user->can(AdminAccess::MANAGE_ADMINS_PERMISSION);
    }
}
