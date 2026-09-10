<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_access_transactions_create_and_sees_sidebar_menu()
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($kasir)->get(route('admin.transactions.create'));
        $response->assertStatus(200);

        // Kasir sees "Kasir & Sewa" in sidebar
        $dashboardResponse = $this->actingAs($kasir)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Kasir & Sewa', false);
    }

    public function test_admin_cannot_access_transactions_create_and_sidebar_hides_kasir_dan_sewa()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Route /admin/transactions/create is 403 Forbidden for admin
        $response = $this->actingAs($admin)->get(route('admin.transactions.create'));
        $response->assertStatus(403);

        // Admin does NOT see "Kasir & Sewa" in sidebar
        $dashboardResponse = $this->actingAs($admin)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertDontSee('Kasir & Sewa', false);
        $dashboardResponse->assertDontSee('Kasir Transaksi Baru');
        $dashboardResponse->assertSee('Booking Masuk');
    }
}
