<?php

namespace Tests\Feature;

use App\Filament\Resources\HospitalGroupResource\Pages\ManageHospitalGroups;
use App\Filament\Resources\HospitalResource\Pages\CreateHospital;
use App\Filament\Resources\HospitalResource\Pages\EditHospital;
use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use App\Models\HospitalGroup;
use App\Models\User;
use App\Support\AdminAccess;
use Database\Seeders\HospitalDirectorySeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HospitalDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_installs_verified_entries_and_excludes_pending_names(): void
    {
        $this->seed(HospitalDirectorySeeder::class);
        $this->assertDatabaseCount('hospitals', 44);
        $this->assertDatabaseCount('hospital_groups', 35);
        $this->assertDatabaseCount('countries', 3);
        $manvi = Hospital::whereHas('group', fn ($q) => $q->where('slug', 'manvi'))->firstOrFail();
        $this->assertSame('Vijayawada', $manvi->city->name);
        $this->assertSame('https://manvihospitals.com/', $manvi->source_url);
        $this->assertFalse(Hospital::where('name', 'like', '%Max%')->whereHas('city', fn ($q) => $q->where('name', 'Bengaluru'))->exists());
        $this->assertDatabaseMissing('hospitals', ['name' => 'Kolkata Fertility Centre']);
        $this->assertSame(1, Hospital::whereHas('group', fn ($q) => $q->where('slug', 'hcg'))->count());
        $this->assertSame(1, Hospital::whereHas('group', fn ($q) => $q->where('slug', 'sri-ramachandra'))->count());
        foreach (HospitalGroup::whereNotNull('catalog_logo')->get() as $group) {
            $this->assertFileExists(public_path($group->catalog_logo));
            $this->assertNotNull($group->logoUrl());
        }
        $this->seed(HospitalDirectorySeeder::class);
        $this->assertDatabaseCount('hospitals', 44);
    }

    public function test_existing_urls_content_and_city_aliases_are_preserved(): void
    {
        $country = Country::create(['name' => 'India', 'slug' => 'india']);
        $city = City::create(['name' => 'Bangalore', 'slug' => 'bangalore-india', 'country_id' => $country->id]);
        $existing = Hospital::create([
            'name' => 'Apollo Hospitals, Bannerghatta Road, Bangalore',
            'slug' => 'apollo-hospitals-bannerghatta-road-bangalore-bangalore',
            'city_id' => $city->id, 'country_id' => $country->id,
            'description' => 'Administrator authored content', 'logo' => 'custom.png',
            'is_featured' => true, 'sort_order' => 2,
        ]);
        $this->seed(HospitalDirectorySeeder::class);
        $this->assertDatabaseCount('hospitals', 44);
        $this->assertDatabaseMissing('cities', ['name' => 'Bengaluru']);
        $this->assertSame('apollo', $existing->fresh()->group->slug);
        $this->assertSame($city->id, $existing->fresh()->city_id);
        $this->assertSame('Administrator authored content', $existing->fresh()->description);
        $this->assertSame('custom.png', $existing->fresh()->logo);
        $this->assertSame(2, $existing->fresh()->sort_order);
        $existing->update(['name' => 'Administrator edited name']);
        Hospital::whereHas('group', fn ($q) => $q->where('slug', 'manvi'))->delete();
        $this->seed(HospitalDirectorySeeder::class);
        $this->assertDatabaseCount('hospitals', 43);
        $this->assertSame('Administrator edited name', $existing->fresh()->name);
        $this->assertDatabaseMissing('hospitals', ['slug' => 'manvi-hospitals-vijayawada']);
    }

    public function test_filters_combine_search_country_city_group_and_care_without_leaking_results(): void
    {
        $this->seed(HospitalDirectorySeeder::class);
        $this->get('/hospitals?q=Chennai&group=nova&country=india&city=chennai-india&care=fertility')->assertOk()
            ->assertViewHas('hospitals', fn ($rows) => $rows->total() === 1 && $rows->first()->group->slug === 'nova');
        $this->get('/hospitals?q=Apollo&group=fortis')->assertOk()
            ->assertViewHas('hospitals', fn ($rows) => $rows->isEmpty());
        $this->get('/hospitals?country=thailand&city=chennai-india')->assertOk()
            ->assertViewHas('hospitals', fn ($rows) => $rows->isEmpty());
        $this->get('/hospitals?country=india&page=2')->assertOk()
            ->assertViewHas('hospitals', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 12 && str_contains($rows->nextPageUrl(), 'country=india'));
        $this->get('/hospitals?q[]=invalid')->assertSessionHasErrors('q');
        $this->get('/hospitals?group[]=invalid')->assertSessionHasErrors('group');
        $this->get('/hospitals?care=invalid')->assertSessionHasErrors('care');
        $this->get('/hospitals?care[]=fertility')->assertSessionHasErrors('care');
    }

    public function test_cards_detail_sitemap_and_inquiry_use_saved_hospital_context(): void
    {
        $this->seed(HospitalDirectorySeeder::class);
        $hospital = Hospital::whereHas('group', fn ($q) => $q->where('slug', 'manvi'))->firstOrFail();
        $this->withSession(['locale' => 'en'])->get('/hospitals?group=manvi')->assertOk()
            ->assertSee($hospital->logoUrl(), false)->assertSee('Vijayawada');
        $this->get(route('hospitals.show', $hospital))->assertOk()
            ->assertSee('name="hospital_id" value="'.$hospital->id.'"', false)
            ->assertSee($hospital->source_url, false)->assertSee(rawurlencode($hospital->name), false);
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('hospitals.show', $hospital), false);
        $this->get('/')->assertOk()->assertViewHas('featuredHospitals', fn ($rows) => $rows->count() === 4);
        $hospital->update(['logo' => 'hospitals/logos/custom.png', 'images' => ['hospitals/photos/custom.jpg']]);
        $this->assertSame(Storage::disk('public')->url($hospital->logo), $hospital->fresh()->logoUrl());
        $this->assertSame(Storage::disk('public')->url($hospital->images[0]), $hospital->fresh()->photoUrl());
    }

    public function test_admin_can_create_and_edit_hospital_group_and_uploaded_images(): void
    {
        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::findOrCreate(AdminAccess::OWNER_ROLE);
        $role->syncPermissions([Permission::findOrCreate(AdminAccess::PANEL_PERMISSION), Permission::findOrCreate(AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION)]);
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'Testing-2026!']);
        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);
        $owner->refresh()->assignRole($role);
        $this->actingAs($owner)->withSession(['admin_session_version' => $owner->session_version]);
        $country = Country::create(['name' => 'India', 'slug' => 'india']);
        $city = City::create(['name' => 'Chennai', 'slug' => 'chennai', 'country_id' => $country->id]);
        Livewire::test(ManageHospitalGroups::class)->callAction('create', data: [
            'name' => 'Test Group', 'slug' => 'test-group', 'sort_order' => 1,
            'website' => 'https://example.org/', 'logo' => UploadedFile::fake()->image('group.png'),
        ])->assertHasNoActionErrors();
        $group = HospitalGroup::where('slug', 'test-group')->firstOrFail();
        Storage::disk('public')->assertExists($group->logo);
        Livewire::test(ManageHospitalGroups::class)->callTableAction('edit', $group, data: ['name' => 'Updated Group'])
            ->assertHasNoTableActionErrors();
        $this->assertSame('Updated Group', $group->fresh()->name);
        Livewire::test(CreateHospital::class)->fillForm([
            'name' => 'Test Hospital', 'slug' => 'test-hospital', 'country_id' => $country->id,
            'city_id' => $city->id, 'hospital_group_id' => $group->id, 'care_type' => 'fertility',
            'source_url' => 'https://example.org/', 'logo' => UploadedFile::fake()->image('logo.png'),
            'images' => [UploadedFile::fake()->image('building.jpg')],
        ])->call('create')->assertHasNoFormErrors();
        $hospital = Hospital::where('slug', 'test-hospital')->firstOrFail();
        Storage::disk('public')->assertExists($hospital->logo);
        Storage::disk('public')->assertExists($hospital->images[0]);
        $this->assertSame($group->id, $hospital->hospital_group_id);
        Livewire::test(EditHospital::class, ['record' => $hospital->slug])
            ->fillForm(['name' => 'Updated Hospital'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('Updated Hospital', $hospital->fresh()->name);
    }
}
