<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Livewire\Admin\Transaction\Create;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CashierCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cart_groups_same_item_with_quantity_and_multiplied_price()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $item = InventoryItem::create([
            'name' => 'Tenda Kap 6 Org',
            'sku' => 'TND-006',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 70000,
            'is_active' => true,
        ]);

        $unit1 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'ITM-004-05',
            'status' => 'Available',
        ]);

        $unit2 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'ITM-004-04',
            'status' => 'Available',
        ]);

        Livewire::actingAs($kasir)
            ->test(Create::class)
            ->call('addToCart', $item->id)
            ->assertCount('cart', 1)
            ->call('addToCart', $item->id)
            // Internal cart has 2 units, but groupedCart has 1 group with quantity 2
            ->assertCount('cart', 2)
            ->assertSet('groupedCart.0.quantity', 2)
            ->assertSet('groupedCart.0.total_base_price', 140000)
            ->assertSet('groupedCart.0.serial_numbers', ['ITM-004-05', 'ITM-004-04'])
            // Test decrease quantity
            ->call('decreaseItem', $item->id)
            ->assertCount('cart', 1)
            ->assertSet('groupedCart.0.quantity', 1)
            ->assertSet('groupedCart.0.total_base_price', 70000)
            // Test remove item completely
            ->call('removeItemCompletely', $item->id)
            ->assertCount('cart', 0);
    }

    public function test_payment_amount_strips_leading_zeroes()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        Livewire::actingAs($kasir)
            ->test(Create::class)
            ->set('payment_amount', '0415000')
            ->assertSet('payment_amount', '415000')
            ->set('payment_amount', '0')
            ->assertSet('payment_amount', '0')
            ->set('payment_amount', '')
            ->assertSet('payment_amount', '');
    }
}
