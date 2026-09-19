<?php

namespace Tests\Feature;

use App\Filament\Resources\BlogCategoryResource\Pages\ManageBlogCategories;
use App\Filament\Resources\BlogPostResource\Pages\ManageBlogPosts;
use App\Filament\Resources\FaqResource\Pages\ManageFaqs;
use App\Filament\Resources\ReviewResource\Pages\ManageReviews;
use App\Filament\Resources\ServiceResource\Pages\ManageServices;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\City;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Hospital;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Support\AdminAccess;
use Database\Seeders\BanglaContentSeeder;
use Database\Seeders\SiteContactSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_localizes_lists_details_metadata_and_cached_footer(): void
    {
        $translations = $this->createExistingContent();
        $this->seed(SiteContactSeeder::class);
        // Warm the footer cache before translations are installed.
        $this->get('/services')->assertOk()->assertSee('Main Office');
        $this->seed(BanglaContentSeeder::class);

        $pages = [
            '/services' => ['services' => ['name', 'short_desc']],
            '/services/appointment' => ['services' => ['body']],
            '/reviews' => ['reviews' => ['patient_name', 'body', 'treatment'], 'hospitals' => ['name']],
            '/blog' => ['blog_categories' => ['name'], 'blog_posts' => ['title', 'meta_description']],
            '/blog/visa-checklist' => ['blog_categories' => ['name'], 'blog_posts' => ['title', 'body', 'meta_title', 'meta_description']],
            '/faq' => ['faqs' => ['question', 'answer']],
        ];

        foreach (['bn', 'en', 'bn'] as $locale) {
            $this->from('/services')->get('/language/'.$locale)
                ->assertRedirect('/services')->assertSessionHas('locale', $locale);

            foreach ($pages as $url => $tables) {
                $response = $this->get($url)->assertOk()->assertSee('lang="'.$locale.'"', false);
                foreach ($tables as $table => $fields) {
                    foreach ($fields as $field) {
                        foreach ($translations[$table][$field] as $english => $bangla) {
                            $response->assertSee($locale === 'bn' ? $bangla : $english);
                            $response->assertDontSee($locale === 'bn' ? $english : $bangla);
                        }
                    }
                }

                $response->assertSee($locale === 'bn' ? 'প্রধান কার্যালয়' : 'Main Office');
                $response->assertSee($locale === 'bn' ? 'চট্টগ্রাম কার্যালয়' : 'Chattogram Office');
                $response->assertSee($locale === 'bn' ? 'ঢাকা, বাংলাদেশ' : 'Dhaka, Bangladesh');
                $response->assertSee($locale === 'bn' ? 'সাধারণ জিজ্ঞাসা' : 'FAQ');
            }
        }
    }

    public function test_translation_backfill_preserves_originals_custom_content_and_admin_edits(): void
    {
        $this->createExistingContent();
        $original = Service::firstOrFail();
        $originalAttributes = $original->getAttributes();
        $custom = Service::create(['name' => 'Custom care', 'slug' => 'custom-care', 'body' => 'Written by owner']);
        $this->seed(BanglaContentSeeder::class);
        $original->refresh()->update(['name_bn' => 'আমাদের নিজস্ব শিরোনাম']);
        $this->seed(BanglaContentSeeder::class);

        $this->assertSame('আমাদের নিজস্ব শিরোনাম', $original->fresh()->name_bn);
        foreach ($originalAttributes as $field => $value) {
            if (! str_ends_with($field, '_bn') && $field !== 'updated_at') {
                $this->assertEquals($value, $original->fresh()->getAttributes()[$field]);
            }
        }
        $this->assertNull($custom->fresh()->name_bn);
        $this->assertSame('Written by owner', $custom->fresh()->body);
        $this->assertDatabaseCount('services', 10);
    }

    public function test_missing_translations_fall_back_without_exposing_html(): void
    {
        $service = Service::create([
            'slug' => 'safe-content', 'name' => 'Original name', 'name_bn' => '   ',
            'short_desc' => 'Original summary', 'body' => 'Original body',
            'body_bn' => '<script>alert("test")</script>',
        ]);
        app()->setLocale('bn');
        $this->assertSame('Original name', $service->localized('name'));
        $this->assertNull($service->localized('icon'));
        $this->withSession(['locale' => 'bn'])->get('/services/safe-content')->assertOk()
            ->assertSee('Original name')->assertSee($service->body_bn)
            ->assertDontSee($service->body_bn, false);
        $this->withSession(['locale' => 'en'])->get('/services/safe-content')->assertOk()
            ->assertSee('Original body')->assertDontSee($service->body_bn);
    }

    public function test_unpublished_reviews_and_posts_remain_hidden_in_both_languages(): void
    {
        Review::create(['patient_name' => 'Hidden patient', 'patient_name_bn' => 'গোপন রোগী', 'body' => 'Private review', 'is_published' => false]);
        BlogPost::create(['title' => 'Draft post', 'title_bn' => 'খসড়া লেখা', 'slug' => 'draft', 'body' => 'Private post']);
        foreach (['bn', 'en'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/reviews')->assertOk()
                ->assertDontSee('Hidden patient')->assertDontSee('গোপন রোগী');
            $this->get('/blog')->assertOk()->assertDontSee('Draft post')->assertDontSee('খসড়া লেখা');
        }
    }

    public function test_owner_can_edit_all_bilingual_content_and_both_languages_are_required(): void
    {
        $this->createExistingContent();
        $this->seed(BanglaContentSeeder::class);
        $this->signInOwner();

        foreach ([
            [ManageServices::class, Service::firstOrFail(), 'name'],
            [ManageReviews::class, Review::firstOrFail(), 'patient_name'],
            [ManageBlogCategories::class, BlogCategory::firstOrFail(), 'name'],
            [ManageBlogPosts::class, BlogPost::firstOrFail(), 'title'],
            [ManageFaqs::class, Faq::firstOrFail(), 'question'],
        ] as [$page, $record, $field]) {
            $english = $record->getAttribute($field);
            Livewire::test($page)->callTableAction('edit', $record, data: [$field.'_bn' => 'নতুন বাংলা লেখা'])
                ->assertHasNoTableActionErrors();
            $this->assertSame('নতুন বাংলা লেখা', $record->fresh()->getAttribute($field.'_bn'));
            $this->assertSame($english, $record->fresh()->getAttribute($field));
            Livewire::test($page)->callTableAction('edit', $record, data: [$field.'_bn' => ''])
                ->assertHasTableActionErrors([$field.'_bn' => 'required']);
        }

        Livewire::test(ManageFaqs::class)->callAction('create', data: [
            'question' => 'A new question?', 'question_bn' => 'নতুন প্রশ্ন?',
            'answer' => 'A new answer.', 'answer_bn' => 'নতুন উত্তর।',
            'category' => 'general', 'sort_order' => 10,
        ])->assertHasNoActionErrors();
        $this->withSession(['locale' => 'bn'])->get('/faq')->assertOk()->assertSee('নতুন উত্তর।');
        $this->withSession(['locale' => 'en'])->get('/faq')->assertOk()->assertSee('A new answer.');
    }

    public function test_guests_and_non_owner_admins_cannot_manage_site_content(): void
    {
        $paths = ['/admin/services', '/admin/reviews', '/admin/blog-categories', '/admin/blog-posts', '/admin/faqs'];
        foreach ($paths as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
        $role = Role::findOrCreate(AdminAccess::USER_ADMIN_ROLE);
        $role->givePermissionTo(Permission::findOrCreate(AdminAccess::PANEL_PERMISSION));
        $admin = User::create(['name' => 'Content Admin', 'email' => 'content-admin@example.test', 'password' => 'Test-Password-2026!']);
        $admin->assignRole($role);
        $admin->refresh();
        $this->actingAs($admin)->withSession(['admin_session_version' => $admin->session_version]);
        foreach ($paths as $path) {
            $this->get($path)->assertForbidden();
        }
    }

    private function createExistingContent(): array
    {
        $translations = require database_path('translations/bangla-content.php');
        foreach (array_keys($translations['services']['name']) as $name) {
            Service::create([
                'name' => $name, 'slug' => Str::slug($name),
                'short_desc' => array_key_first($translations['services']['short_desc']),
                'body' => array_key_first($translations['services']['body']),
            ]);
        }
        $country = Country::create(['name' => 'India', 'slug' => 'india']);
        $city = City::create(['country_id' => $country->id, 'name' => 'Chennai', 'slug' => 'chennai']);
        $hospital = Hospital::create(['country_id' => $country->id, 'city_id' => $city->id, 'name' => 'Apollo Hospitals Chennai', 'slug' => 'apollo-chennai']);
        $review = ['hospital_id' => $hospital->id];
        foreach ($translations['reviews'] as $field => $values) {
            $review[$field] = array_key_first($values);
        }
        Review::create($review);
        $category = BlogCategory::create(['name' => 'Medical Visa', 'slug' => 'medical-visa']);
        $post = ['blog_category_id' => $category->id, 'slug' => 'visa-checklist', 'published_at' => now()];
        foreach ($translations['blog_posts'] as $field => $values) {
            $post[$field] = array_key_first($values);
        }
        BlogPost::create($post);
        foreach (array_keys($translations['faqs']['question']) as $index => $question) {
            Faq::create(['question' => $question, 'answer' => array_keys($translations['faqs']['answer'])[$index]]);
        }

        return $translations;
    }

    private function signInOwner(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $role = Role::findOrCreate(AdminAccess::OWNER_ROLE);
        $role->syncPermissions([
            Permission::findOrCreate(AdminAccess::PANEL_PERMISSION),
            Permission::findOrCreate(AdminAccess::MANAGE_SITE_SETTINGS_PERMISSION),
        ]);
        $owner = User::create(['name' => 'Content Owner', 'email' => 'content-owner@example.test', 'password' => 'Test-Password-2026!']);
        DB::table('users')->where('id', $owner->id)->update(['is_owner' => true]);
        $owner->refresh()->assignRole($role);
        $this->actingAs($owner)->withSession(['admin_session_version' => $owner->session_version]);
    }
}
