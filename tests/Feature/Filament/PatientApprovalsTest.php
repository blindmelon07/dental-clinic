<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\PatientApprovals;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientApprovalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_approving_a_registration_without_a_city_does_not_crash(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('super_admin');

        $clinic = Clinic::factory()->create();

        // Mirrors a real self-registration: RegisteredUserController validates
        // 'city' as nullable, so a sign-up that skipped it stores pending data
        // with no city at all.
        $pendingUser = User::factory()->create([
            'is_active'  => false,
            'clinic_id'  => $clinic->id,
            'pending_patient_data' => [
                'first_name'    => 'Jane',
                'last_name'     => 'Doe',
                'date_of_birth' => '1995-05-05',
                'gender'        => 'female',
                'phone'         => '09171234567',
                'address'       => '123 Main St',
            ],
        ]);
        $pendingUser->assignRole('patient');

        Livewire::actingAs($admin)
            ->test(PatientApprovals::class)
            ->callTableAction('approve', $pendingUser)
            ->assertSuccessful();

        $this->assertDatabaseHas('patients', [
            'user_id'    => $pendingUser->id,
            'first_name' => 'Jane',
            'city'       => null,
        ]);

        $this->assertTrue($pendingUser->fresh()->is_active);
    }
}
