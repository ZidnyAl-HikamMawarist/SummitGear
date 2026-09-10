<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\Payment;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\RentalDetail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RentalBugFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_allows_valid_attributes(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'nik' => '1234567890123456',
            'phone' => '08123456789',
            'address' => 'Jl. Rinjani No. 45',
            'consent_at' => now(),
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'John Doe',
            'nik' => '1234567890123456',
            'phone' => '08123456789',
            'address' => 'Jl. Rinjani No. 45',
        ]);
    }

    public function test_rental_fillable_and_total_price_accessor(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'nik' => '9876543210987654',
            'phone' => '08987654321',
        ]);

        $rental = Rental::create([
            'rental_code' => 'TRX-TEST-001',
            'customer_id' => $customer->id,
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'scheduled_return_time' => now()->addDays(2)->setTime(18, 0),
            'status' => 'BOOKED',
            'total_price' => 150000,
            'discount' => 10000,
            'deposit_amount' => 50000,
        ]);

        $this->assertDatabaseHas('rentals', [
            'rental_code' => 'TRX-TEST-001',
            'total_price' => 150000,
            'discount' => 10000,
            'deposit_amount' => 50000,
        ]);

        $this->assertEquals(150000, $rental->total_price);
    }

    public function test_payment_creates_with_valid_schema(): void
    {
        $customer = Customer::create([
            'name' => 'Bob Smith',
            'nik' => '1122334455667788',
            'phone' => '08112233445',
        ]);

        $rental = Rental::create([
            'rental_code' => 'TRX-TEST-002',
            'customer_id' => $customer->id,
            'start_date' => now(),
            'end_date' => now()->addDays(1),
            'scheduled_return_time' => now()->addDays(1)->setTime(18, 0),
            'status' => 'BOOKED',
            'total_price' => 75000,
        ]);

        $payment = Payment::create([
            'rental_id' => $rental->id,
            'type' => 'rental',
            'method' => 'CASH',
            'amount' => 75000,
            'paid_at' => now(),
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'type' => 'rental',
            'method' => 'CASH',
            'amount' => 75000,
        ]);
    }
}
