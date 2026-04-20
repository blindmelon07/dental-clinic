<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    public function definition(): array
    {
        $qty   = fake()->numberBetween(1, 3);
        $price = fake()->randomFloat(2, 200, 5000);

        return [
            'invoice_id'  => Invoice::factory(),
            'service_id'  => null,
            'description' => fake()->randomElement(['Dental Cleaning', 'Tooth Extraction', 'Root Canal', 'Dental Filling', 'Consultation']),
            'quantity'    => $qty,
            'unit_price'  => $price,
            'total'       => $qty * $price,
        ];
    }
}
