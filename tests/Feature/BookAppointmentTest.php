<?php

namespace Tests\Feature;

use App\Livewire\Patient\BookAppointment;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private Dentist $dentist;
    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dentist = Dentist::factory()->create();
        $this->date = now()->addDays(7)->toDateString();

        DentistSchedule::create([
            'dentist_id'   => $this->dentist->id,
            'day_of_week'  => (int) date('N', strtotime($this->date)),
            'start_time'   => '09:00',
            'end_time'     => '12:00',
            'is_available' => true,
        ]);
    }

    private function patientUser(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('patient');
        Patient::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function bookingFor(User $user, Service $service)
    {
        return Livewire::actingAs($user)
            ->test(BookAppointment::class)
            ->call('selectService', $service->id)
            ->call('selectDentist', $this->dentist->id)
            ->set('selectedDate', $this->date);
    }

    public function test_a_slot_taken_while_another_patient_was_confirming_is_rejected(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 30]);

        // Both patients load the same free slot before either confirms.
        $first = $this->bookingFor($this->patientUser(), $service)->call('selectSlot', $this->date, '10:00');
        $second = $this->bookingFor($this->patientUser(), $service)->call('selectSlot', $this->date, '10:00');

        $first->call('confirm')->assertSet('bookingComplete', true);
        $second->call('confirm')
            ->assertSet('bookingComplete', false)
            ->assertHasErrors('booking');

        $this->assertSame(1, Appointment::where('dentist_id', $this->dentist->id)->count());
    }

    public function test_a_longer_appointment_blocks_every_slot_it_overlaps(): void
    {
        $hourLong = Service::factory()->create(['duration_minutes' => 60]);
        $halfHour = Service::factory()->create(['duration_minutes' => 30]);

        $this->bookingFor($this->patientUser(), $hourLong)
            ->call('selectSlot', $this->date, '10:00')
            ->call('confirm')
            ->assertSet('bookingComplete', true);

        $slots = $this->bookingFor($this->patientUser(), $halfHour)->instance()->availableSlots;

        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('10:30', $slots);
        $this->assertContains('09:30', $slots);
        $this->assertContains('11:00', $slots);
    }

    public function test_a_service_with_no_duration_still_lists_slots(): void
    {
        // A 0-minute step used to loop forever and exhaust memory.
        $service = Service::factory()->create(['duration_minutes' => 0]);

        $slots = $this->bookingFor($this->patientUser(), $service)->instance()->availableSlots;

        $this->assertSame(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'], $slots);
    }
}
