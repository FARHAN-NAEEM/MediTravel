<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactChannelResource;
use App\Filament\Resources\OfficeLocationResource;
use App\Filament\Resources\SocialLinkResource;
use App\Models\ContactChannel;
use App\Models\OfficeLocation;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\SiteContactService;
use App\Support\AdminAccess;
use Database\Seeders\SiteContactSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteContactSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_only_owner_can_see_or_open_site_settings_resources(): void
    {
        $owner = $this->owner();
        $admin = $this->userAdmin();

        $this->actingAs($owner);
        $this->assertTrue(ContactChannelResource::shouldRegisterNavigation());
        $this->assertTrue(OfficeLocationResource::shouldRegisterNavigation());
        $this->assertTrue(SocialLinkResource::shouldRegisterNavigation());

        $this->assertTrue($admin->can(AdminAccess::PANEL_PERMISSION));
        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));

        $this->actingAs($admin)
            ->withSession(['admin_session_version' => $admin->session_version])
            ->get('/admin/contact-channels')
            ->assertForbidden();

        $this->get('/admin/office-locations')->assertForbidden();
        $this->get('/admin/social-links')->assertForbidden();
        $this->assertFalse(ContactChannelResource::shouldRegisterNavigation());
    }

    public function test_primary_channel_is_unique_per_type_and_drives_whatsapp_redirect(): void
    {
        $first = ContactChannel::query()->create([
            'type' => 'whatsapp',
            'label' => 'Old WhatsApp',
            'value' => '+880 1700-000001',
            'is_primary' => true,
            'is_active' => true,
            'sort_order' => 20,
        ]);
        $second = ContactChannel::query()->create([
            'type' => 'whatsapp',
            'label' => 'Patient Support',
            'value' => '+880 1900-000002',
            'is_primary' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame('8801900000002', app(SiteContactService::class)->primaryWhatsappNumber());

        $response = $this->post('/inquiries', [
            'name' => 'Footer Test Patient',
            'phone' => '+8801700000000',
            'type' => 'appointment',
        ]);

        $this->assertStringStartsWith(
            'https://wa.me/8801900000002?text=',
            (string) $response->headers->get('Location'),
        );
    }

    public function test_footer_shows_only_active_channels_named_social_links_and_eight_offices(): void
    {
        ContactChannel::query()->create([
            'type' => 'phone',
            'label' => 'Visa Help',
            'value' => '+8801700000011',
            'is_primary' => true,
            'is_active' => true,
            'sort_order' => 10,
        ]);
        ContactChannel::query()->create([
            'type' => 'email',
            'label' => 'Hidden Email',
            'value' => 'hidden@example.com',
            'is_primary' => false,
            'is_active' => false,
            'sort_order' => 20,
        ]);
        SocialLink::query()->create([
            'platform' => 'facebook',
            'name' => 'Asian Health Connect Facebook',
            'url' => 'https://facebook.com/asian-health-connect-test',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        foreach (range(1, 8) as $index) {
            OfficeLocation::query()->create([
                'name' => "District Office {$index}",
                'district' => "District {$index}",
                'address' => "Medical Road {$index}, Bangladesh",
                'is_active' => true,
                'sort_order' => $index * 10,
            ]);
        }

        OfficeLocation::query()->create([
            'name' => 'Hidden Office',
            'district' => 'Hidden',
            'address' => 'Not public',
            'is_active' => false,
            'sort_order' => 100,
        ]);

        $response = $this->get('/')->assertOk();
        $response->assertSee('Visa Help');
        $response->assertSee('Asian Health Connect Facebook');
        $response->assertSee('href="https://facebook.com/asian-health-connect-test"', false);
        $response->assertDontSee('hidden@example.com');
        $response->assertDontSee('Hidden Office');

        foreach (range(1, 8) as $index) {
            $response->assertSee("District Office {$index}");
        }

        $this->assertDoesNotMatchRegularExpression(
            '/>\s*https:\/\/facebook\.com\/asian-health-connect-test\s*</',
            (string) $response->getContent(),
        );
    }

    public function test_contact_seeder_supplies_three_offices_and_each_core_channel(): void
    {
        $this->seed(SiteContactSeeder::class);

        $this->assertDatabaseCount('office_locations', 3);
        $this->assertDatabaseHas('contact_channels', ['type' => 'phone', 'is_active' => true]);
        $this->assertDatabaseHas('contact_channels', ['type' => 'whatsapp', 'is_active' => true]);
        $this->assertDatabaseHas('contact_channels', ['type' => 'email', 'is_active' => true]);
        $this->assertDatabaseHas('social_links', ['platform' => 'facebook']);
    }

    private function owner(): User
    {
        $permissions = collect([
            AdminAccess::PANEL_PERMISSION,
            AdminAccess::MANAGE_ADMINS_PERMISSION,
            AdminAccess::VIEW_ACTIVITY_PERMISSION,
            AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION,
        ])->map(fn (string $permission) => Permission::findOrCreate($permission));
        $role = Role::findOrCreate(AdminAccess::OWNER_ROLE);
        $role->syncPermissions($permissions);

        $owner = User::query()->create([
            'name' => 'Primary Owner',
            'email' => 'owner-settings@example.com',
            'password' => 'Owner-Password-2026!',
        ]);
        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);
        $owner->refresh()->assignRole($role);

        return $owner->refresh();
    }

    private function userAdmin(): User
    {
        $panelPermission = Permission::findOrCreate(AdminAccess::PANEL_PERMISSION);
        $role = Role::findOrCreate(AdminAccess::USER_ADMIN_ROLE);
        $role->syncPermissions([$panelPermission]);

        $admin = User::query()->create([
            'name' => 'User Admin',
            'email' => 'user-settings@example.com',
            'password' => 'User-Password-2026!',
        ]);
        $admin->assignRole($role);

        return $admin->refresh();
    }
}
