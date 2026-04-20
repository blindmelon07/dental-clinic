<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_number_returns_formatted_string(): void
    {
        $number = Appointment::generateNumber();

        $this->assertMatchesRegularExpression('/^APT-\d{8}-\d{4}$/', $number);
    }

    public function test_confirm_sets_status_and_timestamp(): void
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Pending->value]);

        $appointment->confirm();
        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertNotNull($appointment->confirmed_at);
    }

    public function test_cancel_sets_status_reason_and_timestamp(): void
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Pending->value]);

        $appointment->cancel('No longer needed');
        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::Cancelled, $appointment->status);
        $this->assertEquals('No longer needed', $appointment->cancellation_reason);
        $this->assertNotNull($appointment->cancelled_at);
    }

    public function test_complete_sets_status_and_timestamp(): void
    {
        $appointment = Appointment::factory()->confirmed()->create();

        $appointment->complete();
        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::Completed, $appointment->status);
        $this->assertNotNull($appointment->completed_at);
    }
}
