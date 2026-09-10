<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Livewire\Public\Booking;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_drawer_can_be_opened_and_closed()
    {
        Livewire::test(Booking::class)
            ->assertSet('isCartOpen', false)
            ->call('openCart')
            ->assertSet('isCartOpen', true)
            ->call('closeCart')
            ->assertSet('isCartOpen', false)
            ->call('toggleCart')
            ->assertSet('isCartOpen', true);
    }

    public function test_items_are_grouped_in_cart_with_quantity()
    {
        $item = InventoryItem::create([
            'name' => 'Tenda Dome 4P',
            'sku' => 'TND-001',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 50000,
            'is_active' => true,
        ]);

        $unit1 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-001-01',
            'status' => 'Available',
        ]);

        $unit2 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-001-02',
            'status' => 'Available',
        ]);

        Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->assertCount('cart', 1)
            ->assertSet('cart.0.quantity', 1)
            ->assertSet('cart.0.inventory_item_id', $item->id)
            ->call('addToCart', $item->id)
            // Still 1 grouped entry in cart, but quantity is now 2
            ->assertCount('cart', 1)
            ->assertSet('cart.0.quantity', 2)
            ->assertCount('cart.0.unit_ids', 2);
    }

    public function test_phone_number_formatting_and_nik_limit()
    {
        Livewire::test(Booking::class)
            ->set('phone_number', '081234567890')
            ->assertSet('phone_number', '81234567890')
            ->assertSet('phone', '+6281234567890')
            ->set('phone_number', '628999888777')
            ->assertSet('phone_number', '8999888777')
            ->assertSet('phone', '+628999888777')
            ->set('nik', '12345678901234567899') // 20 digits, should be truncated to 16
            ->assertSet('nik', '1234567890123456');
    }

    public function test_booking_validation_requires_phone_and_16_digit_nik()
    {
        $item = InventoryItem::create([
            'name' => 'Carrier 60L',
            'sku' => 'CAR-001',
            'category' => 'Tas',
            'rental_type' => 'daily',
            'price_per_day' => 30000,
            'is_active' => true,
        ]);

        ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'CAR-001-01',
            'status' => 'Available',
        ]);

        Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->set('name', 'Budi Santoso')
            ->set('phone_number', '123') // Less than 9 digits
            ->set('nik', '12345') // Less than 16 digits
            ->set('address', 'Jl. Merbabu No. 1')
            ->call('submitBooking')
            ->assertHasErrors(['phone_number', 'nik']);
    }

    public function test_booking_catalog_lazy_loading_and_load_more()
    {
        // Create 15 items
        for ($i = 1; $i <= 15; $i++) {
            $item = InventoryItem::create([
                'name' => "Item Rental $i",
                'sku' => "SKU-$i",
                'category' => 'Tenda',
                'rental_type' => 'daily',
                'price_per_day' => 20000,
                'is_active' => true,
            ]);
            ItemUnit::create([
                'item_id' => $item->id,
                'serial_number' => "SKU-$i-01",
                'status' => 'Available',
            ]);
        }

        $component = Livewire::test(Booking::class)
            ->assertSet('perPage', 12);

        // Initially should have 12 items rendered and hasMorePages true
        $items = $component->get('availableItems');
        $this->assertCount(12, $items);
        $this->assertTrue($component->get('hasMorePages'));

        // Calling loadMore() loads next items
        $component->call('loadMore')
            ->assertSet('perPage', 24);

        $itemsAfterLoadMore = $component->get('availableItems');
        $this->assertCount(15, $itemsAfterLoadMore);
        $this->assertFalse($component->get('hasMorePages'));
    }

    public function test_cart_step_navigation()
    {
        $item = InventoryItem::create([
            'name' => 'Carrier Deuter 50L',
            'sku' => 'DTR-50L',
            'category' => 'Carrier',
            'rental_type' => 'daily',
            'price_per_day' => 45000,
            'is_active' => true,
        ]);
        ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'DTR-50L-01',
            'status' => 'Available',
        ]);

        $component = Livewire::test(Booking::class)
            ->assertSet('cartStep', 'items')
            // Trying to proceed to form with empty cart fails
            ->call('proceedToForm')
            ->assertHasErrors(['cart'])
            ->assertSet('cartStep', 'items');

        // Add item to cart
        $component->call('addToCart', $item->id)
            ->call('proceedToForm')
            ->assertSet('cartStep', 'form')
            ->call('backToItems')
            ->assertSet('cartStep', 'items');
    }

    public function test_user_can_choose_duration_and_calculation_appears()
    {
        $item = InventoryItem::create([
            'name' => 'Tenda Dome 4P',
            'sku' => 'TND-002',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 50000,
            'is_active' => true,
        ]);
        ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-002-01',
            'status' => 'Available',
        ]);

        $component = Livewire::test(Booking::class)
            ->assertSet('start_date', null)
            ->assertSet('end_date', null)
            ->assertSet('duration_days', 0)
            ->assertSet('total_price', 0);

        // Add to cart
        $component->call('addToCart', $item->id);

        // Price is still 0 because duration is not chosen yet
        $this->assertEquals(0, $component->get('total_price'));
        $this->assertEquals(50000, $component->get('subtotal_per_day'));

        // User chooses 1 day duration
        $component->call('setDuration', 1)
            ->assertSet('duration_days', 1);
        $this->assertEquals(50000, $component->get('total_price'));

        // User chooses 3 days duration
        $component->call('setDuration', 3)
            ->assertSet('duration_days', 3);
        $this->assertEquals(150000, $component->get('total_price'));
    }
}
