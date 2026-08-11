<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PatientCertificateResource\Pages\CreatePatientCertificate;
use App\Filament\Resources\PatientCertificateResource\Pages\EditPatientCertificate;
use App\Models\Patient;
use App\Models\PatientCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientCertificateResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');
    }

    public function test_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patient-certificates');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patient-certificates/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_certification(): void
    {
        $patient = Patient::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreatePatientCertificate::class)
            ->fillForm([
                'patient_id'   => $patient->id,
                'type'         => 'certification',
                'date_treated' => today()->format('Y-m-d'),
                'issue_date'   => today()->format('Y-m-d'),
                'issued_by'    => $this->admin->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('patient_certificates', ['patient_id' => $patient->id, 'type' => 'certification']);
        $this->assertNotNull(PatientCertificate::where('patient_id', $patient->id)->first()->certificate_number);
    }

    public function test_can_create_a_medical_clearance_with_treatments_checklist(): void
    {
        $patient = Patient::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreatePatientCertificate::class)
            ->fillForm([
                'patient_id'   => $patient->id,
                'type'         => 'medical_clearance',
                'date_treated' => today()->format('Y-m-d'),
                'issue_date'   => today()->format('Y-m-d'),
                'issued_by'    => $this->admin->id,
                'treatments'   => ['treatment_cleaning', 'treatment_xray'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('patient_certificates', [
            'patient_id'          => $patient->id,
            'treatment_cleaning'  => true,
            'treatment_xray'      => true,
            'treatment_extraction' => false,
        ]);
    }

    public function test_can_edit_a_certificate(): void
    {
        $certificate = PatientCertificate::create([
            'patient_id'         => Patient::factory()->create()->id,
            'issued_by'          => $this->admin->id,
            'type'               => 'certification',
            'certificate_number' => PatientCertificate::generateNumber(),
            'date_treated'       => today(),
            'issue_date'         => today(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditPatientCertificate::class, ['record' => $certificate->getRouteKey()])
            ->fillForm(['findings' => 'Updated findings'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated findings', $certificate->fresh()->findings);
    }

    public function test_can_delete_a_certificate(): void
    {
        $certificate = PatientCertificate::create([
            'patient_id'         => Patient::factory()->create()->id,
            'issued_by'          => $this->admin->id,
            'type'               => 'certification',
            'certificate_number' => PatientCertificate::generateNumber(),
            'date_treated'       => today(),
            'issue_date'         => today(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditPatientCertificate::class, ['record' => $certificate->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($certificate);
    }
}
