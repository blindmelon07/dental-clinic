<?php

namespace Tests\Feature\Filament;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/appointments');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/appointments/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/appointments/{$appointment->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $appointment = Appointment::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/appointments/{$appointment->id}/edit");
        $response->assertStatus(200);
    }

    public function test_confirm_action_updates_status(): void
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::Pending->value]);

        $appointment->confirm();

        $this->assertDatabaseHas('appointments', [
            'id'     => $appointment->id,
            'status' => AppointmentStatus::Confirmed->value,
        ]);
        $this->assertNotNull($appointment->fresh()->confirmed_at);
    }

    public function test_cancel_action_updates_status(): void
    {
        $appointment = Appointment::factory()->confirmed()->create();

        $appointment->cancel('Patient request');

        $this->assertDatabaseHas('appointments', [
            'id'                  => $appointment->id,
            'status'              => AppointmentStatus::Cancelled->value,
            'cancellation_reason' => 'Patient request',
        ]);
        $this->assertNotNull($appointment->fresh()->cancelled_at);
    }
}
