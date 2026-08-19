<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\DentalRecordResource\Pages\CreateDentalRecord;
use App\Filament\Resources\DentalRecordResource\Pages\EditDentalRecord;
use App\Filament\Resources\DentalRecordResource\Pages\ListDentalRecords;
use App\Filament\Resources\DentalRecordResource\RelationManagers\InstallmentsRelationManager;
use App\Filament\Widgets\PaymentPlanStatsWidget;
use App\Models\DentalRecord;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentPlanSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');
    }

    public function test_creating_with_installment_plan_generates_installments(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name]],
                'is_installment' => true,
                'paymentPlan' => [
                    'installment_count' => 4,
                    'frequency'         => 'monthly',
                    'start_date'        => now()->addWeek()->toDateString(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = DentalRecord::first();
        $this->assertNotNull($record->paymentPlan);
        $this->assertEquals(4, $record->paymentPlan->installment_count);
        $this->assertEquals(4, $record->installments()->count());
        $this->assertEquals(1000.0, (float) $record->installments()->sum('amount'));
    }

    public function test_editing_toggles_installment_plan_off_and_deletes_it(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name]],
                'is_installment' => true,
                'paymentPlan' => [
                    'installment_count' => 3,
                    'frequency'         => 'monthly',
                    'start_date'        => now()->addWeek()->toDateString(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = DentalRecord::first();
        $this->assertNotNull($record->paymentPlan);

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->id])
            ->fillForm(['is_installment' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($record->fresh()->paymentPlan);
        $this->assertEquals(0, $record->fresh()->installments()->count());
    }

    public function test_relation_manager_renders_and_records_payment(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name]],
                'is_installment' => true,
                'paymentPlan' => [
                    'installment_count' => 2,
                    'frequency'         => 'monthly',
                    'start_date'        => now()->addWeek()->toDateString(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = DentalRecord::first()->fresh(['installments']);
        $firstInstallment = $record->installments()->orderBy('installment_number')->first();

        Livewire::actingAs($this->admin)
            ->test(InstallmentsRelationManager::class, [
                'ownerRecord' => $record,
                'pageClass' => \App\Filament\Resources\DentalRecordResource\Pages\ViewDentalRecord::class,
            ])
            ->assertSuccessful()
            ->callTableAction('recordPayment', $firstInstallment, data: [
                'amount' => (float) $firstInstallment->amount,
                'payment_method' => 'cash',
                'paid_at' => now(),
            ]);

        $this->assertNotNull($firstInstallment->fresh()->payment_id);
        $this->assertNotNull($record->getOrCreateInvoice());
    }

    public function test_dashboard_widget_and_list_filters_render_with_an_active_plan(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name]],
                'is_installment' => true,
                'paymentPlan' => [
                    'installment_count' => 2,
                    'frequency'         => 'monthly',
                    'start_date'        => now()->addWeek()->toDateString(),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Backdate the first installment directly (the form won't accept a past
        // start_date) so there's something overdue for the widget/filter to find.
        DentalRecord::first()->installments()->first()->update(['due_date' => today()->subDay()]);
        Livewire::actingAs($this->admin)
            ->test(PaymentPlanStatsWidget::class)
            ->assertSuccessful()
            ->assertSee('Active Payment Plans')
            ->assertSee('Overdue Installments')
            ->assertSee('Patients with Balance');

        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);

        Livewire::actingAs($this->admin)
            ->test(ListDentalRecords::class)
            ->assertSuccessful()
            ->filterTable('has_payment_plan', ['value' => '1'])
            ->assertCountTableRecords(1)
            ->resetTableFilters()
            ->filterTable('installments_overdue', ['value' => '1'])
            ->assertCountTableRecords(1)
            ->resetTableFilters()
            ->filterTable('installments_unpaid', ['value' => '1'])
            ->assertCountTableRecords(1);
    }
}
