<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\DentalRecord;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class DentalRecordSeeder extends Seeder
{
    public function run(): void
    {
        $dentist = Dentist::first();
        $patients = Patient::all();

        if (! $dentist || $patients->isEmpty()) {
            $this->command->warn('No dentist or patients found — run DatabaseSeeder first.');
            return;
        }

        $records = [
            [
                'patient'          => $patients->get(0),
                'visit_date'       => today()->subWeeks(3),
                'chief_complaint'  => 'Persistent toothache on the lower right molar for the past few days.',
                'diagnosis'        => ['General Dentistry – Dental Consultation', 'Panoramic – Standard'],
                'treatment_plan'   => 'Panoramic x-ray to assess the extent of decay before deciding on filling vs. extraction.',
                'treatment_done'   => 'Panoramic radiograph taken; consultation and oral exam completed.',
                'prescription'     => 'Amoxicillin 500mg — 3x daily for 5 days; Mefenamic acid 500mg for pain as needed.',
                'notes'            => 'Patient reports sharp pain when chewing on the right side.',
                'next_visit'       => '1 week',
                'invoice'          => 'paid_today',
            ],
            [
                'patient'          => $patients->get(1) ?? $patients->get(0),
                'visit_date'       => today()->subWeeks(2),
                'chief_complaint'  => 'Routine dental check-up and cleaning.',
                'diagnosis'        => ['General Dentistry – Teeth Cleaning (Prophylaxis)'],
                'treatment_plan'   => 'Standard scaling and polishing.',
                'treatment_done'   => 'Scaling and polishing completed; mild plaque buildup removed.',
                'prescription'     => null,
                'notes'            => 'No cavities found. Good oral hygiene overall.',
                'next_visit'       => '6 months',
                'invoice'          => 'paid',
            ],
            [
                'patient'          => $patients->get(2) ?? $patients->get(0),
                'visit_date'       => today()->subMonth(),
                'chief_complaint'  => 'Chipped front tooth after a minor accident.',
                'diagnosis'        => ['Cosmetic Dentistry – Dental Bonding'],
                'treatment_plan'   => 'Composite bonding to restore the chipped edge.',
                'treatment_done'   => 'Composite bonding applied to tooth #8; shade-matched and polished.',
                'prescription'     => null,
                'notes'            => 'Patient satisfied with the immediate cosmetic result.',
                'next_visit'       => '2 weeks',
                'invoice'          => 'partial',
            ],
            [
                'patient'          => $patients->get(5) ?? $patients->get(0),
                'visit_date'       => today()->subDays(10),
                'chief_complaint'  => 'Jaw pain and a clicking sound when opening the mouth.',
                'diagnosis'        => ['Panoramic – TMJ (Open and Close)', 'Cephalometric – Latero / Lateral (LL)'],
                'treatment_plan'   => 'Further TMJ imaging and possible night guard fitting.',
                'treatment_done'   => null,
                'prescription'     => 'Ibuprofen 400mg as needed for jaw pain.',
                'notes'            => 'Referred for follow-up TMJ assessment.',
                'next_visit'       => '1 month',
                'invoice'          => null,
            ],
            [
                'patient'          => $patients->get(6) ?? $patients->get(0),
                'visit_date'       => today()->subDays(5),
                'chief_complaint'  => 'Severe tooth decay with lingering sensitivity to hot and cold.',
                'diagnosis'        => ['Restorative Dentistry – Root Canal Treatment'],
                'treatment_plan'   => 'Root canal therapy over two sessions.',
                'treatment_done'   => 'Session 1: pulp removal and canal cleaning completed.',
                'prescription'     => 'Ibuprofen 400mg every 6 hours as needed.',
                'notes'            => 'Second session scheduled to complete canal filling and crown fitting.',
                'next_visit'       => '2 weeks',
                'invoice'          => null,
            ],
            [
                'patient'          => $patients->get(3) ?? $patients->get(0),
                'visit_date'       => today()->subDay(),
                'chief_complaint'  => 'Impacted wisdom tooth causing pain and swelling.',
                'diagnosis'        => ['General Dentistry – Tooth Extraction (Surgical)'],
                'treatment_plan'   => 'Surgical extraction scheduled for next week.',
                'treatment_done'   => null,
                'prescription'     => 'Amoxicillin 500mg 3x daily for 5 days prior to surgery.',
                'notes'            => 'Mild swelling on the right side of the jaw.',
                'next_visit'       => '1 week',
                'invoice'          => null,
            ],
        ];

        $invoiceCount = 0;

        foreach ($records as $data) {
            $record = DentalRecord::create([
                'patient_id'                 => $data['patient']->id,
                'dentist_id'                 => $dentist->id,
                'appointment_id'             => null,
                'visit_date'                 => $data['visit_date'],
                'chief_complaint'            => $data['chief_complaint'],
                'diagnosis'                  => implode(', ', $data['diagnosis']),
                'treatment_plan'             => $data['treatment_plan'],
                'treatment_done'             => $data['treatment_done'],
                'prescription'               => $data['prescription'],
                'notes'                      => $data['notes'],
                'next_visit_recommendation'  => $data['next_visit'],
            ]);

            if ($data['invoice'] === null) {
                continue;
            }

            $invoice = $record->createInvoice();
            $invoiceCount++;

            if ($data['invoice'] === 'draft') {
                continue;
            }

            $isPartial = $data['invoice'] === 'partial';
            $paidToday = $data['invoice'] === 'paid_today';
            $amount = $isPartial ? round($invoice->total * 0.4, 2) : $invoice->total;
            $paidAt = $paidToday ? now() : $data['visit_date']->copy()->addHours(2);

            Payment::create([
                'payment_number'   => Payment::generateNumber(),
                'invoice_id'       => $invoice->id,
                'patient_id'       => $invoice->patient_id,
                'amount'           => $amount,
                'payment_method'   => PaymentMethod::Cash->value,
                'reference_number' => null,
                'notes'            => 'Seeded demo payment.',
                'paid_at'          => $paidAt,
            ]);

            $invoice->recalculate();
            $invoice->update([
                'status'  => $isPartial ? InvoiceStatus::PartiallyPaid : InvoiceStatus::Paid,
                'paid_at' => $isPartial ? null : $paidAt,
            ]);
        }

        $this->command->info('✅ Dental records seeded!');
        $this->command->table(
            ['What was seeded', 'Count'],
            [
                ['Dental records', count($records)],
                ['Invoices generated', $invoiceCount],
            ]
        );
    }
}
