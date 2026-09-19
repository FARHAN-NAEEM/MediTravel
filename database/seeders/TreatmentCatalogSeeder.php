<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Setting;
use App\Models\Treatment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TreatmentCatalogSeeder extends Seeder
{
    public const INSTALLATION_KEY = 'treatment_catalog_20260920_installed';

    public function run(): void
    {
        if (Setting::where('key', self::INSTALLATION_KEY)->exists()) {
            return;
        }

        $catalog = json_decode(file_get_contents(database_path('catalogs/treatments-2026-09.json')), true, 512, JSON_THROW_ON_ERROR);

        foreach (array_keys(config('treatments.illustrations')) as $illustration) {
            if (! is_file(public_path('images/treatments/'.$illustration.'.webp'))) {
                throw new RuntimeException('Missing treatment illustration: '.$illustration);
            }
        }

        DB::transaction(function () use ($catalog): void {
            $departments = [];

            foreach ($catalog['departments'] as $entry) {
                $department = Department::whereIn('slug', [$entry['slug'], ...$entry['aliases']])
                    ->orWhere('name', $entry['name'])->orderBy('id')->first();

                if (! $department) {
                    $department = Department::create([
                        'slug' => $entry['slug'],
                        'name' => $entry['name'],
                        'name_bn' => $entry['name_bn'],
                        'icon' => $entry['icon'],
                    ]);
                } elseif (blank($department->name_bn)) {
                    $department->update(['name_bn' => $entry['name_bn']]);
                }

                $departments[$entry['slug']] = $department;
            }

            foreach ($catalog['treatments'] as $index => $entry) {
                $departmentId = $departments[$entry['department']]->id;
                unset($entry['department']);
                $treatment = Treatment::where('slug', $entry['slug'])->orWhere('name', $entry['name'])->first();

                if (! $treatment) {
                    Treatment::create($entry + ['department_id' => $departmentId, 'sort_order' => $index + 10]);
                } else {
                    // Enrich the existing record without replacing its URL, cost links or authored text.
                    $this->fillMissing($treatment, array_intersect_key($entry, array_flip(['name_bn', 'description_bn', 'illustration'])));
                }
            }

            $legacy = [
                'bypass-surgery' => ['name_bn' => 'বাইপাস সার্জারি', 'illustration' => 'heart'],
                'cancer-treatment-planning' => ['name_bn' => 'ক্যান্সারের চিকিৎসা পরিকল্পনা', 'illustration' => 'cancer'],
                'neuro-consultation' => ['name_bn' => 'নিউরোলজি পরামর্শ', 'illustration' => 'brain'],
            ];

            foreach ($legacy as $slug => $fields) {
                if ($treatment = Treatment::where('slug', $slug)->first()) {
                    $this->fillMissing($treatment, $fields);
                }
            }

            // Subsequent deployments must preserve admin edits and deliberate deletions.
            Setting::create(['key' => self::INSTALLATION_KEY, 'value' => '1']);
        });
    }

    private function fillMissing(Treatment $treatment, array $fields): void
    {
        foreach ($fields as $field => $value) {
            if (blank($treatment->getAttribute($field))) {
                $treatment->setAttribute($field, $value);
            }
        }

        if ($treatment->isDirty()) {
            $treatment->save();
        }
    }
}
