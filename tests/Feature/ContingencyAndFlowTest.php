<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Payment;
use App\Models\Deposit;
use App\Models\Penalty;
use App\Models\User;
use App\Models\Setting;
use Livewire\Livewire;
use Carbon\Carbon;
use App\Livewire\Admin\Operations\CheckOut;
use App\Livewire\Admin\Operations\CheckIn;
use App\Livewire\Admin\Operations\Settlement;
use App\Livewire\Admin\Operations\IncomingBooking;
use App\Livewire\Admin\Transaction\Create;
use App\Livewire\Admin\Transaction\Invoice;
use App\Livewire\Admin\Inventory\UnitIndex;
use App\Livewire\Public\Booking;
use App\Jobs\ExpireOnlineBookingJob;

class ContingencyAndFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@summitgear.test',
            'role' => 'admin',
        ]);
        $this->actingAs($this->adminUser);

        Setting::create(['key' => 'admin_supervisor_pin', 'value' => '1234']);
    }

    public function test_customer_blacklist_blocks_booking_and_pos()
    {
        $blacklistedCustomer = Customer::create([
            'name' => 'Bad Customer',
            'nik' => '1234567890123456',
            'phone' => '081234567890',
            'is_blacklisted' => true,
            'blacklist_notes' => 'Piutang macet kabur',
        ]);

        $item = InventoryItem::create([
            'name' => 'Tenda Dome',
            'sku' => 'TND-001',
            'category' => 'Tents',
            'price_per_day' => 50000,
            'rental_type' => 'daily',
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'TND-001-A',
            'status' => 'Available',
            'replacement_value' => 500000,
        ]);

        // 1. Check Public Booking blocks blacklisted phone
        Livewire::test(Booking::class)
            ->set('phone_number', '81234567890')
            ->set('name', 'Bad Customer')
            ->set('nik', '1234567890123456')
            ->set('address', 'Jl. Merdeka No 123')
            ->set('start_date', now()->addDay()->format('Y-m-d'))
            ->set('end_date', now()->addDays(2)->format('Y-m-d'))
            ->set('pickup_time', '10:00')
            ->call('addToCart', $item->id)
            ->call('submitBooking')
            ->assertHasErrors(['customer']);

        // 2. Check POS Transaction blocks blacklisted customer
        Livewire::test(Create::class)
            ->set('customer_id', $blacklistedCustomer->id)
            ->assertSet('is_customer_blacklisted', true)
            ->assertHasErrors(['customer_id']);
    }

    public function test_cannot_delete_unit_if_linked_to_active_or_upcoming_rentals()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'nik' => '3201010101010001',
            'phone' => '081111111111',
        ]);

        $item = InventoryItem::create([
            'name' => 'Carrier 60L',
            'sku' => 'CAR-001',
            'category' => 'Packs',
            'price_per_day' => 30000,
            'rental_type' => 'daily',
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'CAR-001-1',
            'status' => 'Available',
            'replacement_value' => 700000,
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-TEST-001',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'scheduled_return_time' => now()->addDays(3)->endOfDay(),
            'total_price' => 90000,
            'down_payment_amount' => 90000,
            'status' => 'BOOKED',
            'source' => 'online',
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 30000,
        ]);

        Livewire::test(UnitIndex::class, ['itemId' => $item->id])
            ->call('deleteUnit', $unit->id)
            ->assertHasErrors(['delete_unit']);

        $this->assertDatabaseHas('item_units', ['id' => $unit->id, 'deleted_at' => null]);
    }

    public function test_checkout_mandatory_ktp_and_unit_swap()
    {
        $customer = Customer::create([
            'name' => 'Alice Camping',
            'nik' => '3201010101010002',
            'phone' => '081222222222',
        ]);

        $item = InventoryItem::create([
            'name' => 'Kompor Portable',
            'sku' => 'KMP-001',
            'category' => 'Cooking',
            'price_per_day' => 15000,
            'rental_type' => 'daily',
        ]);

        $unit1 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'KMP-001-A',
            'status' => 'Available',
            'replacement_value' => 150000,
        ]);

        $unit2 = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'KMP-001-B',
            'status' => 'Available',
            'replacement_value' => 150000,
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-ONLINE-001',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'scheduled_return_time' => now()->addDays(2)->endOfDay(),
            'total_price' => 30000,
            'down_payment_amount' => 30000,
            'status' => 'PAID',
            'source' => 'online',
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit1->id,
            'price_per_day' => 15000,
        ]);

        // 1. Cannot submit checkout without KTP verification for online booking
        Livewire::test(CheckOut::class, ['rentalId' => $rental->id])
            ->set('customerAgreed', true)
            ->set('is_ktp_verified', false)
            ->call('submitCheckOut')
            ->assertHasErrors(['is_ktp_verified']);

        // 2. Perform Unit Swap from unit1 to unit2
        Livewire::test(CheckOut::class, ['rentalId' => $rental->id])
            ->call('openSwapModal', $detail->id)
            ->assertSet('showSwapModal', true)
            ->set('newUnitId', $unit2->id)
            ->call('executeSwapUnit')
            ->assertHasNoErrors();

        $this->assertEquals($unit2->id, $detail->fresh()->item_unit_id);

        // 3. Complete checkout with KTP verified
        Livewire::test(CheckOut::class, ['rentalId' => $rental->id])
            ->set('customerAgreed', true)
            ->set('is_ktp_verified', true)
            ->call('submitCheckOut')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.operations.handover'));

        $this->assertEquals('RENTED_OUT', $rental->fresh()->status);
        $this->assertDatabaseHas('deposits', [
            'rental_id' => $rental->id,
            'type' => 'DOC',
            'doc_type' => 'KTP',
            'status' => 'HELD',
        ]);
    }

    public function test_checkin_flexible_damage_cost_and_settlement()
    {
        $customer = Customer::create([
            'name' => 'Bob Hiker',
            'nik' => '3201010101010003',
            'phone' => '081333333333',
        ]);

        $item = InventoryItem::create([
            'name' => 'Matras Foil',
            'sku' => 'MTR-001',
            'category' => 'Sleep',
            'price_per_day' => 10000,
            'rental_type' => 'daily',
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'MTR-001-A',
            'status' => 'Rented',
            'replacement_value' => 100000,
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-CHECKIN-001',
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'scheduled_return_time' => now()->endOfDay(),
            'total_price' => 20000,
            'down_payment_amount' => 20000,
            'status' => 'RENTED_OUT',
            'source' => 'walk_in',
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 10000,
        ]);

        Deposit::create([
            'rental_id' => $rental->id,
            'type' => 'CASH',
            'amount' => 50000,
            'status' => 'HELD',
            'retention_deadline' => now()->addDays(30),
        ]);

        // Test 1-click bulk mark all as good
        Livewire::test(CheckIn::class, ['rentalId' => $rental->id])
            ->call('markAllAsGood')
            ->assertSet("checkinData.{$detail->id}.condition", 'Baik')
            ->assertSet("checkinData.{$detail->id}.return_status", 'RETURNED');

        // CheckIn with custom damage fee Rp 25.000 instead of default 30% (Rp 30.000)
        Livewire::test(CheckIn::class, ['rentalId' => $rental->id])
            ->set("checkinData.{$detail->id}.condition", 'Rusak')
            ->set("checkinData.{$detail->id}.damage_cost", 25000)
            ->set("checkinData.{$detail->id}.notes", 'Sobek 3cm jahitan samping')
            ->call('submitCheckIn')
            ->assertHasNoErrors();

        $this->assertEquals('PENDING_SETTLEMENT', $rental->fresh()->status);
        $this->assertDatabaseHas('penalties', [
            'rental_id' => $rental->id,
            'amount' => 25000,
            'is_settled' => false,
        ]);

        // Now test settlement deducts from cash deposit and creates payment
        Livewire::test(Settlement::class, ['rentalId' => $rental->id])
            ->call('processSettlement')
            ->assertHasNoErrors();

        $this->assertEquals('COMPLETED', $rental->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'rental_id' => $rental->id,
            'type' => 'penalty',
            'method' => 'DEPOSIT_DEDUCTION',
            'amount' => 25000,
        ]);
    }

    public function test_rental_extension_on_active_rental()
    {
        $customer = Customer::create([
            'name' => 'Charlie Mountain',
            'nik' => '3201010101010004',
            'phone' => '081444444444',
        ]);

        $item = InventoryItem::create([
            'name' => 'Flysheet 3x4',
            'sku' => 'FLY-001',
            'category' => 'Shelter',
            'price_per_day' => 20000,
            'rental_type' => 'daily',
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'FLY-001-A',
            'status' => 'Rented',
            'replacement_value' => 150000,
        ]);

        $currentEnd = now()->format('Y-m-d');
        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-EXTEND-001',
            'start_date' => now()->subDays(2)->format('Y-m-d'),
            'end_date' => $currentEnd,
            'scheduled_return_time' => now()->endOfDay(),
            'total_price' => 40000,
            'down_payment_amount' => 40000,
            'status' => 'RENTED_OUT',
            'source' => 'walk_in',
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 20000,
        ]);

        $newEndDate = now()->addDays(2)->format('Y-m-d');

        Livewire::test(Invoice::class, ['id' => $rental->id])
            ->call('openExtendModal')
            ->set('newEndDate', $newEndDate)
            ->call('calculateExtension')
            ->assertSet('extendDays', 2)
            ->assertSet('extendCost', 40000)
            ->call('executeExtension')
            ->assertHasNoErrors();

        $fresh = $rental->fresh();
        $this->assertEquals($newEndDate, $fresh->end_date->format('Y-m-d'));
        $this->assertEquals(80000, $fresh->total_price);
        $this->assertEquals('RENTED_OUT', $fresh->status);
    }

    public function test_incoming_booking_tolerance_and_paid_cancellation()
    {
        $customer = Customer::create([
            'name' => 'David Online',
            'nik' => '3201010101010005',
            'phone' => '081555555555',
        ]);

        $item = InventoryItem::create([
            'name' => 'Lentera Tenda',
            'sku' => 'LTR-001',
            'category' => 'Lighting',
            'price_per_day' => 15000,
            'rental_type' => 'daily',
        ]);

        $unit = ItemUnit::create([
            'item_id' => $item->id,
            'serial_number' => 'LTR-001-A',
            'status' => 'Available',
            'replacement_value' => 120000,
        ]);

        $rental = Rental::create([
            'customer_id' => $customer->id,
            'rental_code' => 'TRX-TOLERANCE-001',
            'start_date' => now()->subHours(3)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(2)->format('Y-m-d'),
            'scheduled_return_time' => now()->addDays(2)->endOfDay(),
            'total_price' => 30000,
            'down_payment_amount' => 15000,
            'status' => 'DP_PAID',
            'source' => 'online',
        ]);

        RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 15000,
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'type' => 'DP',
            'method' => 'TRANSFER',
            'amount' => 15000,
            'paid_at' => now(),
        ]);

        // 1. Extend tolerance
        Livewire::test(IncomingBooking::class)
            ->call('extendPickupTolerance', $rental->id, 4)
            ->assertHasNoErrors();

        $this->assertNotNull($rental->fresh()->pickup_extended_until);

        // Verify ExpireOnlineBookingJob will not expire this rental
        Setting::updateOrCreate(['key' => 'online_booking_expire_hours'], ['value' => '2']);
        (new ExpireOnlineBookingJob())->handle();
        $this->assertEquals('DP_PAID', $rental->fresh()->status);

        // 2. Cancel with DP Hangus
        Livewire::test(IncomingBooking::class)
            ->call('confirmCancelBooking', $rental->id, 'normal')
            ->assertSet('cancelPaidAmount', 15000)
            ->set('cancelAction', 'FORFEIT')
            ->call('executeCancellation')
            ->assertHasNoErrors();

        $fresh = $rental->fresh();
        $this->assertEquals('CANCELLED', $fresh->status);
        $this->assertStringContainsString('DP HANGUS', $fresh->settlement_notes);
        $this->assertEquals('Available', $unit->fresh()->status);
    }
}
