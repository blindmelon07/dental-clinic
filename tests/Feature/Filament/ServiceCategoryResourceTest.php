<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ServiceCategoryResource\Pages\CreateServiceCategory;
use App\Filament\Resources\ServiceCategoryResource\Pages\EditServiceCategory;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceCategoryResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/service-categories');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/service-categories/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_service_category(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateServiceCategory::class)
            ->fillForm(['name' => 'Orthodontics'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('service_categories', ['name' => 'Orthodontics', 'slug' => 'orthodontics']);
    }

    public function test_creating_a_category_with_a_colliding_slug_does_not_error(): void
    {
        ServiceCategory::factory()->create(['name' => 'Orthodontics', 'slug' => 'orthodontics']);

        Livewire::actingAs($this->admin)
            ->test(CreateServiceCategory::class)
            ->fillForm(['name' => 'Orthodontics'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('service_categories', ['name' => 'Orthodontics', 'slug' => 'orthodontics-2']);
    }

    public function test_can_edit_a_service_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->admin)
            ->test(EditServiceCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New Name', $category->fresh()->name);
        $this->assertSame('new-name', $category->fresh()->slug);
    }

    public function test_renaming_to_a_colliding_slug_does_not_error(): void
    {
        ServiceCategory::factory()->create(['name' => 'Orthodontics', 'slug' => 'orthodontics']);
        $category = ServiceCategory::factory()->create(['name' => 'Other']);

        Livewire::actingAs($this->admin)
            ->test(EditServiceCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Orthodontics'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('orthodontics-2', $category->fresh()->slug);
    }

    public function test_can_delete_a_service_category(): void
    {
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditServiceCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($category);
    }
}
