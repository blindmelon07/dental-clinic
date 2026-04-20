<?php

namespace Tests\Feature\Filament;

use App\Models\DentalRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentalRecordResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/dental-records');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dental-records/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $record = DentalRecord::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/dental-records/{$record->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $record = DentalRecord::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/dental-records/{$record->id}/edit");
        $response->assertStatus(200);
    }
}
