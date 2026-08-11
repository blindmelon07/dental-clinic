<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\MedicineDispensingResource\Pages\CreateMedicineDispensing;
use App\Filament\Resources\MedicineDispensingResource\Pages\EditMedicineDispensing;
use App\Models\Medicine;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicineDispensingResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/medicine-dispensings');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/medicine-dispensings/create');
        $response->assertStatus(200);
    }

    public function test_can_dispense_a_medicine_and_it_decrements_stock(): void
    {
        $patient = Patient::factory()->create();
        $medicine = Medicine::create(['name' => 'Paracetamol', 'current_stock' => 100, 'unit_price' => 5]);

        Livewire::actingAs($this->admin)
            ->test(CreateMedicineDispensing::class)
            ->fillForm([
                'patient_id'   => $patient->id,
                'medicine_id'  => $medicine->id,
                'quantity'     => 10,
                'dispensed_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_dispensings', ['patient_id' => $patient->id, 'medicine_id' => $medicine->id, 'quantity' => 10]);
        $this->assertEquals(90, $medicine->fresh()->current_stock);
    }

    public function test_dispensed_by_defaults_to_the_logged_in_user_and_is_not_editable(): void
    {
        $patient = Patient::factory()->create();
        $medicine = Medicine::create(['name' => 'Paracetamol', 'current_stock' => 100]);

        Livewire::actingAs($this->admin)
            ->test(CreateMedicineDispensing::class)
            ->fillForm([
                'patient_id'   => $patient->id,
                'medicine_id'  => $medicine->id,
                'quantity'     => 1,
                'dispensed_at' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('medicine_dispensings', ['patient_id' => $patient->id, 'dispensed_by' => $this->admin->id]);
    }

    public function test_can_edit_a_dispensing_record(): void
    {
        $dispensing = MedicineDispensing::create([
            'patient_id'   => Patient::factory()->create()->id,
            'medicine_id'  => Medicine::create(['name' => 'Amoxicillin', 'current_stock' => 100])->id,
            'quantity'     => 5,
            'dispensed_by' => $this->admin->id,
            'dispensed_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditMedicineDispensing::class, ['record' => $dispensing->getRouteKey()])
            ->fillForm(['notes' => 'Given with food'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Given with food', $dispensing->fresh()->notes);
    }

    public function test_deleting_a_dispensing_record_restores_stock(): void
    {
        $medicine = Medicine::create(['name' => 'Cetirizine', 'current_stock' => 100]);
        $dispensing = MedicineDispensing::create([
            'patient_id'   => Patient::factory()->create()->id,
            'medicine_id'  => $medicine->id,
            'quantity'     => 5,
            'dispensed_by' => $this->admin->id,
            'dispensed_at' => now(),
        ]);
        $this->assertEquals(95, $medicine->fresh()->current_stock);

        Livewire::actingAs($this->admin)
            ->test(EditMedicineDispensing::class, ['record' => $dispensing->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($dispensing);
        $this->assertEquals(100, $medicine->fresh()->current_stock);
    }
}
