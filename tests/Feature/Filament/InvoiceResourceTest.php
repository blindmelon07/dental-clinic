<?php

namespace Tests\Feature\Filament;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
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
        $response = $this->actingAs($this->admin)->get('/admin/invoices');
        $response->assertStatus(200);
    }

    public function test_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/invoices/create');
        $response->assertStatus(200);
    }

    public function test_view_page_renders(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/invoices/{$invoice->id}");
        $response->assertStatus(200);
    }

    public function test_edit_page_renders(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/invoices/{$invoice->id}/edit");
        $response->assertStatus(200);
    }
}
