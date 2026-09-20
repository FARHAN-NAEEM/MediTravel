<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Hospital;
use App\Models\HospitalGroup;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class HospitalDirectorySeeder extends Seeder
{
    public const INSTALLATION_KEY = 'hospital_directory_20260920_installed';

    public function run(): void
    {
        if (Setting::where('key', self::INSTALLATION_KEY)->exists()) {
            return;
        }

        $catalog = json_decode(file_get_contents(database_path('catalogs/hospitals-2026-09.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($catalog['groups'] as $entry) {
            if (! empty($entry['catalog_logo']) && ! is_file(public_path($entry['catalog_logo']))) {
                throw new RuntimeException('Missing hospital logo: '.$entry['slug']);
            }
        }

        DB::transaction(function () use ($catalog): void {
            $groups = [];
            foreach ($catalog['groups'] as $index => $entry) {
                $group = HospitalGroup::firstOrCreate(['slug' => $entry['slug']], [
                    'name' => $entry['name'], 'website' => $entry['website'],
                    'catalog_logo' => $entry['catalog_logo'] ?? null, 'sort_order' => $index,
                ]);
                $groups[$entry['slug']] = $group;
            }

            foreach ($catalog['hospitals'] as $index => $entry) {
                $countryName = $entry['country'] ?? 'India';
                $country = Country::whereRaw('LOWER(name) = ?', [strtolower($countryName)])->first()
                    ?? Country::firstOrCreate(['slug' => Str::slug($countryName)], ['name' => $countryName]);
                $cityNames = match ($entry['city']) {
                    'Bengaluru' => ['Bengaluru', 'Bangalore'],
                    'New Delhi' => ['New Delhi', 'Delhi'],
                    'Gurugram' => ['Gurugram', 'Gurgaon'],
                    default => [$entry['city']],
                };
                $city = City::where('country_id', $country->id)->whereIn('name', $cityNames)->orderBy('id')->first()
                    ?? City::firstOrCreate(['slug' => Str::slug($entry['city'].'-'.$countryName)], ['name' => $entry['city'], 'country_id' => $country->id]);
                $slug = Str::slug($entry['name']);
                $hospital = Hospital::whereIn('slug', [$slug, ...($entry['aliases'] ?? [])])->orderBy('id')->first()
                    ?? Hospital::where('country_id', $country->id)->where('name', $entry['name'])->first();
                $fields = [
                    'hospital_group_id' => $groups[$entry['group']]->id,
                    'source_url' => $entry['source'],
                    'directory_reviewed_at' => $catalog['reviewed_on'],
                ];

                if (! $hospital) {
                    Hospital::create($fields + [
                        'name' => $entry['name'], 'slug' => $slug, 'city_id' => $city->id, 'country_id' => $country->id,
                        'care_type' => $entry['care_type'] ?? 'multi-specialty',
                        'is_featured' => $entry['featured'] ?? false, 'sort_order' => $index + 10,
                    ]);
                } else {
                    // Keep existing URLs, doctor links, uploaded assets and administrator-authored content.
                    foreach ($fields as $field => $value) {
                        if (blank($hospital->getAttribute($field))) {
                            $hospital->setAttribute($field, $value);
                        }
                    }
                    if ($hospital->isDirty()) {
                        $hospital->save();
                    }
                }
            }

            // Associate imported Apollo records without moving or renaming their branches.
            Hospital::whereNull('hospital_group_id')->where('name', 'like', 'Apollo %')
                ->update(['hospital_group_id' => $groups['apollo']->id]);

            // A one-time installation prevents future deployments from undoing admin edits/deletions.
            Setting::create(['key' => self::INSTALLATION_KEY, 'value' => '1']);
        });
    }
}
