<?php

namespace App\Support;

final class AdminAccess
{
    public const OWNER_ROLE = 'owner_super_admin';

    public const USER_ADMIN_ROLE = 'user_admin';

    public const PANEL_PERMISSION = 'access_admin_panel';

    public const MANAGE_ADMINS_PERMISSION = 'manage_admins';

    public const VIEW_ACTIVITY_PERMISSION = 'view_admin_activity_logs';

    public const MANAGE_SITE_SETTINGS_PERMISSION = 'manage_site_settings';

    /** @return array<int, string> */
    public static function roles(): array
    {
        return [self::OWNER_ROLE, self::USER_ADMIN_ROLE];
    }
}
