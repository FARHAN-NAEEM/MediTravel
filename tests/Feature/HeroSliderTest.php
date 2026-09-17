<?php

namespace Tests\Feature;

use App\Filament\Resources\HeroImageResource\Pages\CreateHeroImage;
use App\Filament\Resources\HeroImageResource\Pages\EditHeroImage;
use App\Filament\Resources\HeroImageResource\Pages\ListHeroImages;
use App\Models\HeroImage;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminAccess;
use Database\Seeders\HeroSliderSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeroSliderTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_ordered_active_slides_and_escapes_their_content(): void
    {
        HeroImage::create(['image_path' => 'second.jpg', 'heading_en' => 'Second slide', 'sort_order' => 2]);
        HeroImage::create(['image_path' => 'first.jpg', 'heading_en' => 'First slide', 'body_en' => '<script>alert(1)</script>', 'sort_order' => 1]);
        HeroImage::create(['image_path' => 'hidden.jpg', 'heading_en' => 'Hidden slide', 'is_active' => false]);
        Setting::create(['key' => 'hero_slide_interval', 'value' => '3']);

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['First slide', 'Second slide'])
            ->assertDontSee('Hidden slide')
            ->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('heroSlider(2, 3000)', false)
            ->assertSee('hero-slider__controls', false);
    }

    public function test_zero_and_one_slides_have_no_playback_controls(): void
    {
        $this->get('/')->assertOk()->assertDontSee('hero-slider__controls', false);
        HeroImage::create(['image_path' => 'legacy.jpg', 'title' => 'Legacy image']);
        $this->get('/')->assertOk()->assertSee('legacy.jpg')->assertDontSee('hero-slider__controls', false);
    }

    public function test_sample_slides_are_editable_and_seeding_preserves_existing_slides(): void
    {
        Storage::fake('public');
        $this->seed(HeroSliderSeeder::class);
        $this->assertDatabaseCount('hero_images', 2);
        $slide = HeroImage::firstOrFail();
        Storage::disk('public')->assertExists($slide->image_path);
        $slide->update(['heading_en' => 'Admin edited heading']);
        $this->seed(HeroSliderSeeder::class);
        $this->assertDatabaseCount('hero_images', 2);
        $this->assertSame('Admin edited heading', $slide->fresh()->heading_en);
    }

    public function test_more_than_five_slides_can_be_displayed(): void
    {
        foreach (range(1, 7) as $index) {
            HeroImage::create(['image_path' => 'slide-'.$index.'.jpg', 'heading_en' => 'Destination '.$index, 'sort_order' => $index]);
        }

        $this->get('/')->assertOk()->assertSee('heroSlider(7, 5000)', false)->assertSee('Destination 7');
    }

    public function test_language_fallback_and_invalid_saved_interval_are_safe(): void
    {
        $slide = new HeroImage(['heading_bn' => 'বাংলা শিরোনাম', 'heading_en' => 'English heading', 'body_bn' => 'বাংলা বিবরণ']);
        app()->setLocale('bn');
        $this->assertSame('বাংলা শিরোনাম', $slide->localizedContent('heading'));
        app()->setLocale('en');
        $this->assertSame('English heading', $slide->localizedContent('heading'));
        $this->assertSame('বাংলা বিবরণ', $slide->localizedContent('body'));
        Setting::create(['key' => 'hero_slide_interval', 'value' => '0']);
        $this->assertSame(5, HeroImage::slideInterval());
    }

    public function test_admin_can_upload_edit_reorder_and_change_timing(): void
    {
        $this->signInAdmin();
        Storage::fake('public');
        Livewire::test(CreateHeroImage::class)->fillForm([
            'title' => 'Doctor introduction',
            'image_path' => UploadedFile::fake()->image('doctor.jpg'),
            'heading_en' => 'Meet your specialist',
            'body_en' => 'Appointment support for your next visit.',
            'image_fit' => 'contain',
            'sort_order' => 0,
            'is_active' => true,
        ])->call('create')->assertHasNoFormErrors();

        $slide = HeroImage::firstOrFail();
        Storage::disk('public')->assertExists($slide->image_path);
        $this->assertSame('Meet your specialist', $slide->heading_en);

        Livewire::test(EditHeroImage::class, ['record' => $slide->getRouteKey()])
            ->fillForm(['heading_en' => 'Updated specialist', 'is_active' => false])
            ->call('save')->assertHasNoFormErrors();
        $this->assertFalse($slide->fresh()->is_active);

        $other = HeroImage::create(['image_path' => 'other.jpg', 'sort_order' => 2]);
        Livewire::test(ListHeroImages::class)
            ->call('reorderTable', [$other->id, $slide->id])
            ->callAction('sliderSettings', data: ['interval' => 3])
            ->assertHasNoActionErrors();
        $this->assertLessThan($slide->fresh()->sort_order, $other->fresh()->sort_order);
        $this->assertSame(3, HeroImage::slideInterval());

        Livewire::test(ListHeroImages::class)
            ->callAction('sliderSettings', data: ['interval' => 1])
            ->assertHasActionErrors(['interval']);
        $this->assertSame(3, HeroImage::slideInterval());
    }

    public function test_empty_content_is_rejected_and_guests_cannot_manage_slides(): void
    {
        $this->get('/admin/hero-images')->assertRedirect('/admin/login');
        $this->signInAdmin();
        Livewire::test(CreateHeroImage::class)->fillForm([
            'heading_bn' => '', 'heading_en' => '', 'body_bn' => '', 'body_en' => '',
        ])->call('create')->assertHasFormErrors(['image_path', 'heading_bn', 'heading_en', 'body_bn', 'body_en']);
    }

    private function signInAdmin(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::findOrCreate(AdminAccess::USER_ADMIN_ROLE);
        $role->givePermissionTo(Permission::findOrCreate(AdminAccess::PANEL_PERMISSION));
        $admin = User::create(['name' => 'Slider Admin', 'email' => 'slider@example.test', 'password' => 'Test-Password-2026!']);
        $admin->assignRole($role);
        $this->actingAs($admin)->withSession(['admin_session_version' => $admin->session_version]);
    }
}
