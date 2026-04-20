<?php

namespace Tests\Feature\Filament;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
