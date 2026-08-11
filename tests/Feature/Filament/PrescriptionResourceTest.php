<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PrescriptionResource\Pages\CreatePrescription;
use App\Filament\Resources\PrescriptionResource\Pages\EditPrescription;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrescriptionResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/prescriptions');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/prescriptions/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_prescription_with_medications(): void
    {
        Clinic::factory()->create();
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreatePrescription::class)
            ->fillForm([
                'patient_id'      => $patient->id,
                'dentist_id'      => $dentist->id,
                'prescribed_date' => today()->format('Y-m-d'),
                'medications'     => [
                    ['name' => 'Amoxicillin', 'strength' => '500mg', 'dose' => '1 capsule', 'frequency' => '3x a day', 'duration' => '7 days'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $prescription = Prescription::where('patient_id', $patient->id)->first();
        $this->assertNotNull($prescription);
        $this->assertNotNull($prescription->prescription_number);
        $this->assertSame('Amoxicillin', $prescription->medications[0]['name']);
    }

    public function test_creating_a_prescription_does_not_error_when_the_logged_in_user_has_no_clinic(): void
    {
        // Regression test: the create page used to hardcode clinic_id => 1 as a
        // fallback, which threw a foreign key violation whenever no clinic with
        // id 1 existed. It should now fall back to the first available clinic.
        Clinic::factory()->create(['id' => 5]);
        $this->assertNull($this->admin->clinic_id);

        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreatePrescription::class)
            ->fillForm([
                'patient_id'      => $patient->id,
                'dentist_id'      => $dentist->id,
                'prescribed_date' => today()->format('Y-m-d'),
                'medications'     => [['name' => 'Ibuprofen']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('prescriptions', ['patient_id' => $patient->id, 'clinic_id' => 5]);
    }

    public function test_can_edit_a_prescription(): void
    {
        Clinic::factory()->create();
        $prescription = Prescription::create([
            'prescription_number' => Prescription::generateNumber(),
            'clinic_id'           => Clinic::first()->id,
            'patient_id'          => Patient::factory()->create()->id,
            'dentist_id'          => Dentist::factory()->create()->id,
            'prescribed_date'     => today(),
            'medications'         => [['name' => 'Amoxicillin']],
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditPrescription::class, ['record' => $prescription->getRouteKey()])
            ->fillForm(['notes' => 'Take after meals'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Take after meals', $prescription->fresh()->notes);
    }

    public function test_can_delete_a_prescription(): void
    {
        Clinic::factory()->create();
        $prescription = Prescription::create([
            'prescription_number' => Prescription::generateNumber(),
            'clinic_id'           => Clinic::first()->id,
            'patient_id'          => Patient::factory()->create()->id,
            'dentist_id'          => Dentist::factory()->create()->id,
            'prescribed_date'     => today(),
            'medications'         => [['name' => 'Amoxicillin']],
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditPrescription::class, ['record' => $prescription->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($prescription);
    }
}
