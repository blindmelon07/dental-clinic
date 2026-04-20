<?php

namespace Tests\Feature\Filament;

use App\Models\Dentist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
