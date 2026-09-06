<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_hospital_cards_use_the_shared_illustration_and_localized_highlight(): void
    {
        $hospital = $this->hospital([
            'card_highlight_bn' => 'হার্টের চিকিৎসার জন্য বিশেষভাবে পরিচিত',
            'card_highlight_en' => 'Known for specialist cardiac care',
        ]);

        app()->setLocale('bn');

        $response = $this->get('/hospitals')->assertOk();

        $response->assertSee('images/hospital-card-illustration.svg', false);
        $response->assertSee($hospital->card_highlight_bn);
        $response->assertDontSee($hospital->card_highlight_en);
    }

    public function test_hospital_card_highlight_falls_back_and_empty_highlights_render_no_placeholder(): void
    {
        $fallbackHospital = $this->hospital([
            'name' => 'Fallback Hospital',
            'slug' => 'fallback-hospital',
            'card_highlight_bn' => null,
            'card_highlight_en' => 'International patient support available',
        ]);
        $this->hospital([
            'name' => 'No Highlight Hospital',
            'slug' => 'no-highlight-hospital',
            'card_highlight_bn' => null,
            'card_highlight_en' => null,
        ]);

        app()->setLocale('bn');

        $response = $this->get('/hospitals')->assertOk();

        $response->assertSee($fallbackHospital->card_highlight_en);
        $this->assertNull(Hospital::where('slug', 'no-highlight-hospital')->firstOrFail()->cardHighlight());
    }

    private function hospital(array $overrides = []): Hospital
    {
        $country = Country::firstOrCreate(
            ['slug' => 'india'],
            ['name' => 'India', 'sort_order' => 1],
        );
        $city = City::firstOrCreate(
            ['slug' => 'chennai'],
            ['country_id' => $country->id, 'name' => 'Chennai'],
        );

        return Hospital::query()->create(array_merge([
            'country_id' => $country->id,
            'city_id' => $city->id,
            'name' => 'Apollo Hospitals Chennai',
            'slug' => 'apollo-hospitals-chennai',
            'accreditation' => 'NABH, JCI',
            'is_featured' => true,
            'sort_order' => 1,
        ], $overrides));
    }
}
