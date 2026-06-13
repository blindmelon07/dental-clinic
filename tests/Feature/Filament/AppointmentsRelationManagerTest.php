<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\PatientResource\Pages\ViewPatient;
use App\Filament\Resources\PatientResource\RelationManagers\AppointmentsRelationManager;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentsRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');
    }

    public function test_table_renders_with_appointments(): void
    {
        $patient = Patient::factory()->create();
        Appointment::factory()->for($patient)->create();

        $this->actingAs($this->admin);

        Livewire::test(AppointmentsRelationManager::class, [
            'ownerRecord' => $patient,
            'pageClass'   => ViewPatient::class,
        ])->assertOk();
    }
}
