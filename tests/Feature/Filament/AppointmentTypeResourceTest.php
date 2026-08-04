<?php

namespace Tests\Feature\Filament;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTypeResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/appointment-types');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/appointment-types/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $type = AppointmentType::first();

        $response = $this->actingAs($this->admin)->get("/admin/appointment-types/{$type->id}/edit");
        $response->assertStatus(200);
    }

    public function test_migration_seeds_the_original_appointment_types(): void
    {
        $names = AppointmentType::orderBy('sort_order')->pluck('name')->all();

        $this->assertSame(
            ['Consultation', 'Follow-up', 'Emergency', 'Cleaning', 'Procedure', 'X-Ray'],
            $names,
        );

        $this->assertTrue(AppointmentType::where('name', 'Cleaning')->value('is_cleaning'));
    }

    public function test_completing_a_cleaning_type_appointment_updates_next_cleaning_due(): void
    {
        $patient = Patient::factory()->create(['next_cleaning_due' => null]);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'type'       => 'Cleaning',
            'status'     => AppointmentStatus::Confirmed->value,
        ]);

        $appointment->update(['status' => AppointmentStatus::Completed->value]);

        $this->assertNotNull($patient->refresh()->next_cleaning_due);
    }
}
