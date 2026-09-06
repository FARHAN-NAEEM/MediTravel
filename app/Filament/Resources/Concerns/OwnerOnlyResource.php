<?php

namespace App\Filament\Resources\Concerns;

use App\Models\User;
use App\Support\AdminAccess;

trait OwnerOnlyResource
{
    public static function shouldRegisterNavigation(): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canViewAny(): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canView($record): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canCreate(): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canEdit($record): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canDelete($record): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    public static function canDeleteAny(): bool
    {
        return static::ownerCanManageSiteSettings();
    }

    private static function ownerCanManageSiteSettings(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->isOwner()
            && $user->can(AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION);
    }
}
