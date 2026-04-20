<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        static $counter = 0;
        $counter++;

        $subtotal = fake()->randomFloat(2, 500, 10000);
        $taxRate  = 0;
        $discount = 0;
        $total    = $subtotal;

        return [
            'invoice_number'  => 'INV-' . date('Ymd') . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'clinic_id'       => Clinic::factory(),
            'patient_id'      => Patient::factory(),
            'appointment_id'  => null,
            'status'          => InvoiceStatus::Draft->value,
            'invoice_date'    => today()->format('Y-m-d'),
            'due_date'        => today()->addDays(30)->format('Y-m-d'),
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'tax_rate'        => $taxRate,
            'tax_amount'      => 0,
            'total'           => $total,
            'amount_paid'     => 0,
            'balance_due'     => $total,
        ];
    }

    public function paid(): static
    {
        return $this->state(function (array $attrs) {
            return [
                'status'      => InvoiceStatus::Paid->value,
                'amount_paid' => $attrs['total'],
                'balance_due' => 0,
                'paid_at'     => now(),
            ];
        });
    }
}
