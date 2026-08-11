<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\MedicineCategoryResource\Pages\CreateMedicineCategory;
use App\Filament\Resources\MedicineCategoryResource\Pages\EditMedicineCategory;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_can_create_a_medicine_category(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateMedicineCategory::class)
            ->fillForm(['name' => 'Decongestant'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_categories', ['name' => 'Decongestant', 'slug' => 'decongestant']);
    }

    public function test_creating_a_category_matching_a_seeded_slug_does_not_error(): void
    {
        // The migration pre-seeds an 'Other' row with slug 'other'.
        Livewire::actingAs($this->admin)
            ->test(CreateMedicineCategory::class)
            ->fillForm(['name' => 'Other'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_categories', ['name' => 'Other', 'slug' => 'other-2']);
    }

    public function test_can_edit_a_medicine_category(): void
    {
        $category = MedicineCategory::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs($this->admin)
            ->test(EditMedicineCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('new-name', $category->fresh()->slug);
    }

    public function test_can_delete_a_medicine_category(): void
    {
        $category = MedicineCategory::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditMedicineCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($category);
    }
}
