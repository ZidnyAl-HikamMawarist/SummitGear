<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\User;
use App\Livewire\Public\Booking;
use App\Livewire\Admin\Operations\IncomingBooking;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BookingPickupAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_requires_pickup_time_and_saves_datetime()
    {
        $item = InventoryItem::create([
            'name' => 'Tenda Dome 2P',
            'sku' => 'TND-002',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 45000,
            'is_active' => true,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-002-01',
            'status' => 'Available',
        ]);

        $startDate = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(2)->format('Y-m-d');
        $pickupTime = '14:30';

        Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('pickup_time', $pickupTime)
            ->set('name', 'Rizky Pratama')
            ->set('phone_number', '81234567890')
            ->set('nik', '3201123456780001')
            ->set('address', 'Jl. Kaliurang KM 5')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $rental = Rental::latest()->first();
        $this->assertNotNull($rental);
        $this->assertEquals('PENDING_PAYMENT', $rental->status);
        $this->assertEquals('online', $rental->source);

        // Verify pickup datetime is stored in start_date
        $expectedDateTime = Carbon::parse($startDate . ' ' . $pickupTime);
        $this->assertEquals($expectedDateTime->format('Y-m-d H:i'), Carbon::parse($rental->start_date)->format('Y-m-d H:i'));
    }

    public function test_cashier_validates_expired_booking_and_stock_returns_to_warehouse()
    {
        $admin = User::factory()->create([
            'role' => 'kasir',
        ]);

        $item = InventoryItem::create([
            'name' => 'Carrier 60L',
            'sku' => 'CAR-060',
            'category' => 'Tas',
            'rental_type' => 'daily',
            'price_per_day' => 40000,
            'is_active' => true,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'CAR-060-01',
            'status' => 'Available',
        ]);

        $customer = Customer::create([
            'name' => 'Ahmad Hidayat',
            'phone' => '+6281299998888',
            'nik' => '3201999988880001',
            'address' => 'Jl. Gejayan No. 10',
        ]);

        // Create booking with pickup time 3 hours ago (already past 2 hours tolerance)
        $pastPickup = Carbon::now()->subHours(3);
        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-20260909-0001',
            'start_date' => $pastPickup,
            'end_date' => Carbon::now()->addDay(),
            'scheduled_return_time' => Carbon::now()->addDay()->endOfDay(),
            'total_price' => 80000,
            'status' => 'PENDING_PAYMENT',
            'source' => 'online',
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 40000,
        ]);

        // Mark unit as reserved/in rental
        $unit->update(['status' => 'Reserved']);

        $this->actingAs($admin);

        // Test IncomingBooking component shows booking as expired and cashier can validate cancel
        Livewire::test(IncomingBooking::class)
            ->assertSee($rental->rental_code)
            ->assertSee('LEWAT BATAS WAKTU')
            ->call('validateExpiredAndCancel', $rental->id)
            ->assertHasNoErrors()
            ->assertSee('Validasi Berhasil');

        // Check rental is cancelled
        $rental->refresh();
        $this->assertEquals('CANCELLED', $rental->status);

        // Check unit returned to Available
        $unit->refresh();
        $this->assertEquals('Available', $unit->status);
    }

    public function test_incoming_booking_shows_clean_indonesian_time_and_contrast_buttons()
    {
        $admin = User::factory()->create([
            'role' => 'kasir',
        ]);

        $item = InventoryItem::create([
            'name' => 'Tenda Dome 4P',
            'sku' => 'TND-004',
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 60000,
            'is_active' => true,
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-004-01',
            'status' => 'Reserved',
        ]);

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '+6281234567890',
            'nik' => '3201123456789012',
            'address' => 'Jl. Magelang',
        ]);

        // Active booking (pickup 2 hours in the future)
        $futurePickup = Carbon::now()->addHours(2);
        $rentalActive = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-20260909-0002',
            'start_date' => $futurePickup,
            'end_date' => Carbon::now()->addDays(2),
            'scheduled_return_time' => Carbon::now()->addDays(2)->endOfDay(),
            'total_price' => 120000,
            'status' => 'PENDING_PAYMENT',
            'source' => 'online',
        ]);

        RentalDetail::create([
            'rental_id' => $rentalActive->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 60000,
        ]);

        $this->actingAs($admin);

        Livewire::test(IncomingBooking::class)
            ->assertSee($rentalActive->rental_code)
            ->assertSee('Menunggu Diambil')
            ->assertSee('Sisa waktu pengambilan:')
            ->assertDontSee('ago')
            ->assertSee('Proses / Bayar')
            ->assertSee('Batalkan');
    }
}
