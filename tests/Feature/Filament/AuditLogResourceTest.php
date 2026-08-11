<?php

namespace Tests\Feature\Filament;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/audit-logs');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $log = AuditLog::create([
            'user_name' => 'Jane Admin',
            'event'     => 'created',
            'auditable_type' => User::class,
            'auditable_id'   => $this->admin->id,
            'new_values' => ['name' => 'Jane Admin'],
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/audit-logs/{$log->id}");
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_audit_logs(): void
    {
        $receptionist = User::factory()->create(['is_active' => true]);
        $receptionist->assignRole('receptionist');

        $response = $this->actingAs($receptionist)->get('/admin/audit-logs');
        $response->assertForbidden();
    }

    public function test_create_and_edit_routes_do_not_exist(): void
    {
        $this->actingAs($this->admin)->get('/admin/audit-logs/create')->assertNotFound();
    }
}
