<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\DentistResource\Pages\CreateDentist;
use App\Filament\Resources\DentistResource\Pages\EditDentist;
use App\Models\Clinic;
use App\Models\Dentist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DentistResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/dentists');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dentists/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $dentist = Dentist::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/dentists/{$dentist->id}/edit");
        $response->assertStatus(200);
    }

    public function test_can_create_a_dentist(): void
    {
        Clinic::factory()->create();
        $user = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreateDentist::class)
            ->fillForm([
                'user_id'           => $user->id,
                'license_number'    => 'LIC-000123',
                'consultation_fee'  => 500,
                // The Weekly Schedule repeater starts with one empty, required row by
                // default; it must be filled in (or cleared) or the form won't validate.
                'schedules'         => [
                    ['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00', 'is_available' => true],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('dentists', ['user_id' => $user->id, 'license_number' => 'LIC-000123']);
    }

    public function test_can_edit_a_dentist(): void
    {
        $dentist = Dentist::factory()->create(['specialization' => 'General Dentistry']);

        Livewire::actingAs($this->admin)
            ->test(EditDentist::class, ['record' => $dentist->getRouteKey()])
            ->fillForm(['specialization' => 'Orthodontics'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Orthodontics', $dentist->fresh()->specialization);
    }

    public function test_can_delete_a_dentist(): void
    {
        $dentist = Dentist::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditDentist::class, ['record' => $dentist->getRouteKey()])
            ->callAction('delete');

        // Dentist uses SoftDeletes: the row still exists with deleted_at set.
        $this->assertSoftDeleted($dentist);
    }
}
