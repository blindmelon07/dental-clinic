<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\EquipmentCategoryResource\Pages\CreateEquipmentCategory;
use App\Filament\Resources\EquipmentCategoryResource\Pages\EditEquipmentCategory;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EquipmentCategoryResourceTest extends TestCase
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
        EquipmentCategory::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/equipment-categories');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/equipment-categories/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $category = EquipmentCategory::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/equipment-categories/{$category->id}/edit");
        $response->assertStatus(200);
    }

    public function test_equipment_resource_shows_related_category(): void
    {
        $category = EquipmentCategory::factory()->create(['name' => 'Imaging']);
        Equipment::create(['name' => 'Test Scanner', 'equipment_category_id' => $category->id, 'quantity' => 1]);

        $response = $this->actingAs($this->admin)->get('/admin/equipment');
        $response->assertStatus(200);
        $response->assertSee('Imaging', false);
    }

    public function test_can_create_an_equipment_category(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateEquipmentCategory::class)
            ->fillForm(['name' => 'Preventive'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('equipment_categories', ['name' => 'Preventive', 'slug' => 'preventive']);
    }

    public function test_creating_a_category_matching_a_seeded_slug_does_not_error(): void
    {
        // The migration pre-seeds an 'Other' row with slug 'other'.
        Livewire::actingAs($this->admin)
            ->test(CreateEquipmentCategory::class)
            ->fillForm(['name' => 'Other'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('equipment_categories', ['name' => 'Other', 'slug' => 'other-2']);
    }

    public function test_can_edit_an_equipment_category(): void
    {
        $category = EquipmentCategory::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->admin)
            ->test(EditEquipmentCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('new-name', $category->fresh()->slug);
    }

    public function test_can_delete_an_equipment_category(): void
    {
        $category = EquipmentCategory::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditEquipmentCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($category);
    }
}
