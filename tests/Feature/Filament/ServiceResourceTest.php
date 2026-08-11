<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/services');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/services/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $service = Service::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/services/{$service->id}/edit");
        $response->assertStatus(200);
    }

    public function test_can_create_a_service(): void
    {
        Clinic::factory()->create();
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreateService::class)
            ->fillForm([
                'service_category_id' => $category->id,
                'name'                => 'Tooth Extraction',
                'price'               => 1500,
                'duration_minutes'    => 30,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', ['name' => 'Tooth Extraction', 'slug' => 'tooth-extraction']);
    }

    public function test_creating_a_service_with_a_colliding_slug_does_not_error(): void
    {
        // Regression test: creating "Tooth Extraction 2." used to collide with an
        // existing "tooth-extraction-2" slug and throw a 500 (services_slug_unique).
        $category = ServiceCategory::factory()->create();
        Service::factory()->create(['service_category_id' => $category->id, 'slug' => 'tooth-extraction-2']);

        Livewire::actingAs($this->admin)
            ->test(CreateService::class)
            ->fillForm([
                'service_category_id' => $category->id,
                'name'                => 'Tooth Extraction 2.',
                'price'               => 1500,
                'duration_minutes'    => 30,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('services', ['name' => 'Tooth Extraction 2.', 'slug' => 'tooth-extraction-2-2']);
    }

    public function test_can_edit_a_service(): void
    {
        $service = Service::factory()->create(['price' => 100]);

        Livewire::actingAs($this->admin)
            ->test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['price' => 250])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(250, $service->fresh()->price);
    }

    public function test_can_delete_a_service(): void
    {
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditService::class, ['record' => $service->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($service);
    }
}
