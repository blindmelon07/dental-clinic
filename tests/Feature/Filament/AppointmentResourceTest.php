<?php

namespace Tests\Feature\Filament;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages\CreateAppointment;
use App\Filament\Resources\AppointmentResource\Pages\EditAppointment;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_can_create_an_appointment(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreateAppointment::class)
            ->fillForm([
                'patient_id'       => $patient->id,
                'dentist_id'       => $dentist->id,
                'service_id'       => $service->id,
                'type'             => 'Consultation',
                'appointment_date' => today()->addDay()->format('Y-m-d'),
                'start_time'       => '09:00',
                'end_time'         => '09:30',
                'status'           => AppointmentStatus::Pending->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('appointments', ['patient_id' => $patient->id, 'dentist_id' => $dentist->id]);
    }

    public function test_can_edit_an_appointment(): void
    {
        $appointment = Appointment::factory()->create(['chief_complaint' => 'Toothache']);

        Livewire::actingAs($this->admin)
            ->test(EditAppointment::class, ['record' => $appointment->getRouteKey()])
            ->fillForm(['chief_complaint' => 'Follow-up checkup'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Follow-up checkup', $appointment->fresh()->chief_complaint);
    }

    public function test_can_delete_an_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditAppointment::class, ['record' => $appointment->getRouteKey()])
            ->callAction('delete');

        // Appointment uses SoftDeletes: the row still exists with deleted_at set.
        $this->assertSoftDeleted($appointment);
    }
}
