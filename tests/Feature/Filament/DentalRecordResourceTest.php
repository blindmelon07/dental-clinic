<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\DentalRecordResource\Pages\CreateDentalRecord;
use App\Filament\Resources\DentalRecordResource\Pages\EditDentalRecord;
use App\Models\DentalRecord;
use App\Models\Dentist;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_saved_diagnosis_is_preselected_when_editing(): void
    {
        $record = DentalRecord::factory()->create([
            'diagnosis' => 'Cavity in tooth 14, needs filling',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->getRouteKey()])
            ->assertFormSet([
                'diagnosis' => ['Cavity in tooth 14', 'needs filling'],
            ]);
    }

    public function test_diagnosis_survives_a_resave(): void
    {
        $record = DentalRecord::factory()->create([
            'diagnosis' => 'Cavity in tooth 14, needs filling',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditDentalRecord::class, ['record' => $record->getRouteKey()])
            ->call('save');

        $this->assertSame('Cavity in tooth 14, needs filling', $record->refresh()->diagnosis);
    }

    public function test_create_another_dispatches_tabs_reset_event(): void
    {
        $patient = Patient::factory()->create();
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(CreateDentalRecord::class)
            ->fillForm([
                'patient_id' => $patient->id,
                'dentist_id' => $dentist->id,
                'diagnosis' => [$service->display_name],
            ])
            ->call('createAnother')
            ->assertHasNoFormErrors()
            ->assertDispatched('dental-record-tabs-reset');

        $this->assertSame(1, DentalRecord::count());
    }
}
