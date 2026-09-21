<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class HomeHospitalCountriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_previews_are_limited_per_country_and_prioritize_featured_hospitals(): void
    {
        $india = Country::create(['name' => 'India', 'slug' => 'india']);
        $thailand = Country::create(['name' => 'Thailand', 'slug' => 'thailand']);

        foreach (range(1, 6) as $index) {
            $this->hospital($india, $index);
            $this->hospital($thailand, $index);
        }
        $featured = $this->hospital($india, 7, ['is_featured' => true, 'sort_order' => 99]);

        $response = $this->get('/')->assertOk();
        $markets = $response->viewData('hospitalMarkets');
        $indiaRows = $markets->firstWhere('key', 'india')['country']->hospitals;
        $thailandRows = $markets->firstWhere('key', 'thailand')['country']->hospitals;
        $this->assertCount(4, $indiaRows);
        $this->assertCount(4, $thailandRows);
        $this->assertTrue($indiaRows->first()->is($featured));
        $this->assertSame([$featured->id, 1, 3, 5], $indiaRows->modelKeys());
        $this->assertSame([$thailand->id], $thailandRows->pluck('country_id')->unique()->values()->all());

        foreach ($indiaRows->concat($thailandRows) as $hospital) {
            $this->assertTrue($hospital->relationLoaded('city'));
            $this->assertTrue($hospital->relationLoaded('country'));
            $this->assertTrue($hospital->relationLoaded('group'));
        }

        $page = new Crawler($response->getContent());
        $this->assertCount(4, $page->filter('[data-hospital-panel="country-india"] .hospital-card'));
        $this->assertCount(4, $page->filter('[data-hospital-panel="country-thailand"] .hospital-card'));
        $response->assertDontSee('এশিয়ার শীর্ষ হাসপাতালে আপনার চিকিৎসার পথ সহজ করি');
        $this->assertCount(1, $page->filter('#featured-hospitals'));
    }

    public function test_small_and_empty_countries_have_no_filler_cards_and_keep_all_destination_filters(): void
    {
        $china = Country::create(['name' => 'China', 'slug' => 'china']);
        $thailand = Country::create(['name' => 'Thailand', 'slug' => 'thailand']);
        Country::create(['name' => 'Singapore', 'slug' => 'singapore']);
        $this->hospital($china, 1);
        $this->hospital($china, 2);
        $this->hospital($thailand, 1);

        app()->setLocale('bn');
        $response = $this->get('/')->assertOk();
        $page = new Crawler($response->getContent());

        $this->assertCount(2, $page->filter('[data-hospital-panel="country-china"] .hospital-card'));
        $this->assertCount(1, $page->filter('[data-hospital-panel="country-thailand"] .hospital-card'));
        $this->assertCount(0, $page->filter('[data-hospital-panel="country-singapore"] .hospital-card'));
        $this->assertStringContainsString('সিঙ্গাপুর-এর কোনো হাসপাতাল এখনো যোগ করা হয়নি।', $page->filter('[data-hospital-panel="country-singapore"]')->text());
        $this->assertStringContainsString('মালয়েশিয়া-এর কোনো হাসপাতাল এখনো যোগ করা হয়নি।', $page->filter('[data-hospital-panel="country-malaysia"]')->text());
        $this->assertStringContainsString('কোনো ফিচার্ড হাসপাতাল এখনো যোগ করা হয়নি।', $page->filter('[data-hospital-panel="featured"]')->text());

        foreach (['featured', 'country-thailand', 'country-china', 'country-india', 'country-singapore', 'country-malaysia'] as $key) {
            $this->assertCount(1, $page->filter('[data-country-filter="'.$key.'"]'));
        }
        $this->assertSame('display: none;', $page->filter('[data-hospital-panel="country-china"]')->attr('style'));
        $this->assertCount(1, $page->filter('[data-hospital-panel="country-singapore"] a[href="'.route('contact').'"]'));
    }

    public function test_admin_added_countries_and_actual_country_slugs_work_with_english_copy(): void
    {
        $india = Country::create(['name' => 'India', 'slug' => 'republic-of-india']);
        $germany = Country::create(['name' => 'Germany', 'slug' => 'germany']);
        $this->hospital($india, 1);
        $this->hospital($germany, 1);

        app()->setLocale('en');
        $response = $this->get('/')->assertOk();
        $page = new Crawler($response->getContent());
        $this->assertCount(1, $page->filter('[data-country-filter="country-republic-of-india"]'));
        $this->assertCount(0, $page->filter('[data-country-filter="country-india"]'));
        $this->assertCount(1, $page->filter('[data-country-filter="country-germany"]'));
        $this->assertCount(1, $page->filter('[data-hospital-panel="country-germany"] .hospital-card'));
        $this->assertStringContainsString('No hospitals have been added for Malaysia yet.', $page->filter('[data-hospital-panel="country-malaysia"]')->text());
        $this->assertSame(route('hospitals.index'), $page->filter('[data-featured-view-all]')->attr('href'));
        $response->assertSee('country=republic-of-india', false);
        $response->assertSee('country=germany', false);
        $response->assertDontSee('Your route to leading hospitals across Asia');
    }

    private function hospital(Country $country, int $index, array $overrides = []): Hospital
    {
        $city = City::firstOrCreate(['country_id' => $country->id], [
            'name' => $country->name.' City', 'slug' => $country->slug.'-city',
        ]);

        return Hospital::create(array_merge([
            'country_id' => $country->id,
            'city_id' => $city->id,
            'name' => $country->name.' Hospital '.$index,
            'slug' => $country->slug.'-hospital-'.$index,
            'is_featured' => false,
            'sort_order' => $index,
        ], $overrides));
    }
}
