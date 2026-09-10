<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_grouped_details_merges_same_item_rows()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $customer = Customer::create([
            'name' => 'John Doe',
            'nik' => '1234567890123456',
            'phone' => '08123456789',
        ]);

        $item = InventoryItem::create([
            'name' => 'Tenda Kap 4 Org',
            'sku' => 'TND-004',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 40000,
            'is_active' => true,
        ]);

        $unit1 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'ITM-002-05',
            'status' => 'Rented',
        ]);

        $unit2 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'ITM-002-03',
            'status' => 'Rented',
        ]);

        $rental = Rental::create([
            'rental_code' => 'TRX-20260909-0001',
            'customer_id' => $customer->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(1)->format('Y-m-d'),
            'scheduled_return_time' => now()->addDays(1)->endOfDay(),
            'status' => 'BOOKED',
            'total_price' => 160000,
            'discount' => 0,
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit1->id,
            'price_per_day' => 40000,
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit2->id,
            'price_per_day' => 40000,
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'type' => 'rental',
            'method' => 'CASH',
            'amount' => 160000,
            'paid_at' => now(),
        ]);

        // Model groupedDetails check
        $grouped = $rental->groupedDetails;
        $this->assertCount(1, $grouped);
        $this->assertEquals(2, $grouped[0]['quantity']);
        $this->assertEquals(80000, $grouped[0]['total_price_per_day']);
        $this->assertEquals(['ITM-002-05', 'ITM-002-03'], $grouped[0]['serial_numbers']);

        // Web view response check
        $response = $this->actingAs($kasir)->get(route('admin.transactions.invoice', $rental->id));
        $response->assertStatus(200);
        $response->assertSee('Tenda Kap 4 Org');
        $response->assertSee('x2');
        $response->assertSee('ITM-002-05');
        $response->assertSee('ITM-002-03');
    }
}
