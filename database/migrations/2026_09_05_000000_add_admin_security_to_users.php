<?php

use App\Models\User;
use App\Support\AdminAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->index()->after('password');
            $table->boolean('is_owner')->default(false)->index()->after('is_active');
            $table->boolean('must_change_password')->default(false)->after('is_owner');
            $table->unsignedInteger('session_version')->default(1)->after('must_change_password');
            $table->timestamp('last_login_at')->nullable()->after('session_version');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_ip');
            $table->foreignId('created_by')->nullable()->after('password_changed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::create('admin_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80)->index();
            $table->string('description', 500);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['target_user_id', 'created_at']);
        });

        $this->createRolesAndPermissions();
        $this->promoteExistingOwner();
    }

    private function createRolesAndPermissions(): void
    {
        $now = now();
        $roleIds = [];

        foreach (AdminAccess::roles() as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now],
            );

            $roleIds[$roleName] = DB::table('roles')
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->value('id');
        }

        $permissionIds = [];
        foreach ([AdminAccess::PANEL_PERMISSION, AdminAccess::MANAGE_ADMINS_PERMISSION, AdminAccess::VIEW_ACTIVITY_PERMISSION] as $permissionName) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now],
            );

            $permissionIds[$permissionName] = DB::table('permissions')
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->value('id');
        }

        foreach ($permissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleIds[AdminAccess::OWNER_ROLE],
            ]);
        }

        DB::table('role_has_permissions')->updateOrInsert([
            'permission_id' => $permissionIds[AdminAccess::PANEL_PERMISSION],
            'role_id' => $roleIds[AdminAccess::USER_ADMIN_ROLE],
        ]);

    }

    private function promoteExistingOwner(): void
    {
        $owner = DB::table('users')
            ->where('email', config('admin.owner_email'))
            ->first();

        if (! $owner) {
            $owner = DB::table('users')
                ->join('model_has_roles', function ($join): void {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', User::class);
                })
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereIn('roles.name', ['owner_super_admin', 'super_admin'])
                ->select('users.*')
                ->orderBy('users.id')
                ->first();
        }

        $owner ??= DB::table('users')->orderBy('id')->first();

        if (! $owner) {
            return;
        }

        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);

        $ownerRoleId = DB::table('roles')
            ->where('name', AdminAccess::OWNER_ROLE)
            ->where('guard_name', 'web')
            ->value('id');

        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => $ownerRoleId,
            'model_type' => User::class,
            'model_id' => $owner->id,
        ]);

        app('cache')->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'phone',
                'is_active',
                'is_owner',
                'must_change_password',
                'session_version',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
                'created_by',
                'updated_by',
                'deleted_at',
            ]);
        });
    }
};
