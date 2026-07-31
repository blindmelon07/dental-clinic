<?php

namespace Tests\Feature\Filament;

use App\Models\MedicineForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
