<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Resources\DentalRecordResource\Pages\ListDentalRecords;
use App\Filament\Resources\DentistResource\Pages\ListDentists;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Filament\Resources\PatientResource\Pages\EditPatient;
use App\Filament\Resources\PatientResource\Pages\ListPatients;
use App\Filament\Resources\PrescriptionResource\Pages\ListPrescriptions;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrashedRecordsTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_list_pages_render_with_trashed_filter(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        foreach ([ListPatients::class, ListDentists::class, ListAppointments::class, ListDentalRecords::class, ListInvoices::class, ListPrescriptions::class] as $page) {
            Livewire::test($page)
                ->filterTable('trashed', true)
                ->assertOk();
        }
    }

    public function test_trashed_filter_shows_only_trashed_records(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $active = Patient::factory()->create();
        $trashed = Patient::factory()->create();
        $trashed->delete();

        Livewire::test(ListPatients::class)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$trashed])
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$trashed])
            ->assertCanNotSeeTableRecords([$active]);
    }

    public function test_trashed_record_edit_page_opens(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $patient = Patient::factory()->create();
        $patient->delete();

        $this->get("/admin/patients/{$patient->id}/edit")->assertOk();
    }

    public function test_admin_can_restore_but_not_force_delete(): void
    {
        $this->actingAs($this->userWithRole('admin'));

        $patient = Patient::factory()->create();
        $patient->delete();

        Livewire::test(ListPatients::class)
            ->filterTable('trashed', false)
            ->assertActionHidden(TestAction::make(ForceDeleteAction::class)->table($patient))
            ->callAction(TestAction::make(RestoreAction::class)->table($patient));

        $this->assertNotSoftDeleted($patient);
    }

    public function test_receptionist_cannot_restore(): void
    {
        // Receptionists have no delete_patient permission.
        $this->actingAs($this->userWithRole('receptionist'));

        $patient = Patient::factory()->create();
        $patient->delete();

        Livewire::test(ListPatients::class)
            ->filterTable('trashed', false)
            ->assertActionHidden(TestAction::make(RestoreAction::class)->table($patient));
    }

    public function test_super_admin_can_force_delete_record_without_dependents(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $patient = Patient::factory()->create();
        $patient->delete();

        Livewire::test(EditPatient::class, ['record' => $patient->getRouteKey()])
            ->callAction(ForceDeleteAction::class);

        $this->assertModelMissing($patient);
    }

    public function test_force_delete_blocked_while_dependents_exist(): void
    {
        $this->actingAs($this->userWithRole('super_admin'));

        $appointment = Appointment::factory()->create();
        $patient = $appointment->patient;
        // A trashed appointment would still be wiped by the FK cascade.
        $appointment->delete();
        $patient->delete();

        Livewire::test(ListPatients::class)
            ->filterTable('trashed', false)
            ->assertActionDisabled(TestAction::make(ForceDeleteAction::class)->table($patient));

        $this->assertSoftDeleted($patient);
        $this->assertSoftDeleted($appointment);
    }
}
