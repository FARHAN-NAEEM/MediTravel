<?php

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRoleIds = DB::table('roles')
            ->whereIn('name', ['super_admin', 'manager', 'support_agent'])
            ->where('guard_name', 'web')
            ->pluck('id');

        if ($legacyRoleIds->isEmpty()) {
            return;
        }

        $userAdminRoleId = DB::table('roles')
            ->where('name', AdminAccess::USER_ADMIN_ROLE)
            ->where('guard_name', 'web')
            ->value('id');

        $ownerId = DB::table('users')->where('is_owner', true)->orderBy('id')->value('id');
        $legacyAdminIds = DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereIn('role_id', $legacyRoleIds)
            ->pluck('model_id')
            ->unique();

        foreach ($legacyAdminIds as $adminId) {
            if ((int) $adminId === (int) $ownerId) {
                continue;
            }

            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $userAdminRoleId,
                'model_type' => User::class,
                'model_id' => $adminId,
            ]);
        }

        DB::table('model_has_roles')->whereIn('role_id', $legacyRoleIds)->delete();
        DB::table('role_has_permissions')->whereIn('role_id', $legacyRoleIds)->delete();
        DB::table('roles')->whereIn('id', $legacyRoleIds)->delete();

        app('cache')->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        // Legacy roles are intentionally not restored because their prior assignments are ambiguous.
    }
};
