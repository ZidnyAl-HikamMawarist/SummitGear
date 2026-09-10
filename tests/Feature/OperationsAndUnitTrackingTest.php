<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use Livewire\Livewire;
use App\Livewire\Admin\Inventory\UnitIndex;
use App\Livewire\Admin\Operations\CheckIn;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OperationsAndUnitTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_units_via_flux_modal_workflow()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = InventoryItem::create([
            'name' => 'Tenda Dome 4P',
            'sku' => 'TND-001',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 50000,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(UnitIndex::class, ['itemId' => $item->id])
            ->call('openGenerateModal')
            ->assertSet('isGenerateModalOpen', true)
            ->set('generateQty', 3)
            ->set('generateReplacementValue', 350000)
            ->call('submitGenerateUnits')
            ->assertSet('isGenerateModalOpen', false)
            ->assertHasNoErrors();

        $this->assertEquals(3, ItemUnit::where('item_id', $item->id)->count());
        $unit = ItemUnit::where('item_id', $item->id)->first();
        $this->assertEquals(350000, $unit->replacement_value);
        $this->assertEquals('Available', $unit->status);
    }

    public function test_check_in_condition_update_auto_syncs_return_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '081234567890',
            'nik' => '3201123456780001',
        ]);
        $item = InventoryItem::create([
            'name' => 'Carrier 60L',
            'sku' => 'CRR-001',
            'category' => 'Carrier',
            'rental_type' => 'daily',
            'price_per_day' => 45000,
            'is_active' => true,
        ]);
        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'CRR-001-0001',
            'status' => 'Rented',
            'replacement_value' => 500000,
        ]);

        $rental = Rental::create([
            'rental_code' => 'TRX-TEST-001',
            'customer_id' => $customer->id,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'scheduled_return_time' => now()->addDay(),
            'status' => 'RENTED_OUT',
            'total_price' => 90000,
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'return_status' => 'RETURNED',
            'price_per_day' => 45000,
        ]);

        Livewire::actingAs($admin)
            ->test(CheckIn::class, ['rentalId' => $rental->id])
            ->set("checkinData.{$detail->id}.condition", 'Rusak')
            ->assertSet("checkinData.{$detail->id}.return_status", 'DAMAGED')
            ->set("checkinData.{$detail->id}.condition", 'Hilang')
            ->assertSet("checkinData.{$detail->id}.return_status", 'LOST')
            ->set("checkinData.{$detail->id}.condition", 'Baik')
            ->assertSet("checkinData.{$detail->id}.return_status", 'RETURNED');
    }
}
