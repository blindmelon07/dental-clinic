<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users/create');
        $response->assertStatus(200);
    }

    public function test_can_create_a_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name'     => 'Jane Receptionist',
                'email'    => 'jane@example.com',
                'password' => 'password123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_password_is_required_on_create_but_not_on_edit(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateUser::class)
            ->fillForm(['name' => 'No Password', 'email' => 'nopass@example.com'])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    public function test_can_edit_a_user_without_changing_password(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $originalPassword = $user->password;

        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm(['name' => 'New Name'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame($originalPassword, $user->fresh()->password);
    }

    public function test_can_delete_a_user(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($user);
    }
}
