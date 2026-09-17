<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

class ImportDoctors extends Command
{
    protected $signature = 'doctors:import {file : An authorized UTF-8 CSV roster} {--commit : Write the previewed records to the database}';

    protected $description = 'Preview or import doctors from an authorized hospital roster';

    private const REQUIRED_COLUMNS = ['country', 'city', 'hospital', 'department', 'doctor_name', 'source_url'];

    private const ALLOWED_SOURCE_DOMAINS = [
        'apollohospitals.com',
        'fortishealthcare.com',
        'maxhealthcare.in',
    ];

    public function handle(): int
    {
        try {
            [$rows, $errors] = $this->readRows($this->argument('file'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($errors !== []) {
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->error($error);
            }

            $this->error(count($errors).' invalid row(s); nothing was imported.');

            return self::FAILURE;
        }

        if ($rows === []) {
            $this->error('The roster contains no doctor rows.');

            return self::FAILURE;
        }

        $seen = [];

        foreach (Doctor::query()->with('hospital:id,name')->orderBy('id')->get(['id', 'name', 'hospital_id']) as $doctor) {
            $seen[$this->doctorKey($doctor->name)] ??= $doctor->hospital?->name ?? 'an existing hospital';
        }

        $new = [];
        $duplicates = [];

        foreach ($rows as $row) {
            $key = $this->doctorKey($row['doctor_name']);

            if (isset($seen[$key])) {
                $duplicates[] = "Line {$row['_line']} skipped: the name already appears at {$seen[$key]}.";

                continue;
            }

            $seen[$key] = $row['hospital'];
            $new[] = $row;
        }

        $this->components->info(count($rows).' valid row(s): '.count($new).' new, '.count($duplicates).' duplicate name(s) skipped.');

        $groups = collect($new)
            ->groupBy(fn (array $row) => implode(' / ', [$row['country'], $row['city'], $row['hospital'], $row['department']]))
            ->map(fn ($group, string $location) => [$location, $group->count()])
            ->sortBy(fn (array $group) => $group[0])
            ->values();

        if ($groups->isNotEmpty()) {
            $this->table(['Country / City / Hospital / Department', 'New doctors'], $groups->take(30)->all());
        }

        if ($groups->count() > 30) {
            $this->components->warn('Additional location and department groups were omitted from this summary.');
        }

        foreach (array_slice($duplicates, 0, 20) as $duplicate) {
            $this->components->warn($duplicate);
        }

        if (count($duplicates) > 20) {
            $this->components->warn('Additional duplicate names were omitted from this summary.');
        }

        if (! $this->option('commit')) {
            $this->components->warn('Preview only. No database changes were made. Pass --commit to import.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($new): void {
            foreach ($new as $row) {
                $country = Country::query()->firstOrCreate(
                    ['slug' => Str::slug($row['country'])],
                    ['name' => $row['country']]
                );

                $city = City::query()->firstOrCreate(
                    ['country_id' => $country->id, 'name' => $row['city']],
                    ['slug' => $this->availableSlug(City::class, $row['city'].' '.$country->name)]
                );

                $hospital = Hospital::query()->firstOrCreate(
                    ['city_id' => $city->id, 'name' => $row['hospital']],
                    [
                        'country_id' => $country->id,
                        'slug' => $this->availableSlug(Hospital::class, $row['hospital'].' '.$city->name),
                    ]
                );

                $department = Department::query()->firstOrCreate(
                    ['name' => $row['department']],
                    ['slug' => $this->availableSlug(Department::class, $row['department'])]
                );

                Doctor::query()->create([
                    'hospital_id' => $hospital->id,
                    'department_id' => $department->id,
                    'name' => $row['doctor_name'],
                    'slug' => $this->availableSlug(Doctor::class, $row['doctor_name']),
                    'designation' => $row['designation'] ?: null,
                    'qualifications' => $row['qualifications'] ?: null,
                    'experience_years' => $row['experience_years'] ?: 0,
                    'source_url' => $row['source_url'],
                    'is_featured' => false,
                ]);
            }
        });

        $this->components->info(count($new).' doctor(s) imported. Existing doctors were not changed.');

        return self::SUCCESS;
    }

    private function readRows(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('The roster file does not exist or is not readable.');
        }

        $file = fopen($path, 'rb');

        if ($file === false) {
            throw new RuntimeException('The roster file could not be opened.');
        }

        try {
            $header = fgetcsv($file);

            if ($header === false) {
                throw new RuntimeException('The roster file is empty.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header = array_map(fn ($column) => Str::lower(trim($column)), $header);

            if (count($header) !== count(array_unique($header)) || array_diff(self::REQUIRED_COLUMNS, $header) !== []) {
                throw new RuntimeException('The CSV header must contain unique country, city, hospital, department, doctor_name, and source_url columns.');
            }

            $rows = [];
            $errors = [];
            $line = 1;

            while (($values = fgetcsv($file)) !== false) {
                $line++;

                if ($values === [null]) {
                    continue;
                }

                if (count($values) !== count($header)) {
                    $errors[] = "Line {$line}: column count differs from the header.";

                    continue;
                }

                $row = array_map(fn ($value) => trim((string) $value), array_combine($header, $values));
                $row += ['designation' => '', 'qualifications' => '', 'experience_years' => ''];
                $validator = Validator::make($row, [
                    'country' => ['required', 'string', 'max:255'],
                    'city' => ['required', 'string', 'max:255'],
                    'hospital' => ['required', 'string', 'max:255'],
                    'department' => ['required', 'string', 'max:255'],
                    'doctor_name' => ['required', 'string', 'max:255'],
                    'designation' => ['nullable', 'string', 'max:255'],
                    'qualifications' => ['nullable', 'string', 'max:255'],
                    'experience_years' => ['nullable', 'integer', 'between:0,90'],
                    'source_url' => ['required', 'url', 'max:2048', 'starts_with:https://'],
                ]);

                if ($validator->fails()
                    || ! $this->hasOfficialSource($row['source_url'])
                    || Str::slug($row['country']) === ''
                    || Str::slug($row['city']) === ''
                    || Str::slug($row['hospital']) === ''
                    || Str::slug($row['department']) === ''
                    || Str::slug($row['doctor_name']) === ''
                    || $this->doctorKey($row['doctor_name']) === '') {
                    $errors[] = "Line {$line}: invalid fields or a non-official source URL.";

                    continue;
                }

                $rows[] = [...$row, '_line' => $line];
            }

            return [$rows, $errors];
        } finally {
            fclose($file);
        }
    }

    private function hasOfficialSource(string $url): bool
    {
        $host = Str::lower(parse_url($url, PHP_URL_HOST) ?: '');

        foreach (self::ALLOWED_SOURCE_DOMAINS as $domain) {
            if ($host === $domain || Str::endsWith($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }

    private function doctorKey(string $name): string
    {
        $name = preg_replace('/^(?:(?:dr|doctor)\.?\s+)+/iu', '', Str::lower(Str::squish($name)));

        return Str::squish(preg_replace('/[^\pL\pN]+/u', ' ', $name));
    }

    private function availableSlug(string $model, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($model::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
