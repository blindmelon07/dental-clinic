<?php

namespace Tests\Feature\Filament;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\InvoiceResource\Pages\CreateInvoice;
use App\Filament\Resources\InvoiceResource\Pages\EditInvoice;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/invoices');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/invoices/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/invoices/{$invoice->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/invoices/{$invoice->id}/edit");
        $response->assertStatus(200);
    }

    public function test_can_create_an_invoice_with_line_items(): void
    {
        $patient = Patient::factory()->create();
        $service = Service::factory()->create(['price' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(CreateInvoice::class)
            ->fillForm([
                'patient_id'   => $patient->id,
                'invoice_date' => today()->format('Y-m-d'),
                'items'        => [
                    ['service_id' => $service->id, 'description' => $service->name, 'quantity' => 1, 'unit_price' => 1000, 'total' => 1000],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id]);
        $this->assertSame(1, Invoice::where('patient_id', $patient->id)->first()->items()->count());
    }

    public function test_can_edit_an_invoice(): void
    {
        $invoice = Invoice::factory()->create(['notes' => 'Original notes']);

        Livewire::actingAs($this->admin)
            ->test(EditInvoice::class, ['record' => $invoice->getRouteKey()])
            ->fillForm(['notes' => 'Updated notes'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Updated notes', $invoice->fresh()->notes);
    }

    public function test_recording_a_payment_updates_balance_and_status(): void
    {
        $invoice = Invoice::factory()->create(['total' => 1000, 'amount_paid' => 0, 'balance_due' => 1000]);

        Livewire::actingAs($this->admin)
            ->test(EditInvoice::class, ['record' => $invoice->getRouteKey()])
            ->callAction('recordPayment', data: [
                'amount'         => 1000,
                'payment_method' => PaymentMethod::Cash->value,
                'paid_at'        => now(),
            ]);

        $this->assertEquals(0, $invoice->fresh()->balance_due);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 1000]);
    }

    public function test_can_delete_an_invoice(): void
    {
        $invoice = Invoice::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditInvoice::class, ['record' => $invoice->getRouteKey()])
            ->callAction('delete');

        // Invoice uses SoftDeletes: the row still exists with deleted_at set.
        $this->assertSoftDeleted($invoice);
    }

    public function test_the_outstanding_stat_card_url_actually_filters_the_list(): void
    {
        // Regression test: Filament's ListRecords page binds $tableFilters to the URL
        // query key "filters" (#[Url(as: 'filters')]), not "tableFilters" — using the
        // wrong key silently opened the list unfiltered. Visit the exact URL the
        // BillingStatsWidget "Outstanding Balance" card links to and check it narrows.
        $outstanding = Invoice::factory()->create(['status' => InvoiceStatus::PartiallyPaid, 'balance_due' => 500]);
        $paid = Invoice::factory()->create(['status' => InvoiceStatus::Paid, 'balance_due' => 0]);

        $url = InvoiceResource::getUrl('index', ['filters' => ['outstanding' => ['isActive' => true]]]);

        $response = $this->actingAs($this->admin)->get($url);

        $response->assertStatus(200);
        $response->assertSee($outstanding->invoice_number);
        $response->assertDontSee($paid->invoice_number);
    }
}
