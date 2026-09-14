<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\User;
use App\Livewire\Public\Booking;
use App\Livewire\Admin\Operations\IncomingBooking;
use App\Jobs\ReleaseHoldBookingJob;
use Livewire\Livewire;
use Carbon\Carbon;

class BookingAntiHoardingAndPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createStockItem($name = 'Tenda Dome 4P', $stockCount = 2, $price = 50000)
    {
        $item = InventoryItem::create([
            'name' => $name,
            'sku' => 'SKU-' . uniqid(),
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => $price,
            'is_active' => true,
        ]);

        $units = [];
        for ($i = 1; $i <= $stockCount; $i++) {
            $units[] = ItemUnit::create([
                'item_id' => $item->id,
                'serial_number' => 'SN-' . $item->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'Available',
            ]);
        }

        return [$item, $units];
    }

    public function test_booking_sets_10_minute_expiration_and_pending_payment_status()
    {
        [$item, $units] = $this->createStockItem('Tenda Dome Eiger', 2, 60000);

        $startDate = Carbon::now()->addDay()->format('Y-m-d');
        $endDate = Carbon::now()->addDays(3)->format('Y-m-d');

        $component = Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->set('cartStep', 'form')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('pickup_time', '10:00')
            ->set('name', 'Budi Santoso')
            ->set('phone_number', '081234567890')
            ->set('nik', '3201123456780001')
            ->set('address', 'Jl. Kaliurang KM 5')
            ->call('submitBooking')
            ->assertHasNoErrors();

        $rental = Rental::where('source', 'online')->latest()->first();
        $this->assertNotNull($rental);
        $this->assertEquals(Rental::STATUS_PENDING_PAYMENT, $rental->status);
        $this->assertNotNull($rental->expires_at);

        // Expiration should be ~10 minutes from now (between 580 and 605 seconds)
        $diffSeconds = Carbon::now()->diffInSeconds($rental->expires_at, false);
        $this->assertGreaterThan(580, $diffSeconds);
        $this->assertLessThanOrEqual(605, $diffSeconds);

        // ItemUnit should be held in rental details
        $this->assertDatabaseHas('rental_details', [
            'rental_id' => $rental->id,
        ]);
        $this->assertEquals(1, $rental->details()->count());

        // Component should now be on step 'payment'
        $component->assertSet('cartStep', 'payment')
            ->assertSet('activeRentalId', $rental->id);
    }

    public function test_anti_hoarding_prevents_multiple_unpaid_bookings_from_same_phone_or_nik()
    {
        [$item, $units] = $this->createStockItem('Carrier Osprey 60L', 2, 40000);

        $customer = Customer::create([
            'name' => 'Spammer User',
            'phone' => '081999888777',
            'nik' => '3201999988887777',
            'address' => 'Jl. Merdeka No 1',
        ]);

        // Existing active PENDING_PAYMENT booking within 10-minute window
        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-HOLD-001',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'scheduled_return_time' => Carbon::now()->addDays(2)->endOfDay(),
            'total_price' => 80000,
            'status' => Rental::STATUS_PENDING_PAYMENT,
            'source' => 'online',
            'expires_at' => Carbon::now()->addMinutes(8),
        ]);

        // Attempting a second booking with the same phone must be rejected
        Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->set('cartStep', 'form')
            ->set('start_date', Carbon::now()->addDay()->format('Y-m-d'))
            ->set('end_date', Carbon::now()->addDays(2)->format('Y-m-d'))
            ->set('pickup_time', '14:00')
            ->set('name', 'Spammer User Again')
            ->set('phone_number', '081999888777')
            ->set('nik', '3201999988889999') // different nik, same phone
            ->set('address', 'Jl. Merdeka No 2')
            ->call('submitBooking')
            ->assertHasErrors(['anti_hoarding']);

        // Attempting with same NIK must also be rejected
        Livewire::test(Booking::class)
            ->call('addToCart', $item->id)
            ->set('cartStep', 'form')
            ->set('start_date', Carbon::now()->addDay()->format('Y-m-d'))
            ->set('end_date', Carbon::now()->addDays(2)->format('Y-m-d'))
            ->set('pickup_time', '14:00')
            ->set('name', 'Different Name')
            ->set('phone_number', '085554443322') // different phone
            ->set('nik', '3201999988887777') // same NIK as active booking
            ->set('address', 'Jl. Merdeka No 3')
            ->call('submitBooking')
            ->assertHasErrors(['anti_hoarding']);
    }

    public function test_expired_booking_stock_is_immediately_available_in_catalog()
    {
        // 1 item with only 1 unit stock
        [$item, $units] = $this->createStockItem('Kompor Ultralight', 1, 15000);
        $unit = $units[0];

        $customer = Customer::create([
            'name' => 'Ghost Booker',
            'phone' => '087771112222',
            'nik' => '3201777711112222',
            'address' => 'Jl. Hantu 99',
        ]);

        $startDate = Carbon::now()->addDays(2)->startOfDay();
        $endDate = Carbon::now()->addDays(4)->startOfDay();

        // Create booking that EXPIRED 2 minutes ago
        $expiredRental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-EXPIRED-001',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'scheduled_return_time' => $endDate->copy()->endOfDay(),
            'total_price' => 30000,
            'status' => Rental::STATUS_PENDING_PAYMENT,
            'source' => 'online',
            'expires_at' => Carbon::now()->subMinutes(2),
        ]);

        RentalDetail::create([
            'rental_id' => $expiredRental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 15000,
        ]);
        $unit->update(['status' => 'Reserved']);

        // A new customer browses the catalog for that same date range:
        // Because the previous booking is PENDING_PAYMENT but expires_at <= now,
        // it should be treated as available and able to be added to cart.
        $component = Livewire::test(Booking::class)
            ->set('start_date', $startDate->format('Y-m-d'))
            ->set('end_date', $endDate->format('Y-m-d'))
            ->call('addToCart', $item->id);

        $component->assertSet('cart.0.quantity', 1);
    }

    public function test_confirm_online_payment_updates_status_and_clears_expiration()
    {
        [$item, $units] = $this->createStockItem('Sleeping Bag Bulu Angsa', 1, 25000);
        $unit = $units[0];

        $customer = Customer::create([
            'name' => 'Citra Lestari',
            'phone' => '082133445566',
            'nik' => '3201334455660001',
            'address' => 'Jl. Palagan No 12',
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-DP-TEST',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(3),
            'scheduled_return_time' => Carbon::now()->addDays(3)->endOfDay(),
            'total_price' => 100000,
            'status' => Rental::STATUS_PENDING_PAYMENT,
            'source' => 'online',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 25000,
        ]);
        $unit->update(['status' => 'Reserved']);

        // Customer selects DP (30%) and confirms payment
        $component = Livewire::test(Booking::class)
            ->set('activeRentalId', $rental->id)
            ->set('activeRentalCode', $rental->rental_code)
            ->set('expiresAt', $rental->expires_at->toIso8601String())
            ->set('total_price', 100000)
            ->set('paymentOption', 'dp')
            ->set('selectedPaymentMethod', 'qris')
            ->call('confirmOnlinePayment')
            ->assertHasNoErrors()
            ->assertSet('showSuccessModal', true);

        $rental->refresh();
        $this->assertEquals(Rental::STATUS_DP_PAID, $rental->status);
        $this->assertEquals(30000, $rental->down_payment_amount);
        $this->assertEquals('dp', $rental->payment_type);
        $this->assertNull($rental->expires_at); // Expiration cleared, stock permanently secured
        $this->assertEquals(70000, $rental->balance_due);
    }

    public function test_release_hold_booking_job_restores_stock_to_available()
    {
        [$item, $units] = $this->createStockItem('Tenda Doom 2P', 1, 30000);
        $unit = $units[0];

        $customer = Customer::create([
            'name' => 'Abandoned User',
            'phone' => '089991112223',
            'nik' => '3201999111222301',
            'address' => 'Jl. Kaliurang KM 10',
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-ABANDONED',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'scheduled_return_time' => Carbon::now()->addDays(2)->endOfDay(),
            'total_price' => 30000,
            'status' => Rental::STATUS_PENDING_PAYMENT,
            'source' => 'online',
            'expires_at' => Carbon::now()->subMinute(), // expired 1 minute ago
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 30000,
        ]);
        $unit->update(['status' => 'Reserved']);

        // Run the background cleanup job
        $job = new ReleaseHoldBookingJob();
        $job->handle();

        $rental->refresh();
        $this->assertEquals('CANCELLED', $rental->status);

        $unit->refresh();
        $this->assertEquals('Available', $unit->status);
    }

    public function test_incoming_booking_displays_dp_paid_and_paid_badges()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        [$item, $units] = $this->createStockItem('Kompor Windproof', 1, 20000);

        $customer = Customer::create([
            'name' => 'Dewi Sartika',
            'phone' => '081233344455',
            'nik' => '3201333444550001',
            'address' => 'Jl. Magelang KM 4',
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-DP-BADGE-01',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'scheduled_return_time' => Carbon::now()->addDays(2)->endOfDay(),
            'total_price' => 50000,
            'down_payment_amount' => 15000,
            'payment_type' => 'dp',
            'status' => Rental::STATUS_DP_PAID,
            'source' => 'online',
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $units[0]->id,
            'price_per_day' => 20000,
        ]);

        $this->actingAs($kasir);

        Livewire::test(IncomingBooking::class)
            ->assertSee($rental->rental_code)
            ->assertSee('DP Terbayar (30%)')
            ->assertSee('DP: Rp 15.000')
            ->assertSee('Sisa: Rp 35.000');
    }
}
