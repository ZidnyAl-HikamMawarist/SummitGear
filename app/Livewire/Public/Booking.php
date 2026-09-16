<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Customer;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\PricingRule;
use App\Models\Rental;
use App\Models\RentalDetail;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\WhatsAppService;
use App\Mail\BookingInvoiceMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Jobs\ReleaseHoldBookingJob;
use Carbon\Carbon;

class Booking extends Component
{
    // Customer Info
    public $name = '';
    public $email = '';
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
    public $cartStep = 'items'; // 'items' | 'form' | 'payment'
    
    // Progressive Loading / Infinite Scroll
    public $perPage = 12;
    public $totalFilteredItems = 0;
    
    // Totals
    public $total_price = 0;

    // Payment Step & Anti-Hoarding State
    public $activeRentalId = null;
    public $activeRentalCode = null;
    public $expiresAt = null;
    public $remainingSeconds = 600;
    public $paymentOption = 'dp'; // 'dp' (30%) or 'full' (100%)
    public $selectedPaymentMethod = 'qris'; // 'qris', 'gopay', 'bca_va', 'mandiri_va'
    public $isExpiredModalOpen = false;

    // Success Confirmation Modal
    public $showSuccessModal = false;
    public $confirmedBooking = null;

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

        if (!$this->start_date) {
            $this->start_date = now()->format('Y-m-d');
        }
        if (!$this->end_date) {
            $this->end_date = $this->start_date;
        }
        if (!$this->pickup_time) {
            $this->pickup_time = '10:00';
        }

        $this->calculateTotalPrice();
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

    public function updatedStartDate($value)
    {
        $today = now()->format('Y-m-d');
        if ($value && $value < $today) {
            $this->start_date = $today;
        }
        if ($this->start_date && $this->end_date && $this->end_date < $this->start_date) {
            $this->end_date = $this->start_date;
        }
        $this->calculateTotalPrice();
    }

    public function updatedEndDate($value)
    {
        $today = now()->format('Y-m-d');
        $minDate = $this->start_date ?: $today;
        if ($value && $value < $minDate) {
            $this->end_date = $minDate;
        }
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

    public function setCategory($category)
    {
        $this->selectedCategory = $category;
        $this->perPage = 12;
    }

    public function getEstimatedTotalPriceProperty()
    {
        if ($this->duration_days > 0 && $this->total_price > 0) {
            return $this->total_price;
        }

        return $this->subtotal_per_day;
    }

    public function getDownPaymentAmountProperty()
    {
        $target = $this->duration_days > 0 ? $this->total_price : $this->subtotal_per_day;
        $dp = round(($target * 0.30) / 1000) * 1000;
        return (int) max(10000, min($target, $dp));
    }

    public function getPayableAmountProperty()
    {
        $target = $this->duration_days > 0 ? $this->total_price : $this->subtotal_per_day;
        return (int) ($this->paymentOption === 'dp' ? $this->downPaymentAmount : $target);
    }

    public function getRemainingBalanceProperty()
    {
        $target = $this->duration_days > 0 ? $this->total_price : $this->subtotal_per_day;
        return (int) max(0, $target - $this->payableAmount);
    }

    public function getAvailableItemsProperty()
    {
        $cartUnitIds = $this->getAllCartUnitIds();

        $query = InventoryItem::with('packageItems');
        
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

        if ($items->isEmpty()) {
            return $items;
        }

        $hasDates = !empty($this->start_date) && !empty($this->end_date);
        $start = $hasDates ? Carbon::parse($this->start_date)->startOfDay() : null;
        $end = $hasDates ? Carbon::parse($this->end_date)->endOfDay() : null;

        $itemIds = $items->pluck('id');
        $packageComponentIds = $items->where('is_package', true)->flatMap(function ($pkg) {
            return $pkg->packageItems->pluck('component_item_id');
        });
        $allNeededItemIds = $itemIds->merge($packageComponentIds)->unique();

        $unitCountsQuery = ItemUnit::whereIn('item_id', $allNeededItemIds)
            ->where('status', 'Available');

        if (!empty($cartUnitIds)) {
            $unitCountsQuery->whereNotIn('id', $cartUnitIds);
        }

        if ($hasDates) {
            $unitCountsQuery->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                  ->where(function ($subStatus) {
                      $subStatus->where('status', '!=', 'PENDING_PAYMENT')
                                ->orWhere(function ($expQ) {
                                    $expQ->where('status', 'PENDING_PAYMENT')
                                         ->where(function ($inner) {
                                             $inner->whereNull('expires_at')
                                                   ->orWhere('expires_at', '>', Carbon::now());
                                         });
                                });
                  })
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

        $availableCounts = $unitCountsQuery
            ->selectRaw('item_id, count(*) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        $cartQuantities = [];
        foreach ($this->cart as $cItem) {
            $cartQuantities[$cItem['inventory_item_id']] = $cItem['quantity'];
        }

        foreach ($items as $item) {
            if ($item->is_package) {
                $pkgComponents = $item->packageItems;
                if ($pkgComponents->isEmpty()) {
                    $item->available_count = 0;
                } else {
                    $maxPackages = PHP_INT_MAX;
                    foreach ($pkgComponents as $pi) {
                        $compAvailable = (int) ($availableCounts[$pi->component_item_id] ?? 0);
                        $reqQty = max(1, (int) $pi->quantity);
                        $availForComponent = (int) floor($compAvailable / $reqQty);
                        if ($availForComponent < $maxPackages) {
                            $maxPackages = $availForComponent;
                        }
                    }
                    $item->available_count = $maxPackages === PHP_INT_MAX ? 0 : $maxPackages;
                }
            } else {
                $item->available_count = (int) ($availableCounts[$item->id] ?? 0);
            }
            $item->cart_quantity = (int) ($cartQuantities[$item->id] ?? 0);
        }

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
        $invItem = InventoryItem::with('packageItems')->findOrFail($inventoryItemId);

        // Jika item adalah paket (is_package = 1)
        if ($invItem->is_package) {
            $pkgComponents = $invItem->packageItems;
            if ($pkgComponents->isEmpty()) {
                $this->addError('cart', 'Paket ini belum memiliki komponen alat yang dikonfigurasi.');
                return;
            }

            $selectedUnitIds = [];
            $tempCartUnitIds = $cartUnitIds;

            foreach ($pkgComponents as $pi) {
                $reqQty = max(1, (int) $pi->quantity);

                $compQuery = ItemUnit::where('item_id', $pi->component_item_id)
                    ->where('status', 'Available')
                    ->whereNotIn('id', $tempCartUnitIds);

                if ($hasDates) {
                    $start = Carbon::parse($this->start_date)->startOfDay();
                    $end = Carbon::parse($this->end_date)->endOfDay();
                    $compQuery->whereDoesntHave('rentalDetails.rental', function ($q) use ($start, $end) {
                        $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                          ->where(function ($subStatus) {
                              $subStatus->where('status', '!=', 'PENDING_PAYMENT')
                                        ->orWhere(function ($expQ) {
                                            $expQ->where('status', 'PENDING_PAYMENT')
                                                 ->where(function ($inner) {
                                                     $inner->whereNull('expires_at')
                                                           ->orWhere('expires_at', '>', Carbon::now());
                                                 });
                                        });
                          })
                          ->where(function ($query) use ($start, $end) {
                              $query->whereBetween('start_date', [$start, $end])
                                    ->orWhereBetween('end_date', [$start, $end])
                                    ->orWhere(function ($subQuery) use ($start, $end) {
                                        $subQuery->where('start_date', '<=', $start)
                                                 ->where('end_date', '>=', $end);
                                    });
                          });
                    });
                }

                $units = $compQuery->take($reqQty)->get();

                if ($units->count() < $reqQty) {
                    (new ReleaseHoldBookingJob())->handle();
                    $units = $compQuery->take($reqQty)->get();
                }

                if ($units->count() < $reqQty) {
                    $this->addError('cart', 'Stok komponen paket tidak mencukupi untuk tanggal tersebut.');
                    return;
                }

                foreach ($units as $u) {
                    $selectedUnitIds[] = $u->id;
                    $tempCartUnitIds[] = $u->id;
                }
            }

            $foundIndex = null;
            foreach ($this->cart as $index => $item) {
                if ($item['inventory_item_id'] == $inventoryItemId) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex !== null) {
                $this->cart[$foundIndex]['unit_ids'] = array_merge($this->cart[$foundIndex]['unit_ids'], $selectedUnitIds);
                $this->cart[$foundIndex]['quantity']++;
            } else {
                $this->cart[] = [
                    'inventory_item_id' => $invItem->id,
                    'item_name' => $invItem->name,
                    'category' => $invItem->category,
                    'rental_type' => $invItem->rental_type,
                    'base_price' => $invItem->price_per_day,
                    'photo_url' => $invItem->photo_url,
                    'quantity' => 1,
                    'unit_ids' => $selectedUnitIds,
                    'units_per_package' => count($selectedUnitIds),
                    'is_package' => true,
                ];
            }

            $this->calculateTotalPrice();
            return;
        }

        // Regular item (is_package = 0)
        $unitQuery = ItemUnit::with('item')
            ->where('item_id', $inventoryItemId)
            ->where('status', 'Available')
            ->whereNotIn('id', $cartUnitIds);

        if ($hasDates) {
            $start = Carbon::parse($this->start_date)->startOfDay();
            $end = Carbon::parse($this->end_date)->endOfDay();
            $unitQuery->whereDoesntHave('rentalDetails.rental', function($q) use ($start, $end) {
                $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                  ->where(function ($subStatus) {
                      $subStatus->where('status', '!=', 'PENDING_PAYMENT')
                                ->orWhere(function ($expQ) {
                                    $expQ->where('status', 'PENDING_PAYMENT')
                                         ->where(function ($inner) {
                                             $inner->whereNull('expires_at')
                                                   ->orWhere('expires_at', '>', Carbon::now());
                                         });
                                });
                  })
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
            // Lazy release of any expired hold rentals so stock is reclaimed immediately
            $hasExpired = Rental::where('status', Rental::STATUS_PENDING_PAYMENT)
                ->where(function ($q) {
                    $q->where('expires_at', '<=', Carbon::now())
                      ->orWhere(function ($sq) {
                          $sq->whereNull('expires_at')
                             ->where('created_at', '<=', Carbon::now()->subMinutes(10));
                      });
                })
                ->exists();

            if ($hasExpired) {
                (new ReleaseHoldBookingJob())->handle();
                $unit = $unitQuery->first();
            }
        }

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
                'units_per_package' => 1,
                'is_package' => false,
            ];
        }

        $this->calculateTotalPrice();
    }

    public function decreaseQuantity($inventoryItemId)
    {
        foreach ($this->cart as $index => $item) {
            if ($item['inventory_item_id'] == $inventoryItemId) {
                if ($item['quantity'] > 1) {
                    $unitsToPop = !empty($item['is_package']) && !empty($item['units_per_package'])
                        ? (int) $item['units_per_package']
                        : 1;
                    $this->cart[$index]['unit_ids'] = array_slice($this->cart[$index]['unit_ids'], 0, -$unitsToPop);
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
                $dayType = $dayOfWeek <= 5 ? 'weekday' : 'weekend';

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

        if (!$this->start_date) {
            $this->start_date = now()->format('Y-m-d');
        }
        if (!$this->end_date) {
            $this->end_date = $this->start_date;
        }
        if (!$this->pickup_time) {
            $this->pickup_time = '10:00';
        }

        // Clean & sanitize phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$this->phone_number);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '62')) {
            $cleanPhone = substr($cleanPhone, 2);
        }
        $this->phone_number = substr($cleanPhone, 0, 13);
        $this->phone = $this->phone_number ? '+62' . $this->phone_number : '';

        // Clean & sanitize NIK
        $this->nik = substr(preg_replace('/[^0-9]/', '', (string)$this->nik), 0, 16);

        $this->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'nullable|email|max:255',
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
            'address' => 'required|string|min:5|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required',
            'cart' => 'required|array|min:1',
        ], [
            'name.required' => 'Nama lengkap wajib diisi sesuai KTP.',
            'name.min' => 'Nama lengkap minimal 3 karakter.',
            'email.required' => 'Alamat email wajib diisi untuk pengiriman invoice.',
            'email.email' => 'Format alamat email tidak valid.',
            'phone_number.required' => 'Nomor WhatsApp wajib diisi.',
            'phone_number.min' => 'Nomor WhatsApp minimal 9 digit angka (setelah +62).',
            'phone_number.max' => 'Nomor WhatsApp maksimal 13 digit angka.',
            'phone_number.regex' => 'Format nomor WhatsApp harus berupa angka.',
            'nik.required' => 'NIK KTP wajib diisi.',
            'nik.size' => 'NIK KTP harus tepat 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'address.required' => 'Alamat tinggal / domisili wajib diisi.',
            'address.min' => 'Alamat tinggal minimal 5 karakter.',
            'pickup_time.required' => 'Jam pengambilan booking wajib diisi.',
            'cart.min' => 'Pilih minimal 1 barang untuk dipesan.',
        ]);

        // Anti-Hoarding & Booking Limit: Cek apakah user memiliki pending booking aktif
        $activePending = Rental::where('status', Rental::STATUS_PENDING_PAYMENT)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', Carbon::now());
            })
            ->whereHas('customer', function ($q) use ($cleanPhone) {
                $q->where('phone', $this->phone)
                  ->orWhere('phone', '0' . $cleanPhone)
                  ->orWhere('phone', $cleanPhone)
                  ->orWhere('phone', 'like', '%' . $cleanPhone)
                  ->orWhere('nik', $this->nik);
            })
            ->first();

        if ($activePending) {
            $secondsLeft = $activePending->expires_at ? Carbon::now()->diffInSeconds($activePending->expires_at, false) : 600;
            $minsLeft = max(1, ceil($secondsLeft / 60));
            $this->addError('booking_limit', "Anda masih memiliki transaksi booking aktif ({$activePending->rental_code}) yang belum dibayar. Mohon selesaikan pembayaran tersebut atau tunggu {$minsLeft} menit hingga batas waktu berakhir.");
            $this->addError('anti_hoarding', "Anda masih memiliki transaksi booking aktif ({$activePending->rental_code}) yang belum dibayar.");
            return;
        }

        DB::beginTransaction();
        try {
            $start = Carbon::parse($this->start_date)->startOfDay();
            $end = Carbon::parse($this->end_date)->endOfDay();

            // Prepare confirmed items summary
            $confirmedItems = [];
            foreach ($this->cart as $cartGroup) {
                $confirmedItems[] = [
                    'name' => $cartGroup['item_name'],
                    'quantity' => $cartGroup['quantity'],
                    'price_per_day' => (int) $cartGroup['base_price'],
                    'subtotal' => (int) ($cartGroup['base_price'] * $cartGroup['quantity'] * max(1, $this->duration_days)),
                    'photo_url' => $cartGroup['photo_url'] ?? null,
                ];
            }

            // Cegah double-booking / race condition unit dengan PESSIMISTIC LOCKING (ACID Isolation)
            $allUnits = [];
            $allUnitIds = [];
            foreach ($this->cart as $cartGroup) {
                $isPkg = !empty($cartGroup['is_package']);
                $unitCount = count($cartGroup['unit_ids']);
                $pricePerUnit = ($isPkg && $unitCount > 0)
                    ? (int) round(($cartGroup['base_price'] * $cartGroup['quantity']) / $unitCount)
                    : (int) $cartGroup['base_price'];

                foreach ($cartGroup['unit_ids'] as $unitId) {
                    $allUnits[] = [
                        'unit_id' => $unitId,
                        'item_name' => $cartGroup['item_name'],
                        'base_price' => $pricePerUnit,
                    ];
                    $allUnitIds[] = $unitId;
                }
            }

            // Kunci baris unit fisik di PostgreSQL agar transaksi concurrent harus antre
            $lockedUnits = ItemUnit::whereIn('id', $allUnitIds)
                ->where('status', 'Available')
                ->lockForUpdate()
                ->get();

            if ($lockedUnits->count() !== count($allUnitIds)) {
                $this->addError('cart', 'Sebagian unit alat baru saja disewa oleh orang lain. Silakan periksa kembali keranjang Anda.');
                DB::rollBack();
                return;
            }

            foreach ($allUnits as $unitItem) {
                $isConflicted = RentalDetail::where('item_unit_id', $unitItem['unit_id'])
                    ->whereHas('rental', function ($q) use ($start, $end) {
                        $q->whereNotIn('status', ['COMPLETED', 'CANCELLED', 'VOID'])
                          ->where(function ($subStatus) {
                              $subStatus->where('status', '!=', 'PENDING_PAYMENT')
                                        ->orWhere(function ($expQ) {
                                            $expQ->where('status', 'PENDING_PAYMENT')
                                                 ->where(function ($inner) {
                                                     $inner->whereNull('expires_at')
                                                           ->orWhere('expires_at', '>', Carbon::now());
                                                 });
                                        });
                          })
                          ->where(function ($query) use ($start, $end) {
                              $query->whereBetween('start_date', [$start, $end])
                                    ->orWhereBetween('end_date', [$start, $end])
                                    ->orWhere(function ($sub) use ($start, $end) {
                                        $sub->where('start_date', '<=', $start)
                                            ->where('end_date', '>=', $end);
                                    });
                          });
                    })
                    ->lockForUpdate()
                    ->exists();

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
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'nik' => $this->nik,
                    'address' => $this->address,
                    'consent_at' => now(),
                ]);
            } else {
                $customer->update([
                    'name' => $this->name,
                    'email' => $this->email ?: $customer->email,
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

            // Create Rental dengan batas waktu hold 10 menit
            $pickupDateTime = Carbon::parse($this->start_date . ' ' . $this->pickup_time);
            $scheduledReturn = Carbon::parse($this->end_date . ' 18:00:00');
            $expiresAt = Carbon::now()->addMinutes(10);

            $rental = Rental::create([
                'customer_id' => $customer->id,
                'rental_code' => $rentalCode,
                'start_date' => $pickupDateTime,
                'end_date' => $this->end_date,
                'scheduled_return_time' => $scheduledReturn,
                'total_price' => $this->total_price,
                'discount' => 0,
                'status' => Rental::STATUS_PENDING_PAYMENT,
                'down_payment_amount' => 0,
                'payment_type' => 'dp',
                'source' => 'online',
                'expires_at' => $expiresAt,
            ]);

            // Create Rental Details
            foreach ($allUnits as $unitItem) {
                RentalDetail::create([
                    'rental_id' => $rental->id,
                    'item_unit_id' => $unitItem['unit_id'],
                    'price_per_day' => $unitItem['base_price'],
                ]);
            }

            AuditLogger::log('CREATE', 'Rental', $rental->id, "Reservasi online dibuat ({$rentalCode}) dengan hold stok 10 menit. Menunggu pembayaran DP/Lunas.");

            DB::commit();

            // Set state untuk Step 3: Pembayaran / DP dengan Countdown Timer 10 Menit
            $this->activeRentalId = $rental->id;
            $this->activeRentalCode = $rentalCode;
            $this->expiresAt = $expiresAt->toIso8601String();
            $this->remainingSeconds = 600;
            $this->paymentOption = 'dp';
            $this->selectedPaymentMethod = 'qris';
            $this->cartStep = 'payment';
            $this->isCartOpen = true;

            $pickupTolerance = $pickupDateTime->copy()->addHours(2);
            $formattedPickup = $pickupDateTime->translatedFormat('d F Y, \p\u\k\u\l H:i') . ' WIB';
            $toleranceTime = $pickupTolerance->translatedFormat('H:i') . ' WIB';

            // Siapkan payload awal
            $this->confirmedBooking = [
                'rental_code' => $rentalCode,
                'customer_name' => $this->name,
                'customer_email' => $this->email,
                'customer_phone' => $this->phone,
                'customer_nik' => $this->nik,
                'customer_address' => $this->address,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'duration_days' => max(1, $this->duration_days),
                'pickup_time' => $this->pickup_time,
                'pickup_formatted' => $formattedPickup,
                'tolerance_formatted' => $toleranceTime,
                'total_price' => $this->total_price,
                'total_formatted' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),
                'items' => $confirmedItems,
                'created_at' => now()->toIso8601String(),
                'status' => 'MENUNGGU PEMBAYARAN',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('booking', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function setPaymentOption($option)
    {
        if (in_array($option, ['dp', 'full'])) {
            $this->paymentOption = $option;
        }
    }

    public function setPaymentMethod($method)
    {
        $this->selectedPaymentMethod = $method;
    }

    public function checkBookingStatus()
    {
        if (!$this->activeRentalId) return;

        $rental = Rental::find($this->activeRentalId);
        if (!$rental) {
            $this->activeRentalId = null;
            $this->cartStep = 'items';
            return;
        }

        if ($rental->status !== Rental::STATUS_PENDING_PAYMENT) {
            if (in_array($rental->status, [Rental::STATUS_DP_PAID, Rental::STATUS_PAID, Rental::STATUS_BOOKED])) {
                $this->showSuccessModal = true;
                $this->isCartOpen = false;
                $this->cart = [];
                $this->activeRentalId = null;
            } elseif ($rental->status === Rental::STATUS_CANCELLED) {
                $this->handleExpiredBooking();
            }
            return;
        }

        if ($rental->expires_at && Carbon::now()->greaterThan($rental->expires_at)) {
            $this->handleExpiredBooking();
        } else {
            $this->remainingSeconds = max(0, Carbon::now()->diffInSeconds($rental->expires_at, false));
        }
    }

    public function confirmOnlinePayment()
    {
        if (!$this->activeRentalId) {
            $this->addError('payment', 'Sesi booking tidak ditemukan.');
            return;
        }

        DB::beginTransaction();
        try {
            $rental = Rental::with(['customer', 'details.itemUnit.item'])
                ->where('id', $this->activeRentalId)
                ->lockForUpdate()
                ->first();

            if (!$rental) {
                throw new \Exception('Data booking tidak ditemukan.');
            }

            if ($rental->status !== Rental::STATUS_PENDING_PAYMENT) {
                throw new \Exception('Status transaksi tidak valid untuk pembayaran.');
            }

            if ($rental->expires_at && Carbon::now()->greaterThan($rental->expires_at)) {
                $this->handleExpiredBooking();
                DB::rollBack();
                return;
            }

            $isDp = ($this->paymentOption === 'dp');
            $calculatedDp = (int) max(10000, min($rental->total_price, round(($rental->total_price * 0.30) / 1000) * 1000));
            $amount = $isDp ? $calculatedDp : $rental->total_price;
            $paymentType = $isDp ? 'DP' : 'FULL';
            $newStatus = $isDp ? Rental::STATUS_DP_PAID : Rental::STATUS_PAID;

            Payment::create([
                'rental_id' => $rental->id,
                'type' => $paymentType,
                'method' => strtoupper($this->selectedPaymentMethod),
                'amount' => $amount,
                'paid_at' => Carbon::now(),
            ]);

            $rental->update([
                'status' => $newStatus,
                'down_payment_amount' => $isDp ? $amount : $rental->total_price,
                'payment_type' => $this->paymentOption,
                'expires_at' => null, // Countdown selesai & stok aman permanen!
            ]);

            AuditLogger::log(
                'PAYMENT',
                'Rental',
                $rental->id,
                "Pembayaran online berhasil via {$this->selectedPaymentMethod} ({$paymentType}: Rp " . number_format($amount, 0, ',', '.') . "). Status rental menjadi {$newStatus}."
            );

            // Commit transaksi database terlebih dahulu agar lock baris PostgreSQL dilepas seketika
            DB::commit();

            // Kirim notifikasi WA & Email (Di luar transaksi DB agar tidak menahan lock pool database)
            try {
                WhatsAppService::sendBookingConfirmation($rental);
            } catch (\Throwable $waEx) {
                Log::warning("Gagal mengirim WA booking: " . $waEx->getMessage());
            }

            try {
                if (!empty($rental->customer->email)) {
                    Mail::to($rental->customer->email)->send(new BookingInvoiceMail($rental));
                }
            } catch (\Throwable $mailEx) {
                Log::warning("Gagal mengirim email invoice: " . $mailEx->getMessage());
            }

            // Payload konfirmasi booking sukses
            $pickupTime = Carbon::parse($rental->start_date);
            $pickupTolerance = $pickupTime->copy()->addHours(2);
            $formattedPickup = $pickupTime->translatedFormat('d F Y, \p\u\k\u\l H:i') . ' WIB';
            $toleranceTime = $pickupTolerance->translatedFormat('H:i') . ' WIB';

            $items = [];
            foreach ($rental->details as $d) {
                $items[] = [
                    'name' => $d->itemUnit?->item?->name ?? 'Peralatan',
                    'quantity' => 1,
                    'price_per_day' => (int) $d->price_per_day,
                ];
            }

            $this->confirmedBooking = array_merge([
                'rental_code' => $rental->rental_code,
                'customer_name' => $rental->customer->name ?? '',
                'customer_email' => $rental->customer->email ?? '',
                'customer_phone' => $rental->customer->phone ?? '',
                'customer_nik' => $rental->customer->nik ?? '',
                'start_date' => Carbon::parse($rental->start_date)->format('Y-m-d'),
                'end_date' => Carbon::parse($rental->end_date)->format('Y-m-d'),
                'pickup_time' => $pickupTime->format('H:i'),
                'pickup_formatted' => $formattedPickup,
                'tolerance_formatted' => $toleranceTime,
                'total_price' => $rental->total_price,
                'total_formatted' => 'Rp ' . number_format($rental->total_price, 0, ',', '.'),
                'items' => $items,
            ], $this->confirmedBooking ?? [], [
                'down_payment_amount' => $rental->down_payment_amount,
                'balance_due' => $rental->balance_due,
                'payment_type' => $this->paymentOption,
                'payment_method' => strtoupper($this->selectedPaymentMethod),
                'payment_status_label' => $isDp ? 'DP TERBAYAR (30%)' : 'LUNAS (100%)',
                'status' => $isDp ? 'DP TERBAYAR - MENUNGGU DIAMBIL' : 'LUNAS - MENUNGGU DIAMBIL',
            ]);

            $this->isCartOpen = false;
            $this->showSuccessModal = true;
            $this->cart = [];
            $this->total_price = 0;
            $this->activeRentalId = null;

            $this->dispatch('booking-confirmed', booking: $this->confirmedBooking);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('payment', 'Gagal memproses konfirmasi pembayaran: ' . $e->getMessage());
        }
    }

    public function handleExpiredBooking()
    {
        if ($this->activeRentalId) {
            DB::beginTransaction();
            try {
                $rental = Rental::with('details.itemUnit')->find($this->activeRentalId);
                if ($rental && $rental->status === Rental::STATUS_PENDING_PAYMENT) {
                    $rental->update([
                        'status' => Rental::STATUS_CANCELLED,
                        'expires_at' => null,
                    ]);

                    foreach ($rental->details as $detail) {
                        if ($detail->itemUnit) {
                            $detail->itemUnit->update(['status' => 'Available']);
                        }
                    }

                    AuditLogger::log('EXPIRED', 'Rental', $rental->id, "Booking online {$rental->rental_code} dibatalkan otomatis karena melewati batas waktu pembayaran 10 menit. Stok unit dikembalikan ke gudang.");
                }
                DB::commit();
            } catch (\Throwable $t) {
                DB::rollBack();
            }
        }

        $this->isExpiredModalOpen = true;
        $this->activeRentalId = null;
        $this->cartStep = 'items';
    }

    public function cancelActiveBooking()
    {
        if ($this->activeRentalId) {
            DB::beginTransaction();
            try {
                $rental = Rental::with('details.itemUnit')->find($this->activeRentalId);
                if ($rental && $rental->status === Rental::STATUS_PENDING_PAYMENT) {
                    $rental->update([
                        'status' => Rental::STATUS_CANCELLED,
                        'expires_at' => null,
                    ]);

                    foreach ($rental->details as $detail) {
                        if ($detail->itemUnit) {
                            $detail->itemUnit->update(['status' => 'Available']);
                        }
                    }

                    AuditLogger::log('CANCEL', 'Rental', $rental->id, "Pelanggan membatalkan booking online {$rental->rental_code} pada tahap pembayaran. Stok unit kembali ke gudang.");
                }
                DB::commit();
            } catch (\Throwable $t) {
                DB::rollBack();
            }
        }

        $this->activeRentalId = null;
        $this->cartStep = 'items';
    }

    public function closeExpiredModal()
    {
        $this->isExpiredModalOpen = false;
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.public.booking')->layout('components.layouts.guest');
    }
}
