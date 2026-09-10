<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\PricingRule;
use App\Models\Rental;
use App\Models\RentalDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Booking extends Component
{
    // Customer Info
    public $name = '';
    public $phone = '';
    public $phone_number = '';
    public $nik = '';
    public $address = '';

    // Booking Dates & Time
    public $start_date;
    public $end_date;
    public $pickup_time = '10:00';

    // Cart & Grid
    public $cart = [];
    public $searchQuery = '';
    public $selectedCategory = 'all';
    public $isCartOpen = false;
    public $cartStep = 'items'; // 'items' | 'form'
    
    // Progressive Loading / Infinite Scroll
    public $perPage = 12;
    public $totalFilteredItems = 0;
    
    // Totals
    public $total_price = 0;

    public function mount()
    {
        $this->start_date = null;
        $this->end_date = null;
    }

    public function openCart()
    {
        $this->isCartOpen = true;
    }

    public function closeCart()
    {
        $this->isCartOpen = false;
    }

    public function toggleCart()
    {
        $this->isCartOpen = !$this->isCartOpen;
    }

    public function setStep($step)
    {
        if ($step === 'form' && empty($this->cart)) {
            $this->addError('cart', 'Keranjang masih kosong. Silakan pilih alat terlebih dahulu.');
            return;
        }
        $this->cartStep = $step;
    }

    public function proceedToForm()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang masih kosong. Silakan pilih alat terlebih dahulu.');
            return;
        }
        $this->cartStep = 'form';
    }

    public function backToItems()
    {
        $this->cartStep = 'items';
    }

    public function setDuration($days)
    {
        if (!$this->start_date) {
            $this->start_date = now()->format('Y-m-d');
        }
        $this->end_date = Carbon::parse($this->start_date)->addDays(max(0, $days - 1))->format('Y-m-d');
        $this->calculateTotalPrice();
    }

    public function getSubtotalPerDayProperty()
    {
        return (float) collect($this->cart)->sum(function ($item) {
            return $item['base_price'] * $item['quantity'];
        });
    }

    public function updatedPhoneNumber($value)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($cleaned, '0')) {
            $cleaned = substr($cleaned, 1);
        } elseif (str_starts_with($cleaned, '62')) {
            $cleaned = substr($cleaned, 2);
        }
        $this->phone_number = substr($cleaned, 0, 13);
        $this->phone = $this->phone_number ? '+62' . $this->phone_number : '';
    }

    public function updatedNik($value)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        $this->nik = substr($cleaned, 0, 16);
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

    public function getTotalCartCountProperty()
    {
        return (int) collect($this->cart)->sum('quantity');
    }

    public function getAllCartUnitIds()
    {
        $ids = [];
        foreach ($this->cart as $item) {
            if (isset($item['unit_ids']) && is_array($item['unit_ids'])) {
                foreach ($item['unit_ids'] as $uid) {
                    $ids[] = $uid;
                }
            } elseif (isset($item['unit_id'])) {
                $ids[] = $item['unit_id'];
            }
        }
        return $ids;
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

    public function loadMore()
    {
        $this->perPage += 12;
    }

    public function updatedSearchQuery()
    {
        $this->perPage = 12;
    }

    public function updatedSelectedCategory()
    {
        $this->perPage = 12;
    }

    public function getAvailableItemsProperty()
    {
        $cartUnitIds = $this->getAllCartUnitIds();

        $query = InventoryItem::query();
        
        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->searchQuery . '%')
                  ->orWhere('sku', 'ilike', '%' . $this->searchQuery . '%');
            });
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        $this->totalFilteredItems = (clone $query)->count();

        // Ambil hanya sejumlah $perPage yang terlihat di layar untuk performa maksimal
        $items = $query->take($this->perPage)->get();

        $hasDates = !empty($this->start_date) && !empty($this->end_date);
        $start = $hasDates ? Carbon::parse($this->start_date)->startOfDay() : null;
        $end = $hasDates ? Carbon::parse($this->end_date)->endOfDay() : null;

        $items->map(function ($item) use ($hasDates, $start, $end, $cartUnitIds) {
            $unitQuery = ItemUnit::where('item_id', $item->id)
                ->where('status', 'Available')
                ->whereNotIn('id', $cartUnitIds);

            if ($hasDates) {
                $unitQuery->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                    $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                      ->where(function($query) use ($start, $end) {
                          $query->whereBetween('start_date', [$start, $end])
                                ->orWhereBetween('end_date', [$start, $end])
                                ->orWhere(function($subQuery) use ($start, $end) {
                                    $subQuery->where('start_date', '<=', $start)
                                             ->where('end_date', '>=', $end);
                                });
                      });
                });
            }

            $availableCount = $unitQuery->count();
            $item->available_count = $availableCount;
            $inCart = collect($this->cart)->firstWhere('inventory_item_id', $item->id);
            $item->cart_quantity = $inCart ? $inCart['quantity'] : 0;
            return $item;
        });

        return $items;
    }

    public function getHasMorePagesProperty()
    {
        return $this->totalFilteredItems > $this->perPage;
    }

    public function addToCart($inventoryItemId)
    {
        $hasDates = !empty($this->start_date) && !empty($this->end_date);
        $cartUnitIds = $this->getAllCartUnitIds();

        $unitQuery = ItemUnit::with('item')
            ->where('item_id', $inventoryItemId)
            ->where('status', 'Available')
            ->whereNotIn('id', $cartUnitIds);

        if ($hasDates) {
            $start = Carbon::parse($this->start_date)->startOfDay();
            $end = Carbon::parse($this->end_date)->endOfDay();
            $unitQuery->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                  ->where(function($query) use ($start, $end) {
                      $query->whereBetween('start_date', [$start, $end])
                            ->orWhereBetween('end_date', [$start, $end])
                            ->orWhere(function($subQuery) use ($start, $end) {
                                $subQuery->where('start_date', '<=', $start)
                                         ->where('end_date', '>=', $end);
                            });
                  });
            });
        }

        $unit = $unitQuery->first();

        if (!$unit) {
            $this->addError('cart', $hasDates ? 'Stok unit tidak tersedia untuk tanggal tersebut.' : 'Stok unit tidak tersedia.');
            return;
        }

        // Cari apakah item sudah ada di keranjang untuk digabungkan
        $foundIndex = null;
        foreach ($this->cart as $index => $item) {
            if ($item['inventory_item_id'] == $inventoryItemId) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== null) {
            $this->cart[$foundIndex]['unit_ids'][] = $unit->id;
            $this->cart[$foundIndex]['quantity']++;
        } else {
            $basePrice = $unit->item->price_per_day;
            $this->cart[] = [
                'inventory_item_id' => $unit->item_id,
                'item_name' => $unit->item->name,
                'category' => $unit->item->category,
                'rental_type' => $unit->item->rental_type,
                'base_price' => $basePrice,
                'photo_url' => $unit->item->photo_url,
                'quantity' => 1,
                'unit_ids' => [$unit->id],
            ];
        }

        $this->calculateTotalPrice();
    }

    public function decreaseQuantity($inventoryItemId)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['inventory_item_id'] == $inventoryItemId) {
                if ($item['quantity'] > 1) {
                    array_pop($this->cart[$index]['unit_ids']);
                    $this->cart[$index]['quantity']--;
                } else {
                    unset($this->cart[$index]);
                    $this->cart = array_values($this->cart);
                }
                break;
            }
        }
        $this->calculateTotalPrice();
    }

    public function removeFromCart($inventoryItemId)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['inventory_item_id'] == $inventoryItemId) {
                unset($this->cart[$index]);
                $this->cart = array_values($this->cart);
                break;
            }
        }
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        if (!$this->start_date || !$this->end_date || $this->duration_days <= 0) {
            $this->total_price = 0;
            return;
        }

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
            $total += ($itemTotal * $item['quantity']);
        }

        $this->total_price = max(0, $total);
    }

    public function submitBooking()
    {
        $this->cartStep = 'form';

        if ($this->phone_number && !$this->phone) {
            $this->phone = '+62' . $this->phone_number;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'min:9',
                'max:13',
                'regex:/^[0-9]{9,13}$/'
            ],
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/'
            ],
            'address' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required|date_format:H:i',
            'cart' => 'required|array|min:1',
        ], [
            'name.required' => 'Nama lengkap wajib diisi sesuai KTP.',
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.min' => 'Nomor WhatsApp minimal 9 digit angka (setelah +62).',
            'phone_number.max' => 'Nomor WhatsApp maksimal 13 digit angka.',
            'phone_number.regex' => 'Format nomor WhatsApp harus berupa angka.',
            'nik.required' => 'NIK KTP wajib diisi.',
            'nik.size' => 'NIK KTP harus tepat 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'address.required' => 'Alamat tinggal wajib diisi.',
            'pickup_time.required' => 'Jam pengambilan booking wajib diisi.',
            'pickup_time.date_format' => 'Format jam pengambilan tidak valid (HH:MM).',
            'cart.min' => 'Pilih minimal 1 barang untuk dipesan.',
        ]);

        DB::beginTransaction();
        try {
            $start = Carbon::parse($this->start_date)->startOfDay();
            $end = Carbon::parse($this->end_date)->endOfDay();

            // Cegah double-booking / race condition unit
            $allUnits = [];
            foreach ($this->cart as $cartGroup) {
                foreach ($cartGroup['unit_ids'] as $unitId) {
                    $allUnits[] = [
                        'unit_id' => $unitId,
                        'item_name' => $cartGroup['item_name'],
                        'base_price' => $cartGroup['base_price'],
                    ];
                }
            }

            foreach ($allUnits as $unitItem) {
                $isConflicted = RentalDetail::where('item_unit_id', $unitItem['unit_id'])
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
                    $this->addError('cart', "Unit {$unitItem['item_name']} sudah terbooking oleh orang lain untuk tanggal tersebut.");
                    DB::rollBack();
                    return;
                }
            }

            // Temukan customer berdasarkan nomor telepon atau NIK
            $customerByPhone = Customer::where('phone', $this->phone)->first();
            $customerByNik = Customer::where('nik', $this->nik)->first();

            if ($customerByPhone && $customerByNik && $customerByPhone->id !== $customerByNik->id) {
                $this->addError('phone_number', 'Nomor telepon dan NIK terdaftar pada dua data pelanggan yang berbeda.');
                DB::rollBack();
                return;
            }

            $customer = $customerByPhone ?? $customerByNik;

            if (!$customer) {
                $customer = Customer::create([
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'nik' => $this->nik,
                    'address' => $this->address,
                    'consent_at' => now(),
                ]);
            } else {
                $customer->update([
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'nik' => $this->nik,
                    'address' => $this->address ?: $customer->address,
                ]);
            }

            // Generate Rental Code
            $datePrefix = date('Ymd');
            $lastRental = Rental::where('rental_code', 'like', "TRX-{$datePrefix}-%")->orderBy('id', 'desc')->first();
            $nextSeq = $lastRental ? ((int) substr($lastRental->rental_code, -4)) + 1 : 1;
            $rentalCode = "TRX-{$datePrefix}-" . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            // Create Rental (PENDING_PAYMENT, online)
            $pickupDateTime = Carbon::parse($this->start_date . ' ' . $this->pickup_time);
            $scheduledReturn = Carbon::parse($this->end_date . ' 18:00:00');

            $rental = Rental::create([
                'customer_id' => $customer->id,
                'rental_code' => $rentalCode,
                'start_date' => $pickupDateTime,
                'end_date' => $this->end_date,
                'scheduled_return_time' => $scheduledReturn,
                'total_price' => $this->total_price,
                'discount' => 0,
                'status' => 'PENDING_PAYMENT',
                'source' => 'online',
            ]);

            // Create Rental Details
            foreach ($allUnits as $unitItem) {
                RentalDetail::create([
                    'rental_id' => $rental->id,
                    'item_unit_id' => $unitItem['unit_id'],
                    'price_per_day' => $unitItem['base_price'],
                ]);
            }

            // Kirim notifikasi WA (Mocking / Live)
            \App\Services\WhatsAppService::sendBookingConfirmation($rental);

            DB::commit();

            // Clear cart
            $this->cart = [];
            $this->total_price = 0;
            
            $formattedPickup = $pickupDateTime->translatedFormat('d M Y \p\u\k\u\l H:i') . ' WIB';
            session()->flash('message', "Booking berhasil dengan kode: <strong>{$rentalCode}</strong>.<br>Jadwal Pengambilan: <strong>{$formattedPickup}</strong>.<br><span class='text-xs text-amber-700 font-semibold'>*Perhatian: Toleransi batas pengambilan maksimal 2 jam setelah jadwal. Jika barang tidak diambil, kasir berhak membatalkan booking dan stok unit kembali ke gudang.</span>");
            return redirect()->route('home');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('booking', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.public.booking')->layout('components.layouts.guest');
    }
}
