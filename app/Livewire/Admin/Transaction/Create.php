<?php

namespace App\Livewire\Admin\Transaction;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\PricingRule;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Payment;
use App\Models\Deposit;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Create extends Component
{
    // Step 1: Data Pelanggan & Waktu
    public $customer_id;
    public $start_date;
    public $end_date;
    
    // Step 2: Keranjang Sewa
    public $cart = []; // ['unit_id' => 1, 'inventory_item_id' => 1, 'item_name' => 'Tenda', 'serial_number' => 'TEN-001', 'base_price' => 50000, 'rental_type' => 'daily']
    
    // Step 3: Pencarian Grid
    public $searchQuery = '';
    public $selectedCategory = 'all';
    
    // Step 4: Pembayaran
    public $payment_method = 'CASH'; // CASH, TRANSFER, QRIS
    public $payment_amount = '';
    public $total_price = 0;
    public $discount = 0;
    public $is_ktp_valid = false;

    public function updatedPaymentAmount($value)
    {
        if ($value === '' || $value === null) {
            $this->payment_amount = '';
            return;
        }

        $str = (string) $value;
        if (strlen($str) > 1 && $str[0] === '0') {
            $cleaned = ltrim($str, '0');
            $this->payment_amount = $cleaned === '' ? '0' : $cleaned;
        }
    }

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(1)->format('Y-m-d');
    }

    public function getScheduledReturnTimeProperty()
    {
        if (!$this->end_date) return null;
        return Carbon::parse($this->end_date)->endOfDay();
    }

    public function getDurationDaysProperty()
    {
        if (!$this->start_date || !$this->end_date) return 0;
        
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->startOfDay();
        
        if ($start->equalTo($end)) {
            return 1;
        }
        
        return $start->diffInDays($end) + 1;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['start_date', 'end_date'])) {
            $this->calculateTotalPrice();
        }
    }

    public function getCategoriesProperty()
    {
        return InventoryItem::select('category')->distinct()->pluck('category');
    }

    public function getAvailableItemsProperty()
    {
        if (!$this->start_date || !$this->end_date) {
            return collect([]);
        }

        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();
        $cartUnitIds = collect($this->cart)->pluck('unit_id')->toArray();

        // Ambil inventory items
        $query = InventoryItem::query();
        
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
            });
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        $items = $query->get();

        // Hitung available units per item
        $items = $items->map(function ($item) use ($start, $end, $cartUnitIds) {
            $availableCount = ItemUnit::where('item_id', $item->id)
                ->where('status', 'Available')
                ->whereNotIn('id', $cartUnitIds)
                ->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                    $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                      ->where(function($query) use ($start, $end) {
                          $query->whereBetween('start_date', [$start, $end])
                                ->orWhereBetween('end_date', [$start, $end])
                                ->orWhere(function($subQuery) use ($start, $end) {
                                    $subQuery->where('start_date', '<=', $start)
                                             ->where('end_date', '>=', $end);
                                });
                      });
                })->count();
            
            $item->available_count = $availableCount;
            return $item;
        });

        return $items;
    }

    public function addToCart($inventoryItemId)
    {
        if (!$this->start_date || !$this->end_date) {
            $this->addError('cart', 'Tentukan tanggal sewa terlebih dahulu.');
            return;
        }

        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = Carbon::parse($this->end_date)->endOfDay();
        $cartUnitIds = collect($this->cart)->pluck('unit_id')->toArray();

        // Cari 1 unit yang available
        $unit = ItemUnit::with('item')
            ->where('item_id', $inventoryItemId)
            ->where('status', 'Available')
            ->whereNotIn('id', $cartUnitIds)
            ->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                  ->where(function($query) use ($start, $end) {
                      $query->whereBetween('start_date', [$start, $end])
                            ->orWhereBetween('end_date', [$start, $end])
                            ->orWhere(function($subQuery) use ($start, $end) {
                                $subQuery->where('start_date', '<=', $start)
                                         ->where('end_date', '>=', $end);
                            });
                  });
            })
            ->first();

        if (!$unit) {
            $this->addError('cart', 'Stok unit tidak tersedia untuk tanggal tersebut.');
            return;
        }

        $basePrice = $unit->item->price_per_day;

        $this->cart[] = [
            'unit_id' => $unit->id,
            'inventory_item_id' => $unit->item_id,
            'item_name' => $unit->item->name,
            'serial_number' => $unit->serial_number,
            'rental_type' => $unit->item->rental_type,
            'base_price' => $basePrice,
            'photo_url' => $unit->item->photo_url
        ];

        $this->calculateTotalPrice();
    }

    public function removeFromCart($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Re-index
        $this->calculateTotalPrice();
    }

    public function decreaseItem($inventoryItemId)
    {
        $targetIndex = null;
        foreach ($this->cart as $index => $item) {
            if ($item['inventory_item_id'] == $inventoryItemId) {
                $targetIndex = $index;
            }
        }

        if ($targetIndex !== null) {
            $this->removeFromCart($targetIndex);
        }
    }

    public function removeItemCompletely($inventoryItemId)
    {
        $this->cart = array_values(array_filter($this->cart, function ($item) use ($inventoryItemId) {
            return $item['inventory_item_id'] != $inventoryItemId;
        }));
        $this->calculateTotalPrice();
    }

    public function getGroupedCartProperty()
    {
        $grouped = [];
        foreach ($this->cart as $index => $item) {
            $itemId = $item['inventory_item_id'];
            if (!isset($grouped[$itemId])) {
                $grouped[$itemId] = [
                    'inventory_item_id' => $itemId,
                    'item_name' => $item['item_name'],
                    'photo_url' => $item['photo_url'] ?? null,
                    'base_price' => (float) $item['base_price'],
                    'rental_type' => $item['rental_type'] ?? 'daily',
                    'quantity' => 0,
                    'total_base_price' => 0,
                    'serial_numbers' => [],
                    'cart_indexes' => [],
                ];
            }
            $grouped[$itemId]['quantity'] += 1;
            $grouped[$itemId]['total_base_price'] += (float) $item['base_price'];
            $grouped[$itemId]['serial_numbers'][] = $item['serial_number'];
            $grouped[$itemId]['cart_indexes'][] = $index;
        }
        return collect($grouped)->values();
    }

    public function calculateTotalPrice()
    {
        $total = 0;
        $days = $this->duration_days;

        $allRules = PricingRule::all()->keyBy(function ($rule) {
            return $rule->item_id . '-' . $rule->day_type;
        });

        foreach ($this->cart as $item) {
            $itemTotal = 0;
            for ($i = 0; $i < $days; $i++) {
                $currentDate = Carbon::parse($this->start_date)->addDays($i);
                $dayOfWeek = $currentDate->dayOfWeekIso;
                $dayType = $dayOfWeek <= 4 ? 'weekday' : 'weekend';

                $multiplier = 1.0;
                $ruleKey = $item['inventory_item_id'] . '-' . $dayType;

                if (isset($allRules[$ruleKey])) {
                    $multiplier = (float) $allRules[$ruleKey]->price_multiplier;
                }

                $itemTotal += ($item['base_price'] * $multiplier);
            }
            $total += $itemTotal;
        }

        $this->total_price = max(0, $total - $this->discount);
    }

    public function saveTransaction()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'cart' => 'required|array|min:1',
            'payment_method' => 'required|in:CASH,TRANSFER,QRIS',
            'payment_amount' => 'required|numeric|min:0',
            'is_ktp_valid' => 'accepted'
        ], [
            'customer_id.required' => 'Pilih pelanggan terlebih dahulu.',
            'customer_id.exists' => 'Data pelanggan tidak valid.',
            'start_date.required' => 'Tanggal ambil harus diisi.',
            'end_date.required' => 'Tanggal kembali harus diisi.',
            'cart.min' => 'Keranjang sewa tidak boleh kosong.',
            'payment_amount.required' => 'Nominal pembayaran harus diisi.',
            'payment_amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'is_ktp_valid.accepted' => 'Anda harus mengonfirmasi identitas penyewa valid.'
        ]);

        if ($this->payment_amount < $this->total_price) {
            $this->addError('payment_amount', 'Jumlah pembayaran belum lunas/sesuai total.');
            return;
        }

        DB::beginTransaction();
        try {
            $start = Carbon::parse($this->start_date)->startOfDay();
            $end = Carbon::parse($this->end_date)->endOfDay();

            // Cegah race condition / double-booking unit
            foreach ($this->cart as $cartItem) {
                $isConflicted = RentalDetail::where('item_unit_id', $cartItem['unit_id'])
                    ->whereHas('rental', function ($q) use ($start, $end) {
                        $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                          ->where(function ($query) use ($start, $end) {
                              $query->whereBetween('start_date', [$start, $end])
                                    ->orWhereBetween('end_date', [$start, $end])
                                    ->orWhere(function ($sub) use ($start, $end) {
                                        $sub->where('start_date', '<=', $start)
                                            ->where('end_date', '>=', $end);
                                    });
                          });
                    })->exists();

                if ($isConflicted) {
                    $this->addError('cart', "Unit {$cartItem['item_name']} (SN: {$cartItem['serial_number']}) sudah terbooking oleh transaksi lain untuk periode tersebut.");
                    DB::rollBack();
                    return;
                }
            }

            $datePrefix = date('Ymd');
            $lastRental = Rental::where('rental_code', 'like', "TRX-{$datePrefix}-%")
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();
            $nextSeq = $lastRental ? ((int) substr($lastRental->rental_code, -4)) + 1 : 1;
            $rentalCode = "TRX-{$datePrefix}-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $rental = Rental::create([
                'customer_id' => $this->customer_id,
                'rental_code' => $rentalCode,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'scheduled_return_time' => $this->scheduled_return_time,
                'total_price' => $this->total_price,
                'discount' => $this->discount,
                'status' => 'BOOKED', 
                'source' => 'walk_in',
            ]);

            foreach ($this->cart as $item) {
                RentalDetail::create([
                    'rental_id' => $rental->id,
                    'item_unit_id' => $item['unit_id'],
                    'price_per_day' => $item['base_price'],
                ]);
            }

            Payment::create([
                'rental_id' => $rental->id,
                'type' => 'rental',
                'method' => $this->payment_method,
                'amount' => $this->payment_amount,
                'paid_at' => now(),
            ]);

            // Catat jaminan KTP ke tabel deposits jika valid
            if ($this->is_ktp_valid) {
                Deposit::create([
                    'rental_id' => $rental->id,
                    'type' => 'DOC',
                    'amount' => 0,
                    'doc_type' => 'KTP',
                    'status' => 'HELD',
                    'retention_deadline' => Carbon::parse($this->end_date)->addDays(30),
                ]);
            }

            AuditLogger::log('CREATE', 'Rental', $rental->id, "Membuat transaksi baru dari POS: {$rentalCode}");

            DB::commit();

            session()->flash('message', 'Transaksi berhasil dibuat! Kode: ' . $rentalCode);
            return redirect()->route('admin.transactions.invoice', $rental->id);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('checkout', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        return view('livewire.admin.transaction.create', [
            'customers' => $customers,
        ])->layout('components.layouts.app');
    }
}
