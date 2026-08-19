<?php

namespace Tests\Feature\Filament;

use App\Enums\EquipmentStatus;
use App\Filament\Resources\EquipmentResource\Pages\CreateEquipment;
use App\Filament\Resources\EquipmentResource\Pages\EditEquipment;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/equipment');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/equipment/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $equipment = Equipment::create(['name' => 'Dental Chair', 'quantity' => 1]);

        $response = $this->actingAs($this->admin)->get("/admin/equipment/{$equipment->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $equipment = Equipment::create(['name' => 'Autoclave', 'quantity' => 1]);

        $response = $this->actingAs($this->admin)->get("/admin/equipment/{$equipment->id}/edit");
        $response->assertStatus(200);
    }

    public function test_can_create_equipment(): void
    {
        $category = EquipmentCategory::factory()->create(['name' => 'Imaging']);

        Livewire::actingAs($this->admin)
            ->test(CreateEquipment::class)
            ->fillForm([
                'name'                   => '3D X-Ray Scanner',
                'equipment_category_id'  => $category->id,
                'status'                 => EquipmentStatus::Operational->value,
                'quantity'               => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('equipment', ['name' => '3D X-Ray Scanner', 'equipment_category_id' => $category->id]);
    }

    public function test_duplicate_serial_number_shows_a_validation_error(): void
    {
        Equipment::create(['name' => 'Autoclave', 'serial_number' => 'SN-001', 'quantity' => 1]);

        Livewire::actingAs($this->admin)
            ->test(CreateEquipment::class)
            ->fillForm([
                'name'          => 'Second Autoclave',
                'serial_number' => 'SN-001',
                'quantity'      => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['serial_number']);
    }

    public function test_can_edit_equipment(): void
    {
        $equipment = Equipment::create(['name' => 'Ultrasonic Scaler', 'quantity' => 1]);

        Livewire::actingAs($this->admin)
            ->test(EditEquipment::class, ['record' => $equipment->getRouteKey()])
            ->fillForm(['status' => EquipmentStatus::UnderMaintenance->value])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(EquipmentStatus::UnderMaintenance, $equipment->fresh()->status);
    }

    public function test_can_delete_equipment(): void
    {
        $equipment = Equipment::create(['name' => 'Throwaway', 'quantity' => 1]);

        Livewire::actingAs($this->admin)
            ->test(EditEquipment::class, ['record' => $equipment->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($equipment);
    }

    public function test_maintenance_due_filter_narrows_the_list(): void
    {
        $overdue = Equipment::create(['name' => 'Overdue Compressor', 'quantity' => 1, 'next_maintenance_date' => now()->subDay()]);
        $fine = Equipment::create(['name' => 'Fine Chair', 'quantity' => 1, 'next_maintenance_date' => now()->addMonth()]);

        Livewire::actingAs($this->admin)
            ->test(\App\Filament\Resources\EquipmentResource\Pages\ListEquipment::class)
            ->filterTable('maintenance_due', true)
            ->assertCanSeeTableRecords([$overdue])
            ->assertCanNotSeeTableRecords([$fine]);
    }
}
