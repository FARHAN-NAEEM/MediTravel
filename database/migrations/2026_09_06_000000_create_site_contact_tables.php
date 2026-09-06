<?php

use App\Support\AdminAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_channels', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 24)->index();
            $table->string('label');
            $table->string('value');
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['type', 'value']);
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('office_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('district');
            $table->text('address');
            $table->string('phone', 40)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 32)->index();
            $table->string('name');
            $table->string('url', 2048);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $this->migrateLegacyContactSettings();
        $this->grantOwnerPermission();
    }

    private function migrateLegacyContactSettings(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $settings = DB::table('settings')
            ->whereIn('key', ['support_phone', 'whatsapp_number', 'support_email'])
            ->pluck('value', 'key');

        $legacyChannels = [
            ['key' => 'support_phone', 'type' => 'phone', 'label' => 'Support'],
            ['key' => 'whatsapp_number', 'type' => 'whatsapp', 'label' => 'WhatsApp Support'],
            ['key' => 'support_email', 'type' => 'email', 'label' => 'Support'],
        ];

        foreach ($legacyChannels as $channel) {
            $value = trim((string) ($settings[$channel['key']] ?? ''));

            if ($value === '') {
                continue;
            }

            DB::table('contact_channels')->updateOrInsert(
                ['type' => $channel['type'], 'value' => $value],
                [
                    'label' => $channel['label'],
                    'is_primary' => true,
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function grantOwnerPermission(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();
        DB::table('permissions')->updateOrInsert(
            ['name' => AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION, 'guard_name' => 'web'],
            ['created_at' => $now, 'updated_at' => $now],
        );

        $permissionId = DB::table('permissions')
            ->where('name', AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION)
            ->where('guard_name', 'web')
            ->value('id');
        $ownerRoleId = DB::table('roles')
            ->where('name', AdminAccess::OWNER_ROLE)
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId && $ownerRoleId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $ownerRoleId,
            ]);
        }

        app('cache')->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')
                ->where('name', AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION)
                ->where('guard_name', 'web')
                ->value('id');

            if ($permissionId) {
                DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }

        Schema::dropIfExists('social_links');
        Schema::dropIfExists('office_locations');
        Schema::dropIfExists('contact_channels');
    }
};
