<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\MedicineResource\Pages\CreateMedicine;
use App\Filament\Resources\MedicineResource\Pages\EditMedicine;
use App\Models\Medicine;
use App\Models\MedicineForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicineResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/medicines');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/medicines/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_medicine(): void
    {
        $form = MedicineForm::where('slug', 'tablet')->first();

        Livewire::actingAs($this->admin)
            ->test(CreateMedicine::class)
            ->fillForm([
                'name'              => 'Amoxicillin',
                'medicine_form_id'  => $form->id,
                'unit'              => 'pcs',
                'current_stock'     => 100,
                'minimum_stock'     => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicines', ['name' => 'Amoxicillin', 'current_stock' => 100]);
    }

    public function test_can_edit_a_medicine(): void
    {
        $form = MedicineForm::where('slug', 'tablet')->first();
        $medicine = Medicine::create([
            'name' => 'Ibuprofen', 'medicine_form_id' => $form->id, 'current_stock' => 5, 'minimum_stock' => 10,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditMedicine::class, ['record' => $medicine->getRouteKey()])
            ->fillForm(['current_stock' => 50])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(50, $medicine->fresh()->current_stock);
    }

    public function test_can_delete_a_medicine(): void
    {
        $medicine = Medicine::create(['name' => 'Throwaway']);

        Livewire::actingAs($this->admin)
            ->test(EditMedicine::class, ['record' => $medicine->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($medicine);
    }
}
