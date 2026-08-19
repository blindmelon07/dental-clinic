<?php

namespace Tests\Feature\Filament;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\DentalRecordResource\Pages\CreateDentalRecord;
use App\Filament\Resources\DentalRecordResource\Pages\EditDentalRecord;
use App\Models\DentalRecord;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DentalRecordResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/dental-records');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dental-records/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $record = DentalRecord::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/dental-records/{$record->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $record = DentalRecord::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/dental-records/{$record->id}/edit");
        $response->assertStatus(200);
    }

    public function test_saved_diagnosis_is_preselected_when_editing(): void
    {
        $record = DentalRecord::factory()->create([
            'diagnosis' => 'Cavity in tooth 14, needs filling',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->getRouteKey()])
            // The diagnosis Repeater keys its rows by a runtime-generated UUID, so
            // compare row values only rather than asserting on the whole keyed array.
            ->assertFormSet(function (array $state): array {
                $this->assertEquals(
                    [
                        ['service' => 'Cavity in tooth 14', 'partial_payment' => 0.0],
                        ['service' => 'needs filling', 'partial_payment' => 0.0],
                    ],
                    array_values($state['diagnosis'] ?? [])
                );

                return [];
            });
    }

    public function test_diagnosis_survives_a_resave(): void
    {
        $record = DentalRecord::factory()->create([
            'diagnosis' => 'Cavity in tooth 14, needs filling',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->getRouteKey()])
            ->call('save');

        $this->assertSame('Cavity in tooth 14, needs filling', $record->refresh()->diagnosis);
    }

    public function test_create_another_dispatches_tabs_reset_event(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis' => [['service' => $service->display_name]],
            ])
            ->call('createAnother')
            ->assertHasNoFormErrors()
            ->assertDispatched('dental-record-tabs-reset');

        $this->assertSame(1, DentalRecord::count());
    }

    public function test_can_delete_a_dental_record(): void
    {
        $record = DentalRecord::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->getRouteKey()])
            ->callAction('delete');

        // DentalRecord uses SoftDeletes: the row still exists with deleted_at set.
        $this->assertSoftDeleted($record);
    }

    public function test_can_set_a_partial_payment_when_creating(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name, 'partial_payment' => 400]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertEquals(400, DentalRecord::first()->partial_payment);
        $this->assertEquals(600, DentalRecord::first()->balance_due);
    }

    public function test_partial_payment_cannot_exceed_its_services_price(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [['service' => $service->display_name, 'partial_payment' => 1500]],
            ])
            ->call('create')
            ->assertHasFormErrors(['diagnosis.0.partial_payment']);
    }

    public function test_partial_payments_are_entered_per_diagnosis_and_summed(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $filling = Service::factory()->create(['price' => 1000]);
        $cleaning = Service::factory()->create(['price' => 500]);

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis'  => [
                    ['service' => $filling->display_name, 'partial_payment' => 400],
                    ['service' => $cleaning->display_name, 'partial_payment' => 500],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $record = DentalRecord::first();

        $this->assertEquals(900, $record->partial_payment);
        $this->assertEquals(600, $record->balance_due);
        $this->assertEquals([400.0, 500.0], $record->diagnosis_partial_payments);
    }

    public function test_generating_an_invoice_carries_the_partial_payment_over_as_a_recorded_payment(): void
    {
        $service = Service::factory()->create(['price' => 1000]);
        $record = DentalRecord::factory()->create([
            'diagnosis'       => $service->display_name,
            'partial_payment' => 400,
        ]);

        $invoice = $record->createInvoice();

        $this->assertEquals(400, $invoice->amount_paid);
        $this->assertEquals(600, $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::PartiallyPaid, $invoice->status);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 400]);
    }

    public function test_generating_an_invoice_with_a_full_partial_payment_marks_it_paid(): void
    {
        $service = Service::factory()->create(['price' => 1000]);
        $record = DentalRecord::factory()->create([
            'diagnosis'       => $service->display_name,
            'partial_payment' => 1000,
        ]);

        $invoice = $record->createInvoice();

        $this->assertEquals(0, $invoice->balance_due);
        $this->assertEquals(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }
}
