<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\VisaDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPreparationChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_visa_page_only_shows_active_required_unassigned_documents(): void
    {
        app()->setLocale('en');

        $service = Service::query()->create([
            'name' => 'Appointment Coordination',
            'slug' => 'appointment-coordination',
        ]);

        $this->document('Valid Passport');
        $this->document('Optional Cover Letter', ['is_required' => false]);
        $this->document('Inactive Statement', ['is_active' => false]);
        $this->document('Service-only Report', ['service_id' => $service->id]);

        $response = $this->get('/visa-support')->assertOk();

        $response->assertSee('Required Documents Checklist');
        $response->assertSee('Valid Passport');
        $response->assertSee('documentChecklist', false);
        $response->assertDontSee('Optional Cover Letter');
        $response->assertDontSee('Inactive Statement');
        $response->assertDontSee('Service-only Report');
    }

    public function test_service_page_only_shows_documents_assigned_to_that_service(): void
    {
        app()->setLocale('en');

        $service = Service::query()->create([
            'name' => 'Appointment Coordination',
            'slug' => 'appointment-coordination',
            'body' => 'We coordinate specialist appointments.',
        ]);
        $otherService = Service::query()->create([
            'name' => 'Interpreter Support',
            'slug' => 'interpreter-support',
        ]);

        $this->document('Appointment Letter', ['service_id' => $service->id]);
        $this->document('Interpreter Notes', ['service_id' => $otherService->id]);
        $this->document('General Visa Passport');

        $response = $this->get('/services/appointment-coordination')->assertOk();

        $response->assertSee('Required Documents Checklist');
        $response->assertSee('Appointment Letter');
        $response->assertSee('service-'.$service->id, false);
        $response->assertDontSee('Interpreter Notes');
        $response->assertDontSee('General Visa Passport');
    }

    private function document(string $title, array $overrides = []): VisaDocument
    {
        return VisaDocument::query()->create(array_merge([
            'title_bn' => $title,
            'title_en' => $title,
            'category' => 'medical_visa',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 10,
        ], $overrides));
    }
}
