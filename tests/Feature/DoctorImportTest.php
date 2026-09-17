<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorImportTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_preview_does_not_write_any_records(): void
    {
        $file = $this->roster([
            $this->doctorRow('Dr. Maya Shah', 'Apollo Hospitals Chennai', 'Cardiac Care', 'https://www.apollohospitals.com/doctors/maya-shah'),
        ]);

        $this->artisan('doctors:import', ['file' => $file])->assertSuccessful();

        $this->assertDatabaseCount('countries', 0);
        $this->assertDatabaseCount('hospitals', 0);
        $this->assertDatabaseCount('doctors', 0);
    }

    public function test_commit_creates_the_hierarchy_and_keeps_the_first_doctor_affiliation(): void
    {
        $file = $this->roster([
            $this->doctorRow('Dr. Maya Shah', 'Apollo Hospitals Chennai', 'Cardiac Care', 'https://www.apollohospitals.com/doctors/maya-shah'),
            $this->doctorRow('Dr Maya Shah', 'Fortis Hospital Kolkata', 'Cancer Care', 'https://www.fortishealthcare.com/doctors/maya-shah', 'Kolkata'),
            $this->doctorRow('Dr. Arjun Sen', 'Max Hospital Saket', 'Cancer Care', 'https://www.maxhealthcare.in/doctor/arjun-sen', 'Delhi'),
        ]);

        $this->artisan('doctors:import', ['file' => $file, '--commit' => true])->assertSuccessful();

        $this->assertDatabaseCount('countries', 1);
        $this->assertDatabaseCount('cities', 2);
        $this->assertDatabaseCount('hospitals', 2);
        $this->assertDatabaseCount('departments', 2);
        $this->assertDatabaseCount('doctors', 2);

        $maya = Doctor::query()->where('name', 'Dr. Maya Shah')->firstOrFail();
        $this->assertSame('Apollo Hospitals Chennai', $maya->hospital->name);
        $this->assertSame('Cardiac Care', $maya->department->name);
        $this->assertSame('https://www.apollohospitals.com/doctors/maya-shah', $maya->source_url);
        $this->assertDatabaseMissing('hospitals', ['name' => 'Fortis Hospital Kolkata']);

        $this->artisan('doctors:import', ['file' => $file, '--commit' => true])->assertSuccessful();
        $this->assertDatabaseCount('doctors', 2);

        $this->get(route('doctors.index', ['q' => 'Maya']))
            ->assertOk()
            ->assertSee('Dr. Maya Shah')
            ->assertDontSee('Dr. Arjun Sen');
        $this->get(route('doctors.show', $maya))->assertOk()->assertDontSee('0 years experience');
    }

    public function test_invalid_source_rejects_the_entire_file_before_any_write(): void
    {
        $file = $this->roster([
            $this->doctorRow('Dr. Maya Shah', 'Apollo Hospitals Chennai', 'Cardiac Care', 'https://www.apollohospitals.com/doctors/maya-shah'),
            $this->doctorRow('Dr. Arjun Sen', 'Max Hospital Saket', 'Cancer Care', 'https://maxhealthcare.in.example.com/doctor/arjun-sen', 'Delhi'),
        ]);

        $this->artisan('doctors:import', ['file' => $file, '--commit' => true])->assertExitCode(1);

        $this->assertDatabaseCount('countries', 0);
        $this->assertDatabaseCount('doctors', 0);
    }

    public function test_existing_doctor_is_not_reassigned_to_a_new_hospital(): void
    {
        $country = Country::query()->create(['name' => 'India', 'slug' => 'india']);
        $city = City::query()->create(['country_id' => $country->id, 'name' => 'Chennai', 'slug' => 'chennai']);
        $hospital = Hospital::query()->create([
            'country_id' => $country->id,
            'city_id' => $city->id,
            'name' => 'Apollo Hospitals Chennai',
            'slug' => 'apollo-hospitals-chennai',
        ]);
        $department = Department::query()->create(['name' => 'Cardiac Care', 'slug' => 'cardiac']);
        Doctor::query()->create([
            'hospital_id' => $hospital->id,
            'department_id' => $department->id,
            'name' => 'Dr. Maya Shah',
            'slug' => 'dr-maya-shah',
        ]);

        $file = $this->roster([
            $this->doctorRow('Dr Maya Shah', 'Fortis Hospital Kolkata', 'Cancer Care', 'https://www.fortishealthcare.com/doctors/maya-shah', 'Kolkata'),
        ]);

        $this->artisan('doctors:import', ['file' => $file, '--commit' => true])->assertSuccessful();

        $this->assertDatabaseCount('doctors', 1);
        $this->assertDatabaseCount('hospitals', 1);
        $this->assertSame($hospital->id, Doctor::firstOrFail()->hospital_id);
    }

    private function doctorRow(string $name, string $hospital, string $department, string $source, string $city = 'Chennai'): array
    {
        return ['India', $city, $hospital, $department, $name, $source, '', '', ''];
    }

    private function roster(array $rows): string
    {
        $file = tempnam(sys_get_temp_dir(), 'doctor-roster-');
        $this->files[] = $file;
        $handle = fopen($file, 'wb');

        fputcsv($handle, ['country', 'city', 'hospital', 'department', 'doctor_name', 'source_url', 'designation', 'qualifications', 'experience_years'], ',', '"', '');

        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '');
        }

        fclose($handle);

        return $file;
    }
}
