<?php

namespace Tests\Unit;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_attribute_includes_middle_name(): void
    {
        $patient = Patient::factory()->make([
            'first_name'  => 'Juan',
            'middle_name' => 'dela',
            'last_name'   => 'Cruz',
        ]);

        $this->assertEquals('Juan dela Cruz', $patient->full_name);
    }

    public function test_full_name_attribute_without_middle_name(): void
    {
        $patient = Patient::factory()->make([
            'first_name'  => 'Maria',
            'middle_name' => null,
            'last_name'   => 'Santos',
        ]);

        $this->assertEquals('Maria Santos', $patient->full_name);
    }

    public function test_age_attribute_is_calculated_correctly(): void
    {
        $patient = Patient::factory()->make([
            'date_of_birth' => now()->subYears(30)->format('Y-m-d'),
        ]);

        $this->assertEquals(30, $patient->age);
    }
}
