<?php

namespace App\Services;

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminManager
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function createUserAdmin(User $actor, array $data): User
    {
        $this->assertOwner($actor);

        $validated = validator(Arr::only($data, ['name', 'email', 'phone']), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9][0-9\s().-]{6,30}$/'],
        ])->validate();

        return DB::transaction(function () use ($actor, $validated): User {
            $admin = User::query()->create([
                ...$validated,
                'password' => Hash::make(Str::password(48)),
            ]);

            $admin->forceFill([
                'is_active' => true,
                'must_change_password' => true,
                'created_by' => $actor->getKey(),
                'updated_by' => $actor->getKey(),
            ])->saveQuietly();
            $admin->syncRoles([AdminAccess::USER_ADMIN_ROLE]);

            $this->activityLogger->log(
                'admin_created',
                "User admin {$admin->name} was created.",
                $admin,
                $actor,
            );

            return $admin->refresh();
        });
    }

    public function updateAdmin(User $actor, User $target, array $data): User
    {
        $this->assertOwner($actor);

        $validated = validator(Arr::only($data, ['name', 'email', 'phone']), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target)],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9][0-9\s().-]{6,30}$/'],
        ])->validate();

        $changes = [];
        foreach ($validated as $field => $value) {
            if ($target->{$field} !== $value) {
                $changes[] = $field;
            }
        }

        $target->fill($validated);
        $target->forceFill(['updated_by' => $actor->getKey()]);
        $target->save();

        if ($changes !== []) {
            $this->activityLogger->log(
                'admin_information_updated',
                "Admin information was updated for {$target->name}.",
                $target,
                $actor,
                ['changed_fields' => $changes],
            );
        }

        return $target->refresh();
    }

    public function activate(User $actor, User $target): void
    {
        $this->assertManageable($actor, $target);

        $target->forceFill([
            'is_active' => true,
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->activityLogger->log('admin_activated', "{$target->name} was activated.", $target, $actor);
    }

    public function deactivate(User $actor, User $target): void
    {
        $this->assertManageable($actor, $target);

        $target->forceFill([
            'is_active' => false,
            'session_version' => $target->session_version + 1,
            'remember_token' => Str::random(60),
            'updated_by' => $actor->getKey(),
        ])->save();

        $this->activityLogger->log('admin_deactivated', "{$target->name} was deactivated.", $target, $actor);
    }

    public function remove(User $actor, User $target): void
    {
        $this->assertManageable($actor, $target);

        DB::transaction(function () use ($actor, $target): void {
            $target->forceFill([
                'is_active' => false,
                'session_version' => $target->session_version + 1,
                'remember_token' => Str::random(60),
                'updated_by' => $actor->getKey(),
            ])->save();
            $target->delete();

            $this->activityLogger->log('admin_removed', "{$target->name} was archived and removed from active administration.", $target, $actor);
        });
    }

    public function sendPasswordResetLink(User $actor, User $target): string
    {
        $this->assertManageable($actor, $target);

        if (! $target->is_active) {
            throw ValidationException::withMessages([
                'status' => 'Activate this admin before sending a password setup or reset link.',
            ]);
        }

        $status = Password::broker()->sendResetLink(['email' => $target->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->activityLogger->log(
                'password_reset_requested',
                "A secure password reset link was sent to {$target->name}.",
                $target,
                $actor,
            );
        }

        return $status;
    }

    private function assertManageable(User $actor, User $target): void
    {
        $this->assertOwner($actor);

        if ($target->isOwner()) {
            $this->activityLogger->log(
                'owner_protection_triggered',
                'An owner account management action was blocked.',
                $target,
                $actor,
            );

            throw new AuthorizationException('The owner account cannot be deactivated, removed, or assigned another role.');
        }
    }

    private function assertOwner(User $actor): void
    {
        if (! $actor->isOwner() || ! $actor->can(AdminAccess::MANAGE_ADMINS_PERMISSION)) {
            $this->activityLogger->log(
                'unauthorized_admin_management_attempt',
                'An unauthorized admin management action was blocked.',
                actor: $actor,
            );

            throw new AuthorizationException('Only the owner can manage administrator accounts.');
        }
    }
}
