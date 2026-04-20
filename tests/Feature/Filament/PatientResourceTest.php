<?php

namespace Tests\Feature\Filament;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp(); // creates roles via TestCase::setUp()

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super_admin');
    }

    public function test_list_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patients');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/patients/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/patients/{$patient->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/patients/{$patient->id}/edit");
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/admin/patients');
        $response->assertRedirect();
    }
}
