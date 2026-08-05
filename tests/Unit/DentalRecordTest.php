<?php

namespace Tests\Unit;

use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentalRecordTest extends TestCase
{
    use RefreshDatabase;

    private function makeRecordWithDiagnosis(): DentalRecord
    {
        $service = Service::factory()->create(['is_active' => true]);

        return DentalRecord::factory()->create([
            'diagnosis' => $service->display_name,
        ]);
    }

    public function test_create_invoice_generates_a_draft_invoice_from_diagnosis_services(): void
    {
        $record = $this->makeRecordWithDiagnosis();

        $invoice = $record->createInvoice();

        $this->assertSame($record->id, $invoice->dental_record_id);
        $this->assertCount(1, $invoice->items);
    }

    public function test_create_invoice_restores_a_soft_deleted_invoice_for_the_same_record(): void
    {
        $record = $this->makeRecordWithDiagnosis();

        $first = $record->createInvoice();
        $first->delete();

        // dental_record_id is unique even across soft-deleted rows, so regenerating
        // must reuse/restore that row rather than insert a new one (which would
        // violate invoices_dental_record_id_unique).
        $second = $record->createInvoice();

        $this->assertSame($first->id, $second->id);
        $this->assertFalse($second->fresh()->trashed());
        $this->assertSame(1, Invoice::withTrashed()->where('dental_record_id', $record->id)->count());
    }

    public function test_create_invoice_throws_when_an_active_invoice_already_exists(): void
    {
        $record = $this->makeRecordWithDiagnosis();
        $record->createInvoice();

        $this->expectException(\RuntimeException::class);
        $record->createInvoice();
    }
}
