<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Models\RentalDetail;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarIndex extends Component
{
    use WithPagination;

    public $startDate;
    public $daysToShow = 14;
    public $categoryFilter = 'ALL';
    public $bookingWindowDays = 7;

    public function mount()
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
        
        $window = DB::table('settings')->where('key', 'booking_window_days')->value('value');
        if ($window) {
            $this->bookingWindowDays = (int)$window;
        }
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function nextPeriod()
    {
        $this->startDate = Carbon::parse($this->startDate)->addDays(7)->format('Y-m-d');
    }

    public function prevPeriod()
    {
        $this->startDate = Carbon::parse($this->startDate)->subDays(7)->format('Y-m-d');
    }

    public function todayPeriod()
    {
        $this->startDate = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate);
        $dates = [];
        for ($i = 0; $i < $this->daysToShow; $i++) {
            $dates[] = $start->copy()->addDays($i);
        }

        $categories = InventoryItem::distinct()->pluck('category');

        $units = ItemUnit::with(['item'])
            ->when($this->categoryFilter !== 'ALL', function ($q) {
                $q->whereHas('item', function ($iq) {
                    $iq->where('category', $this->categoryFilter);
                });
            })
            ->orderBy('item_id')
            ->orderBy('serial_number')
            ->paginate(10);

        // Get rentals covering this period
        $periodStart = $start->copy()->startOfDay();
        $periodEnd = $start->copy()->addDays($this->daysToShow)->endOfDay();

        $activeDetails = RentalDetail::with(['rental.customer', 'itemUnit'])
            ->whereHas('rental', function ($q) use ($periodStart, $periodEnd) {
                $q->whereNotIn('status', ['CANCELLED', 'VOID'])
                  ->where(function ($query) use ($periodStart, $periodEnd) {
                      $query->whereBetween('start_date', [$periodStart, $periodEnd])
                            ->orWhereBetween('end_date', [$periodStart, $periodEnd])
                            ->orWhere(function ($sub) use ($periodStart, $periodEnd) {
                                $sub->where('start_date', '<=', $periodStart)
                                    ->where('end_date', '>=', $periodEnd);
                            });
                  });
            })
            ->get();

        // Build availability matrix: [unit_id][date_str] => status info
        $matrix = [];
        foreach ($units as $unit) {
            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // Physical status check first
                if ($unit->status === 'Maintenance') {
                    $matrix[$unit->id][$dateStr] = ['status' => 'MAINTENANCE', 'label' => 'Servis'];
                    continue;
                }
                if ($unit->status === 'Cleaning') {
                    $matrix[$unit->id][$dateStr] = ['status' => 'CLEANING', 'label' => 'Cuci'];
                    continue;
                }
                if ($unit->status === 'Lost') {
                    $matrix[$unit->id][$dateStr] = ['status' => 'LOST', 'label' => 'Hilang'];
                    continue;
                }

                // Check rental overlap
                $match = $activeDetails->first(function ($d) use ($unit, $date) {
                    if ($d->item_unit_id !== $unit->id) return false;
                    $rStart = Carbon::parse($d->rental->start_date)->startOfDay();
                    $rEnd = Carbon::parse($d->rental->end_date)->endOfDay();
                    return $date->between($rStart, $rEnd);
                });

                if ($match) {
                    $status = $match->rental->status;
                    $matrix[$unit->id][$dateStr] = [
                        'status' => $status,
                        'label' => $match->rental->customer->name ?? $match->rental->rental_code,
                        'rental_id' => $match->rental->id,
                        'rental_code' => $match->rental->rental_code,
                    ];
                } else {
                    $matrix[$unit->id][$dateStr] = ['status' => 'AVAILABLE', 'label' => 'Ready'];
                }
            }
        }

        return view('livewire.admin.operations.calendar-index', [
            'dates' => $dates,
            'units' => $units,
            'matrix' => $matrix,
            'categories' => $categories,
        ])->layout('components.layouts.app');
    }
}
