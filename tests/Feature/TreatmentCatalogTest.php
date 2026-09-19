<?php

namespace Tests\Feature;

use App\Filament\Resources\TreatmentResource\Pages\CreateTreatment;
use App\Filament\Resources\TreatmentResource\Pages\EditTreatment;
use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Hospital;
use App\Models\Treatment;
use App\Models\TreatmentCost;
use App\Models\User;
use App\Support\AdminAccess;
use Database\Seeders\TreatmentCatalogSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TreatmentCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogue_installs_all_topics_with_original_images_and_no_invented_prices(): void
    {
        $this->seed(TreatmentCatalogSeeder::class);
        $this->assertDatabaseCount('treatments', 56);
        $this->assertDatabaseCount('departments', 16);
        $this->assertDatabaseCount('treatment_costs', 0);
        $this->assertDatabaseHas('settings', ['key' => TreatmentCatalogSeeder::INSTALLATION_KEY]);
        foreach (Treatment::with('department')->get() as $treatment) {
            $this->assertNotEmpty($treatment->name_bn);
            $this->assertNotEmpty($treatment->description_bn);
            $this->assertNotEmpty($treatment->department->name_bn);
            $this->assertFileExists(public_path('images/treatments/'.$treatment->illustration.'.webp'));
        }
    }

    public function test_import_reuses_existing_records_and_preserves_edits_costs_and_deletions(): void
    {
        $department = Department::create(['name' => 'Orthopedic', 'slug' => 'orthopedics']);
        $existing = Treatment::create([
            'department_id' => $department->id, 'name' => 'Knee Replacement', 'slug' => 'knee-replacement',
            'description' => 'Hospital-approved copy.', 'name_bn' => 'নিজস্ব শিরোনাম',
            'image_path' => 'treatments/custom.webp', 'sort_order' => 7,
        ]);
        $cost = $this->createCost($existing);
        $this->seed(TreatmentCatalogSeeder::class);
        $this->assertDatabaseCount('treatments', 56);
        $this->assertSame($department->id, $existing->fresh()->department_id);
        $this->assertSame('Hospital-approved copy.', $existing->fresh()->description);
        $this->assertSame('নিজস্ব শিরোনাম', $existing->fresh()->name_bn);
        $this->assertSame('treatments/custom.webp', $existing->fresh()->image_path);
        $this->assertSame(7, $existing->fresh()->sort_order);
        $this->assertSame($existing->id, $cost->fresh()->treatment_id);
        $existing->update(['description_bn' => 'সম্পাদিত লেখা']);
        $deleted = Treatment::where('slug', 'hip-replacement')->firstOrFail();
        $deleted->delete();
        $this->seed(TreatmentCatalogSeeder::class);
        $this->assertDatabaseCount('treatments', 55);
        $this->assertSame('সম্পাদিত লেখা', $existing->fresh()->description_bn);
        $this->assertDatabaseMissing('treatments', ['id' => $deleted->id]);
    }

    public function test_catalogue_search_is_bilingual_and_combines_with_specialty_filters(): void
    {
        $this->seed(TreatmentCatalogSeeder::class);
        $this->withSession(['locale' => 'bn'])->get('/treatments?q='.urlencode('হাঁটু'))
            ->assertOk()->assertSee('হাঁটু')->assertViewHas('treatments', fn ($rows) => $rows->total() > 0);
        $this->get('/treatments?q=Knee&department=orthopedic')->assertOk()
            ->assertViewHas('treatments', fn ($rows) => $rows->total() > 0 && $rows->every(fn ($row) => $row->department->slug === 'orthopedic'));
        $this->get('/treatments?q=Knee&department=cardiac')->assertOk()
            ->assertViewHas('treatments', fn ($rows) => $rows->isEmpty());
        $this->get('/treatments?department=not-a-specialty')->assertOk()
            ->assertViewHas('treatments', fn ($rows) => $rows->isEmpty());
        $this->withSession(['locale' => 'en'])->get('/treatments?page=2')->assertOk()
            ->assertViewHas('treatments', fn ($rows) => $rows->currentPage() === 2 && $rows->count() === 24);
        $this->get('/treatments?q='.str_repeat('x', 121))->assertSessionHasErrors('q');
        $this->get('/treatments?q[]=invalid')->assertSessionHasErrors('q');
    }

    public function test_detail_displays_saved_cost_or_honest_empty_state_and_inquiry_context(): void
    {
        $this->seed(TreatmentCatalogSeeder::class);
        $treatment = Treatment::firstOrFail();
        $this->withSession(['locale' => 'en'])->get(route('treatments.show', $treatment))
            ->assertOk()->assertSee('An individual hospital estimate is needed')
            ->assertSee('name="treatment_id" value="'.$treatment->id.'"', false);
        $this->createCost($treatment);
        $this->get(route('treatments.show', $treatment))->assertOk()->assertSee('INR 12,000 - 18,000')
            ->assertDontSee('An individual hospital estimate is needed');
    }

    public function test_sitemap_and_global_search_include_treatments(): void
    {
        $this->seed(TreatmentCatalogSeeder::class);
        $treatment = Treatment::firstOrFail();
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('treatments.show', $treatment), false);
        $this->get('/search?q='.urlencode($treatment->name_bn))->assertRedirect(route('treatments.show', $treatment));
    }

    public function test_owner_can_create_and_edit_bilingual_treatment_with_custom_image(): void
    {
        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::findOrCreate(AdminAccess::OWNER_ROLE);
        $role->syncPermissions([
            Permission::findOrCreate(AdminAccess::PANEL_PERMISSION),
            Permission::findOrCreate(AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION),
        ]);
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'Testing-2026!']);
        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);
        $owner->refresh()->assignRole($role);
        $this->actingAs($owner)->withSession(['admin_session_version' => $owner->session_version]);
        $department = Department::create(['name' => 'Cardiac Care', 'slug' => 'cardiac']);
        Livewire::test(CreateTreatment::class)->fillForm([
            'name' => 'New consultation', 'name_bn' => 'নতুন পরামর্শ', 'slug' => 'new-consultation',
            'department_id' => $department->id, 'description' => 'Appointment coordination.',
            'description_bn' => 'অ্যাপয়েন্টমেন্ট সমন্বয়।', 'illustration' => 'heart',
            'image_path' => UploadedFile::fake()->image('custom.png'), 'sort_order' => 4,
        ])->call('create')->assertHasNoFormErrors();
        $treatment = Treatment::where('slug', 'new-consultation')->firstOrFail();
        Storage::disk('public')->assertExists($treatment->image_path);
        $this->assertSame(Storage::disk('public')->url($treatment->image_path), $treatment->imageUrl());
        Livewire::test(EditTreatment::class, ['record' => $treatment->slug])
            ->fillForm(['name_bn' => 'পরিমার্জিত পরামর্শ'])->call('save')->assertHasNoFormErrors();
        $this->assertSame('পরিমার্জিত পরামর্শ', $treatment->fresh()->name_bn);
    }

    private function createCost(Treatment $treatment): TreatmentCost
    {
        $country = Country::firstOrCreate(['slug' => 'india'], ['name' => 'India']);
        $city = City::firstOrCreate(['slug' => 'chennai'], ['country_id' => $country->id, 'name' => 'Chennai']);
        $hospital = Hospital::firstOrCreate(['slug' => 'test-hospital'], [
            'name' => 'Test Hospital', 'country_id' => $country->id, 'city_id' => $city->id,
        ]);

        return TreatmentCost::create([
            'treatment_id' => $treatment->id, 'hospital_id' => $hospital->id,
            'currency' => 'INR', 'cost_min' => 12000, 'cost_max' => 18000,
        ]);
    }
}
