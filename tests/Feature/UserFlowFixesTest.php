<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\Penalty;
use App\Models\Deposit;
use App\Models\Payment;
use App\Livewire\Admin\Operations\CheckIn;
use App\Livewire\Admin\Operations\Settlement;
use App\Livewire\Admin\Customer\Form as CustomerForm;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class UserFlowFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCustomer(array $attrs = [])
    {
        static $seq = 1000;
        $seq++;
        return Customer::create(array_merge([
            'name' => 'Test Customer ' . $seq,
            'nik' => '320100000000' . $seq,
            'phone' => '08120000' . $seq,
            'address' => 'Jl. Uji Coba No. ' . $seq,
            'consent_at' => now(),
        ], $attrs));
    }

    protected function makeItem(array $attrs = [])
    {
        static $itemSeq = 100;
        $itemSeq++;
        return InventoryItem::create(array_merge([
            'sku' => 'SKU-' . $itemSeq,
            'name' => 'Tenda Dome ' . $itemSeq,
            'category' => 'Tenda',
            'rental_type' => 'daily',
            'price_per_day' => 50000,
        ], $attrs));
    }

    protected function makeUnit($itemId, array $attrs = [])
    {
        static $unitSeq = 100;
        $unitSeq++;
        return ItemUnit::create(array_merge([
            'item_id' => $itemId,
            'serial_number' => 'TEN-001-' . str_pad($unitSeq, 4, '0', STR_PAD_LEFT),
            'status' => 'Rented',
            'replacement_value' => 500000,
        ], $attrs));
    }

    public function test_check_in_allows_overdue_rentals_without_deadlock()
    {
        $user = User::factory()->create(['role' => 'gudang']);
        $customer = $this->makeCustomer();
        $item = $this->makeItem();
        $unit = $this->makeUnit($item->id);

        $rental = Rental::create([
            'rental_code' => 'TRX-TEST-OVERDUE',
            'customer_id' => $customer->id,
            'start_date' => now()->subDays(3),
            'end_date' => now()->subDays(1),
            'scheduled_return_time' => now()->subHours(5),
            'status' => 'OVERDUE', // Previously rejected by CheckIn
            'total_price' => 100000,
            'down_payment_amount' => 100000,
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 50000,
        ]);

        Livewire::actingAs($user)
            ->test(CheckIn::class, ['rentalId' => $rental->id])
            ->set("checkinData.{$detail->id}.condition", 'Baik')
            ->set("checkinData.{$detail->id}.return_status", 'RETURNED')
            ->call('submitCheckIn')
            ->assertHasNoErrors();

        $rental->refresh();
        // Since rental was 5 hours late, it should transition to PENDING_SETTLEMENT, NOT fail with status error!
        $this->assertEquals('PENDING_SETTLEMENT', $rental->status);
    }

    public function test_check_in_deduplicates_automatic_late_penalty()
    {
        $user = User::factory()->create(['role' => 'gudang']);
        $customer = $this->makeCustomer();
        $item = $this->makeItem();
        $unit = $this->makeUnit($item->id);

        $rental = Rental::create([
            'rental_code' => 'TRX-TEST-DEDUP',
            'customer_id' => $customer->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subHours(4),
            'scheduled_return_time' => now()->subHours(4),
            'status' => 'OVERDUE',
            'total_price' => 100000,
            'down_payment_amount' => 100000,
        ]);

        $detail = RentalDetail::create([
            'rental_id' => $rental->id,
            'item_unit_id' => $unit->id,
            'price_per_day' => 50000,
        ]);

        // Background job already created an automatic penalty
        Penalty::create([
            'rental_id' => $rental->id,
            'reason' => 'Denda keterlambatan otomatis (3 jam)',
            'amount' => 15000,
            'is_settled' => false,
        ]);

        Livewire::actingAs($user)
            ->test(CheckIn::class, ['rentalId' => $rental->id])
            ->set("checkinData.{$detail->id}.condition", 'Baik')
            ->set("checkinData.{$detail->id}.return_status", 'RETURNED')
            ->call('submitCheckIn')
            ->assertHasNoErrors();

        // Must still have exactly 1 late penalty record, updated rather than duplicated!
        $latePenalties = Penalty::where('rental_id', $rental->id)->get();
        $this->assertCount(1, $latePenalties);
    }

    public function test_rental_balance_due_accurately_reflects_full_payments()
    {
        $customer = $this->makeCustomer();
        $rental = Rental::create([
            'rental_code' => 'TRX-PAID-FULL',
            'customer_id' => $customer->id,
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'scheduled_return_time' => now()->addDay(),
            'total_price' => 150000,
            'down_payment_amount' => 150000,
            'status' => 'PAID',
        ]);

        Payment::create([
            'rental_id' => $rental->id,
            'type' => 'FULL',
            'method' => 'QRIS',
            'amount' => 150000,
            'paid_at' => now(),
        ]);

        $this->assertEquals(0, $rental->balance_due);
    }

    public function test_ktp_deposit_is_returned_upon_full_settlement()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $customer = $this->makeCustomer();
        $rental = Rental::create([
            'rental_code' => 'TRX-KTP-SETTLE',
            'customer_id' => $customer->id,
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'scheduled_return_time' => now()->subDay(),
            'status' => 'PENDING_SETTLEMENT',
            'total_price' => 100000,
            'down_payment_amount' => 100000,
        ]);

        // Jaminan fisik KTP (amount 0)
        $ktpDeposit = Deposit::create([
            'rental_id' => $rental->id,
            'type' => 'DOC',
            'amount' => 0,
            'doc_type' => 'KTP',
            'status' => 'HELD',
        ]);

        // Penalty 20.000
        $penalty = Penalty::create([
            'rental_id' => $rental->id,
            'reason' => 'Denda kerusakan minor',
            'amount' => 20000,
            'is_settled' => false,
        ]);

        Livewire::actingAs($user)
            ->test(Settlement::class, ['rentalId' => $rental->id])
            ->set('amountPaid', 20000) // Customer pays the penalty
            ->set('additionalPaymentMethod', 'CASH')
            ->call('processSettlement')
            ->assertHasNoErrors();

        $ktpDeposit->refresh();
        // KTP must be RETURNED, NOT FORFEITED!
        $this->assertEquals('RETURNED', $ktpDeposit->status);

        $rental->refresh();
        $this->assertEquals('COMPLETED', $rental->status);
    }

    public function test_customer_form_saves_email()
    {
        $user = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($user)
            ->test(CustomerForm::class)
            ->set('name', 'Pendaki Hebat')
            ->set('nik', '3201123456780099')
            ->set('phone', '081298765432')
            ->set('email', 'pendaki@summitgear.id')
            ->set('address', 'Jl. Merbabu No. 10')
            ->set('has_consent', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'nik' => '3201123456780099',
            'email' => 'pendaki@summitgear.id',
        ]);
    }
}
