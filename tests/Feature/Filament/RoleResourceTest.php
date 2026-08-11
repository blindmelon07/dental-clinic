<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\RoleResource;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/roles');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/roles/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_role_with_permissions_from_multiple_groups(): void
    {
        // Regression test: every permission group used to share the same 'permissions'
        // form field/statePath, so only the last-rendered group's options validated —
        // picking a permission from an earlier group (e.g. Patients) failed with
        // "The selected permissions is invalid."
        $patientPermission = Permission::where('name', 'view_any_patient')->first();
        $userPermission = Permission::where('name', 'view_any_user')->first();

        Livewire::actingAs($this->admin)
            ->test(CreateRole::class)
            ->fillForm([
                'name' => 'clinic_manager',
                RoleResource::permissionFieldName('Patients') => [$patientPermission->id],
                RoleResource::permissionFieldName('Users')    => [$userPermission->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'clinic_manager')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->permissions->contains($patientPermission));
        $this->assertTrue($role->permissions->contains($userPermission));
    }

    public function test_can_edit_a_role(): void
    {
        $role = Role::create(['name' => 'editable_role', 'guard_name' => 'web']);
        $permission = Permission::where('name', 'view_any_patient')->first();

        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $role->getRouteKey()])
            ->fillForm([RoleResource::permissionFieldName('Patients') => [$permission->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($role->fresh()->permissions->contains($permission));
    }

    public function test_editing_a_role_preserves_permissions_from_unrelated_groups(): void
    {
        $role = Role::create(['name' => 'editable_role', 'guard_name' => 'web']);
        $patientPermission = Permission::where('name', 'view_any_patient')->first();
        $userPermission = Permission::where('name', 'view_any_user')->first();
        $role->syncPermissions([$patientPermission->id, $userPermission->id]);

        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $role->getRouteKey()])
            ->fillForm([RoleResource::permissionFieldName('Users') => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $role->refresh();
        $this->assertTrue($role->permissions->contains($patientPermission));
        $this->assertFalse($role->permissions->contains($userPermission));
    }

    public function test_can_delete_a_custom_role(): void
    {
        $role = Role::create(['name' => 'throwaway_role', 'guard_name' => 'web']);

        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $role->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($role);
    }

    public function test_protected_roles_cannot_be_deleted(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        Livewire::actingAs($this->admin)
            ->test(EditRole::class, ['record' => $role->getRouteKey()])
            ->assertActionHidden('delete');

        $this->assertNotNull($role->fresh());
    }
}
