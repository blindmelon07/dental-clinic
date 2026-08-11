<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\MedicineFormResource\Pages\CreateMedicineForm;
use App\Filament\Resources\MedicineFormResource\Pages\EditMedicineForm;
use App\Models\MedicineForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicineFormResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/medicine-forms');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/medicine-forms/create');
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $form = MedicineForm::first();

        $response = $this->actingAs($this->admin)->get("/admin/medicine-forms/{$form->id}/edit");
        $response->assertStatus(200);
    }

    public function test_medicine_create_form_has_no_inline_create_options(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/medicines/create');
        $response->assertStatus(200);
        $response->assertDontSee('createOption', false);
    }

    public function test_can_create_a_medicine_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateMedicineForm::class)
            ->fillForm(['name' => 'Lozenge'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_forms', ['name' => 'Lozenge', 'slug' => 'lozenge']);
    }

    public function test_creating_a_form_matching_a_seeded_slug_does_not_error(): void
    {
        // The migration pre-seeds a 'Tablet' row with slug 'tablet'.
        Livewire::actingAs($this->admin)
            ->test(CreateMedicineForm::class)
            ->fillForm(['name' => 'Tablet'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_forms', ['name' => 'Tablet', 'slug' => 'tablet-2']);
    }

    public function test_can_edit_a_medicine_form(): void
    {
        $form = MedicineForm::where('slug', 'other')->first();

        Livewire::actingAs($this->admin)
            ->test(EditMedicineForm::class, ['record' => $form->getRouteKey()])
            ->fillForm(['name' => 'Miscellaneous'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('miscellaneous', $form->fresh()->slug);
    }

    public function test_can_delete_a_medicine_form(): void
    {
        $form = MedicineForm::create(['name' => 'Throwaway', 'slug' => 'throwaway']);

        Livewire::actingAs($this->admin)
            ->test(EditMedicineForm::class, ['record' => $form->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($form);
    }
}
