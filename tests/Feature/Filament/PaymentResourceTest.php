<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PaymentResource;
use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/payments');
        $response->assertStatus(200);
    }

    public function test_list_page_shows_payments(): void
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::create([
            'payment_number' => Payment::generateNumber(),
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 500,
            'payment_method' => 'cash',
            'paid_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/payments');
        $response->assertStatus(200);
        $response->assertSee($payment->payment_number);
    }

    public function test_view_page_renders(): void
    {
        $invoice = Invoice::factory()->create();
        $payment = Payment::create([
            'payment_number' => Payment::generateNumber(),
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 500,
            'payment_method' => 'cash',
            'paid_at'        => now(),
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/payments/{$payment->id}");
        $response->assertStatus(200);
    }

    public function test_create_edit_and_delete_routes_do_not_exist(): void
    {
        $this->actingAs($this->admin)->get('/admin/payments/create')->assertNotFound();
    }

    public function test_paid_at_filter_narrows_the_list(): void
    {
        $invoice = Invoice::factory()->create();
        $todayPayment = Payment::create([
            'payment_number' => Payment::generateNumber(),
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 500,
            'payment_method' => 'cash',
            'paid_at'        => today(),
        ]);
        $oldPayment = Payment::create([
            'payment_number' => 'PAY-OLD-0001',
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 300,
            'payment_method' => 'cash',
            'paid_at'        => today()->subMonth(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ListPayments::class)
            ->filterTable('paid_at', ['from' => today()->toDateString(), 'until' => today()->toDateString()])
            ->assertCanSeeTableRecords([$todayPayment])
            ->assertCanNotSeeTableRecords([$oldPayment]);
    }

    public function test_the_daily_income_stat_card_url_actually_filters_the_list(): void
    {
        // Regression test: Filament's ListRecords page binds $tableFilters to the URL
        // query key "filters" (#[Url(as: 'filters')]), not "tableFilters" — using the
        // wrong key silently opened the list unfiltered. Visit the exact URL the
        // BillingStatsWidget "Daily Income" card links to and check it narrows.
        $invoice = Invoice::factory()->create();
        $todayPayment = Payment::create([
            'payment_number' => Payment::generateNumber(),
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 500,
            'payment_method' => 'cash',
            'paid_at'        => today(),
        ]);
        $oldPayment = Payment::create([
            'payment_number' => 'PAY-OLD-0002',
            'invoice_id'     => $invoice->id,
            'patient_id'     => $invoice->patient_id,
            'amount'         => 300,
            'payment_method' => 'cash',
            'paid_at'        => today()->subMonth(),
        ]);

        $url = PaymentResource::getUrl('index', [
            'filters' => ['paid_at' => ['from' => today()->toDateString(), 'until' => today()->toDateString()]],
        ]);

        $response = $this->actingAs($this->admin)->get($url);

        $response->assertStatus(200);
        $response->assertSee($todayPayment->payment_number);
        $response->assertDontSee($oldPayment->payment_number);
    }
}
