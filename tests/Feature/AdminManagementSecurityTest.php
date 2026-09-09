<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Resources\AdminResource;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use App\Services\AdminManager;
use App\Support\AdminAccess;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminManagementSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_owner_can_create_a_user_admin_and_role_input_is_ignored(): void
    {
        $owner = $this->owner();

        $admin = app(AdminManager::class)->createUserAdmin($owner, [
            'name' => 'Operations Admin',
            'email' => 'operations@example.com',
            'phone' => '+880 1700-000000',
            'role' => AdminAccess::OWNER_ROLE,
            'is_owner' => true,
        ]);

        $this->assertTrue($admin->hasRole(AdminAccess::USER_ADMIN_ROLE));
        $this->assertFalse($admin->isOwner());
        $this->assertTrue($admin->is_active);
        $this->assertTrue($admin->must_change_password);
        $this->assertSame($owner->id, $admin->created_by);
        $this->assertDatabaseHas('admin_activity_logs', [
            'actor_id' => $owner->id,
            'target_user_id' => $admin->id,
            'action' => 'admin_created',
        ]);
    }

    public function test_user_admin_cannot_manage_other_admins_or_open_owner_routes(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'one@example.com');

        $this->actingAs($admin)
            ->withSession(['admin_session_version' => $admin->session_version])
            ->get('/admin/admins')
            ->assertForbidden();

        $this->expectException(AuthorizationException::class);

        app(AdminManager::class)->createUserAdmin($admin, [
            'name' => 'Blocked Admin',
            'email' => 'blocked@example.com',
        ]);
    }

    public function test_owner_account_cannot_be_deactivated_deleted_or_reassigned(): void
    {
        $owner = $this->owner();

        try {
            app(AdminManager::class)->deactivate($owner, $owner);
            $this->fail('Owner deactivation should have been blocked.');
        } catch (AuthorizationException) {
            $this->assertTrue($owner->fresh()->is_active);
        }

        try {
            $owner->delete();
            $this->fail('Owner deletion should have been blocked.');
        } catch (AuthorizationException) {
            $this->assertDatabaseHas('users', ['id' => $owner->id, 'deleted_at' => null]);
        }

        $owner->forceFill(['is_owner' => false]);

        $this->expectException(AuthorizationException::class);
        $owner->save();
    }

    public function test_non_owner_cannot_be_promoted_by_mass_assignment_or_forced_model_update(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'promote@example.com');

        $admin->fill(['is_owner' => true, 'name' => 'Still User Admin']);
        $this->assertFalse($admin->isOwner());

        $admin->forceFill(['is_owner' => true]);

        $this->expectException(AuthorizationException::class);
        $admin->save();
    }

    public function test_direct_role_helpers_cannot_transfer_or_remove_ownership(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'role-guard@example.com');

        try {
            $admin->syncRoles([AdminAccess::OWNER_ROLE]);
            $this->fail('A user admin must not be assignable to the owner role.');
        } catch (AuthorizationException) {
            $this->assertTrue($admin->fresh()->hasRole(AdminAccess::USER_ADMIN_ROLE));
        }

        try {
            $owner->removeRole(AdminAccess::OWNER_ROLE);
            $this->fail('The protected owner role must not be removable.');
        } catch (AuthorizationException) {
            $this->assertTrue($owner->fresh()->hasRole(AdminAccess::OWNER_ROLE));
        }

        $this->assertSame(2, AdminActivityLog::query()->where('action', 'role_change_blocked')->count());
    }

    public function test_deactivation_invalidates_existing_session_and_reactivation_restores_panel_access(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'session@example.com');
        $oldVersion = $admin->session_version;

        app(AdminManager::class)->deactivate($owner, $admin);
        $admin->refresh();

        $this->assertFalse($admin->is_active);
        $this->assertGreaterThan($oldVersion, $admin->session_version);
        $this->assertFalse($admin->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($admin)
            ->withSession(['admin_session_version' => $oldVersion])
            ->get('/admin')
            ->assertRedirect(route('filament.admin.auth.login'));

        app(AdminManager::class)->activate($owner, $admin);
        $this->assertTrue($admin->fresh()->canAccessPanel(filament()->getPanel('admin')));
    }

    public function test_password_reset_token_is_one_time_and_invalidates_old_sessions(): void
    {
        Notification::fake();
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'reset@example.com');
        $oldVersion = $admin->session_version;
        $token = Password::broker()->createToken($admin);
        $password = 'New-Strong-Password-2026!';

        $status = Password::broker()->reset([
            'email' => $admin->email,
            'password' => $password,
            'password_confirmation' => $password,
            'token' => $token,
        ], function (User $user, string $newPassword): void {
            $user->forceFill([
                'password' => Hash::make($newPassword),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        $this->assertSame(Password::PASSWORD_RESET, $status);
        $this->assertTrue(Hash::check($password, $admin->fresh()->password));
        $this->assertGreaterThan($oldVersion, $admin->fresh()->session_version);
        $this->assertFalse($admin->fresh()->must_change_password);
        Notification::assertSentTo($admin, PasswordChangedNotification::class);

        $secondStatus = Password::broker()->reset([
            'email' => $admin->email,
            'password' => $password,
            'password_confirmation' => $password,
            'token' => $token,
        ], static fn (): null => null);

        $this->assertSame(Password::INVALID_TOKEN, $secondStatus);
    }

    public function test_remove_soft_deletes_admin_and_preserves_audit_history(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'remove@example.com');

        app(AdminManager::class)->remove($owner, $admin);

        $this->assertSoftDeleted('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('admin_activity_logs', [
            'target_user_id' => $admin->id,
            'action' => 'admin_removed',
        ]);
        $this->assertNotNull(AdminActivityLog::where('target_user_id', $admin->id)->first()?->targetUser);
    }

    public function test_only_owner_sees_admin_management_navigation(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'navigation@example.com');

        $this->actingAs($owner);
        $this->assertTrue(AdminResource::canViewAny());
        $this->assertTrue(AdminResource::shouldRegisterNavigation());

        $this->actingAs($admin);
        $this->assertFalse(AdminResource::canViewAny());
        $this->assertFalse(AdminResource::shouldRegisterNavigation());
    }

    public function test_user_admin_can_use_operational_panel_but_not_admin_management(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'operations-panel@example.com');

        $this->actingAs($admin)
            ->withSession(['admin_session_version' => $admin->session_version])
            ->get('/admin')
            ->assertOk();

        $this->get('/admin/admins')->assertForbidden();
    }

    public function test_successful_login_records_last_login_and_audit_event(): void
    {
        $owner = $this->owner();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $owner->email,
                'password' => 'Owner-Password-2026!',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors()
            ->assertSessionHas('admin_session_version', $owner->session_version);

        $this->assertNotNull($owner->fresh()->last_login_at);
        $this->assertDatabaseHas('admin_activity_logs', [
            'target_user_id' => $owner->id,
            'action' => 'login_success',
        ]);
    }

    public function test_inactive_admin_cannot_log_in_and_failure_is_audited(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'inactive-login@example.com');
        $admin->update(['password' => 'User-Password-2026!']);
        app(AdminManager::class)->deactivate($owner, $admin);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $admin->email,
                'password' => 'User-Password-2026!',
                'remember' => false,
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
        $this->assertDatabaseHas('admin_activity_logs', [
            'target_user_id' => $admin->id,
            'action' => 'login_failed',
        ]);
    }

    public function test_forgot_password_uses_same_generic_response_for_known_and_unknown_email(): void
    {
        Notification::fake();
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'forgot@example.com');

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $admin->email])
            ->call('request')
            ->assertNotified('Check your email');

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => 'unknown@example.com'])
            ->call('request')
            ->assertNotified('Check your email');

        Notification::assertSentTo($admin, ResetPasswordNotification::class);
        Notification::assertNotSentTo($owner, ResetPasswordNotification::class);
        $this->assertDatabaseHas('admin_activity_logs', [
            'target_user_id' => $admin->id,
            'action' => 'password_reset_requested',
        ]);
        $this->assertSame(
            1,
            AdminActivityLog::query()->where('action', 'password_reset_requested')->count(),
        );
    }

    public function test_password_reset_notification_is_sent_without_waiting_for_the_queue(): void
    {
        Notification::fake();
        Queue::fake();

        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'immediate-reset@example.com');

        $admin->sendPasswordResetNotification('reset-token');

        Notification::assertSentTo($admin, ResetPasswordNotification::class);
        Queue::assertNothingPushed();
    }

    public function test_expired_password_reset_token_is_rejected(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin($owner, 'expired@example.com');
        $token = Password::broker()->createToken($admin);

        DB::table('password_reset_tokens')
            ->where('email', $admin->email)
            ->update(['created_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1)]);

        $status = Password::broker()->reset([
            'email' => $admin->email,
            'password' => 'Another-Strong-Password-2026!',
            'password_confirmation' => 'Another-Strong-Password-2026!',
            'token' => $token,
        ], static fn (): null => null);

        $this->assertSame(Password::INVALID_TOKEN, $status);
    }

    private function owner(): User
    {
        $panelPermission = Permission::findOrCreate(AdminAccess::PANEL_PERMISSION);
        $managePermission = Permission::findOrCreate(AdminAccess::MANAGE_ADMINS_PERMISSION);
        $activityPermission = Permission::findOrCreate(AdminAccess::VIEW_ACTIVITY_PERMISSION);
        $siteSettingsPermission = Permission::findOrCreate(AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION);
        $role = Role::findOrCreate(AdminAccess::OWNER_ROLE);
        $role->syncPermissions([$panelPermission, $managePermission, $activityPermission, $siteSettingsPermission]);

        $owner = User::query()->create([
            'name' => 'Primary Owner',
            'email' => 'owner@example.com',
            'password' => 'Owner-Password-2026!',
        ]);
        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);
        $owner->refresh()->assignRole($role);

        return $owner->refresh();
    }

    private function userAdmin(User $owner, string $email): User
    {
        $panelPermission = Permission::findOrCreate(AdminAccess::PANEL_PERMISSION);
        Role::findOrCreate(AdminAccess::USER_ADMIN_ROLE)->syncPermissions([$panelPermission]);

        return app(AdminManager::class)->createUserAdmin($owner, [
            'name' => 'User Admin',
            'email' => $email,
            'phone' => '+8801700000000',
        ]);
    }
}
