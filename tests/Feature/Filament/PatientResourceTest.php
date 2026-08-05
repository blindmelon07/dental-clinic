<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PatientResource\Pages\CreatePatient;
use App\Filament\Resources\PatientResource\Pages\ViewPatient;
use App\Filament\Resources\PatientResource\RelationManagers\AppointmentsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\DentalRecordsRelationManager;
use App\Filament\Resources\PatientResource\RelationManagers\InvoicesRelationManager;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp(); // creates roles via TestCase::setUp()

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');
    }

    public function test_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patients');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patients/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/patients/{$patient->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/patients/{$patient->id}/edit");
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/admin/patients');
        $response->assertRedirect();
    }

    public function test_dental_record_row_opens_a_view_modal(): void
    {
        $patient = Patient::factory()->create();
        $record = DentalRecord::factory()->create(['patient_id' => $patient->id]);

        Livewire::actingAs($this->admin)
            ->test(DentalRecordsRelationManager::class, [
                'ownerRecord' => $patient,
                'pageClass'   => ViewPatient::class,
            ])
            ->assertTableActionExists('view')
            ->callTableAction('view', $record)
            ->assertSuccessful();
    }

    public function test_appointment_row_opens_a_view_modal(): void
    {
        $patient = Patient::factory()->create();
        $record = Appointment::factory()->create(['patient_id' => $patient->id]);

        Livewire::actingAs($this->admin)
            ->test(AppointmentsRelationManager::class, [
                'ownerRecord' => $patient,
                'pageClass'   => ViewPatient::class,
            ])
            ->assertTableActionExists('view')
            ->callTableAction('view', $record)
            ->assertSuccessful();
    }

    public function test_invoice_row_opens_a_view_modal(): void
    {
        $patient = Patient::factory()->create();
        $record = Invoice::factory()->create(['patient_id' => $patient->id]);

        Livewire::actingAs($this->admin)
            ->test(InvoicesRelationManager::class, [
                'ownerRecord' => $patient,
                'pageClass'   => ViewPatient::class,
            ])
            ->assertTableActionExists('view')
            ->callTableAction('view', $record)
            ->assertSuccessful();
    }

    public function test_create_another_dispatches_wizard_reset_event(): void
    {
        Clinic::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreatePatient::class)
            ->fillForm([
                'last_name'     => 'Doe',
                'first_name'    => 'Jane',
                'date_of_birth' => '1995-05-05',
                'gender'        => 'female',
                'address'       => '123 Main St',
                'phone'         => '09171234567',
                'consent_agreed' => true,
            ])
            ->call('createAnother')
            ->assertHasNoFormErrors()
            ->assertDispatched('patient-wizard-reset');

        $this->assertSame(1, Patient::count());
    }
}
