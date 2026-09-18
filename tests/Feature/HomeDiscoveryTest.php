<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class HomeDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_discovery_links_and_questions_are_available_in_bengali(): void
    {
        app()->setLocale('bn');

        $response = $this->get('/')->assertOk();
        $response->assertSee('চিকিৎসার পরবর্তী ধাপটি খুঁজুন');
        $response->assertSee('সিদ্ধান্ত নেওয়ার আগে');
        $response->assertSee('বাংলায় সহায়তা নিয়ে ডাক্তার ও হাসপাতাল খুঁজুন');
        $response->assertDontSee('lead save');

        $page = new Crawler($response->getContent());
        $paths = $page->filterXPath('//section[@aria-labelledby="home-pathways-title"]//a');
        $questions = $page->filterXPath('//section[@aria-labelledby="home-questions-title"]//details');

        foreach (['/doctors', '/hospitals', '/cost-estimator', '/visa-support', '/services'] as $path) {
            $this->assertCount(1, $paths->reduce(fn (Crawler $link) => parse_url($link->attr('href'), PHP_URL_PATH) === $path));
        }

        $this->assertCount(3, $questions);
        $this->assertCount(1, $page->filterXPath('//section[@aria-labelledby="home-questions-title"]//a[@href="'.route('faq').'"]'));
    }

    public function test_homepage_counts_only_destinations_with_hospitals_and_uses_english_copy(): void
    {
        $india = Country::create(['name' => 'India', 'slug' => 'india']);
        Country::create(['name' => 'Malaysia', 'slug' => 'malaysia']);
        $city = City::create(['country_id' => $india->id, 'name' => 'Chennai', 'slug' => 'chennai']);
        Hospital::create([
            'country_id' => $india->id,
            'city_id' => $city->id,
            'name' => 'Example Hospital',
            'slug' => 'example-hospital',
        ]);

        app()->setLocale('en');

        $response = $this->get('/')->assertOk();
        $response->assertSee('Find the next step for your care');

        $page = new Crawler($response->getContent());
        $stat = $page->filter('[data-home-stat="destinations"]');
        $this->assertSame('1', trim($stat->children()->first()->text()));
        $this->assertStringContainsString('Destinations', $stat->text());
        $response->assertDontSee('2,500');
    }
}
