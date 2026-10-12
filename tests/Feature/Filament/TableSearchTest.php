<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\AppointmentResource\Pages\ListAppointments;
use App\Filament\Resources\DentalRecordResource\Pages\ListDentalRecords;
use App\Filament\Resources\DentistResource\Pages\ListDentists;
use App\Filament\Resources\InvoiceResource\Pages\ListInvoices;
use App\Filament\Resources\MedicineDispensingResource\Pages\ListMedicineDispensings;
use App\Filament\Resources\PatientCertificateResource\Pages\ListPatientCertificates;
use App\Filament\Resources\PatientResource\Pages\ListPatients;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Resources\PrescriptionResource\Pages\ListPrescriptions;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TableSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_pages_can_be_searched_and_sorted_by_patient_name(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $pages = [
            ListPatients::class,
            ListDentists::class,
            ListAppointments::class,
            ListDentalRecords::class,
            ListInvoices::class,
            ListPrescriptions::class,
            ListMedicineDispensings::class,
            ListPatientCertificates::class,
            ListPayments::class,
        ];

        foreach ($pages as $page) {
            Livewire::test($page)
                ->searchTable('CEDO')
                ->assertOk();
        }

        Livewire::test(ListPatients::class)
            ->sortTable('full_name')
            ->assertOk();
    }

    public function test_searching_by_patient_name_finds_related_records(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $match = Patient::factory()->create(['first_name' => 'Juan', 'last_name' => 'Cedo']);
        $other = Patient::factory()->create(['first_name' => 'Maria', 'last_name' => 'Santos']);

        $cases = [
            ListPatients::class => [$match, $other],
            ListAppointments::class => [
                Appointment::factory()->create(['patient_id' => $match->id]),
                Appointment::factory()->create(['patient_id' => $other->id]),
            ],
            ListDentalRecords::class => [
                DentalRecord::factory()->create(['patient_id' => $match->id, 'diagnosis' => 'Caries']),
                DentalRecord::factory()->create(['patient_id' => $other->id, 'diagnosis' => 'Caries']),
            ],
            ListInvoices::class => [
                Invoice::factory()->create(['patient_id' => $match->id]),
                Invoice::factory()->create(['patient_id' => $other->id]),
            ],
        ];

        foreach ($cases as $page => [$found, $notFound]) {
            Livewire::test($page)
                ->searchTable('CEDO')
                ->assertCanSeeTableRecords([$found])
                ->assertCanNotSeeTableRecords([$notFound]);
        }
    }
}
