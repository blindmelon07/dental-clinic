<?php

namespace Tests\Unit;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_number_returns_formatted_string(): void
    {
        $number = Invoice::generateNumber();

        $this->assertMatchesRegularExpression('/^INV-\d{8}-\d{4}$/', $number);
    }

    public function test_recalculate_updates_totals_from_items(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_rate'        => 0,
            'discount_amount' => 0,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity'   => 2,
            'unit_price' => 1000,
            'total'      => 2000,
        ]);
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity'   => 1,
            'unit_price' => 500,
            'total'      => 500,
        ]);

        $invoice->recalculate();
        $invoice->refresh();

        $this->assertEquals(2500, $invoice->subtotal);
        $this->assertEquals(2500, $invoice->total);
        $this->assertEquals(2500, $invoice->balance_due);
        $this->assertEquals(0, $invoice->amount_paid);
    }

    public function test_recalculate_applies_tax_correctly(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_rate'        => 12,
            'discount_amount' => 0,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity'   => 1,
            'unit_price' => 1000,
            'total'      => 1000,
        ]);

        $invoice->recalculate();
        $invoice->refresh();

        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals(120, $invoice->tax_amount);
        $this->assertEquals(1120, $invoice->total);
    }

    public function test_recalculate_applies_discount_correctly(): void
    {
        $invoice = Invoice::factory()->create([
            'tax_rate'        => 0,
            'discount_amount' => 100,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity'   => 1,
            'unit_price' => 1000,
            'total'      => 1000,
        ]);

        $invoice->recalculate();
        $invoice->refresh();

        $this->assertEquals(1000, $invoice->subtotal);
        $this->assertEquals(900, $invoice->total);
        $this->assertEquals(900, $invoice->balance_due);
    }
}
