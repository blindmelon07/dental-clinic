<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\XrayTypeResource\Pages\CreateXrayType;
use App\Filament\Resources\XrayTypeResource\Pages\EditXrayType;
use App\Models\User;
use App\Models\XrayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class XrayTypeResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/xray-types');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/xray-types/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $type = XrayType::first();

        $response = $this->actingAs($this->admin)->get("/admin/xray-types/{$type->id}/edit");
        $response->assertStatus(200);
    }

    public function test_can_create_an_xray_type(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateXrayType::class)
            ->fillForm(['name' => 'Cephalometric'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('xray_types', ['name' => 'Cephalometric']);
    }

    public function test_creating_a_duplicate_name_shows_a_validation_error(): void
    {
        // Migration pre-seeds a 'Panoramic' row; name has a unique() rule.
        Livewire::actingAs($this->admin)
            ->test(CreateXrayType::class)
            ->fillForm(['name' => 'Panoramic'])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_can_edit_an_xray_type(): void
    {
        $type = XrayType::where('name', 'Occlusal')->first();

        Livewire::actingAs($this->admin)
            ->test(EditXrayType::class, ['record' => $type->getRouteKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($type->fresh()->is_active);
    }

    public function test_can_delete_an_xray_type(): void
    {
        $type = XrayType::create(['name' => 'Throwaway']);

        Livewire::actingAs($this->admin)
            ->test(EditXrayType::class, ['record' => $type->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($type);
    }
}
