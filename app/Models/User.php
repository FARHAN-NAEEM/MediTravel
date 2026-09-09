<?php

namespace App\Models;

use App\Support\AdminAccess;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles {
        assignRole as protected assignRoleWithoutOwnershipGuard;
        removeRole as protected removeRoleWithoutOwnershipGuard;
        syncRoles as protected syncRolesWithoutOwnershipGuard;
    }
    use Notifiable;
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_owner' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->is_active
            && ! $this->trashed()
            && $this->can(AdminAccess::PANEL_PERMISSION);
    }

    public function isOwner(): bool
    {
        return $this->is_owner === true;
    }

    public function assignRole(...$roles)
    {
        $this->assertRoleChangeAllowed($roles);

        return $this->assignRoleWithoutOwnershipGuard(...$roles);
    }

    public function removeRole(...$roles)
    {
        if ($this->exists && $this->isOwner()) {
            $this->rejectRoleChange('An attempt to remove a role from the protected owner account was blocked.');
        }

        return $this->removeRoleWithoutOwnershipGuard(...$roles);
    }

    public function syncRoles(...$roles)
    {
        $this->assertRoleChangeAllowed($roles);

        return $this->syncRolesWithoutOwnershipGuard(...$roles);
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->isOwner() ? 'Owner / Super Admin' : 'User Admin';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by')->withTrashed();
    }

    public function performedAdminActivities(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'actor_id');
    }

    public function adminActivityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class, 'target_user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');
        $notification = app(ResetPasswordNotification::class, ['token' => $token]);
        $notification->url = $panel->getResetPasswordUrl($token, $this);

        $this->notifyNow($notification);
    }

    private function assertRoleChangeAllowed(array $roles): void
    {
        if (! $this->exists) {
            return;
        }

        $roleNames = $this->resolveRoleNames($roles);

        if ($this->isOwner() && $roleNames !== [AdminAccess::OWNER_ROLE]) {
            $this->rejectRoleChange('An attempt to change the protected owner role was blocked.');
        }

        if (! $this->isOwner() && in_array(AdminAccess::OWNER_ROLE, $roleNames, true)) {
            $this->rejectRoleChange('An attempt to promote a non-owner account to owner was blocked.');
        }
    }

    private function resolveRoleNames(iterable $roles): array
    {
        $names = [];

        foreach ($roles as $role) {
            if (is_array($role) || $role instanceof Collection) {
                $names = [...$names, ...$this->resolveRoleNames($role)];

                continue;
            }

            if ($role instanceof \BackedEnum) {
                $role = $role->value;
            }

            if ($role instanceof RoleContract) {
                $names[] = $role->name;

                continue;
            }

            if (is_int($role) || (is_string($role) && ctype_digit($role))) {
                $names[] = Role::query()->find($role)?->name;

                continue;
            }

            if (is_string($role)) {
                $names[] = $role;
            }
        }

        $names = array_values(array_unique(array_filter($names)));
        sort($names);

        return $names;
    }

    private function rejectRoleChange(string $description): never
    {
        app(\App\Services\AdminActivityLogger::class)->log(
            'role_change_blocked',
            $description,
            $this,
        );

        throw new AuthorizationException('Owner assignment and protected owner role changes are not allowed.');
    }
}
