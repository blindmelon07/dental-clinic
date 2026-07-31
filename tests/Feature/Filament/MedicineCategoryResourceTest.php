<?php

namespace Tests\Feature\Filament;

use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineCategoryResourceTest extends TestCase
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
        MedicineCategory::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/admin/medicine-categories');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/medicine-categories/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $category = MedicineCategory::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/medicine-categories/{$category->id}/edit");
        $response->assertStatus(200);
    }

    public function test_medicine_resource_shows_related_category(): void
    {
        $category = MedicineCategory::factory()->create(['name' => 'Antibiotic']);
        Medicine::create(['name' => 'Test Medicine', 'medicine_category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->get('/admin/medicines');
        $response->assertStatus(200);
        $response->assertSee('Antibiotic', false);
    }
}
