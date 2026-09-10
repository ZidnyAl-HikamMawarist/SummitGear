<?php

namespace App\Livewire\Admin\Analytics;

use Livewire\Component;
use App\Models\Rental;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\RentalDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $timeRange = '30_DAYS'; // 7_DAYS, 30_DAYS, THIS_MONTH, THIS_YEAR
    public $filterMode = 'PRESET'; // 'PRESET' or 'CUSTOM'
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function setPreset($range)
    {
        $this->timeRange = $range;
        $this->filterMode = 'PRESET';

        // Synchronize dateFrom and dateTo with the preset
        $dates = $this->getDateRange();
        $this->dateFrom = $dates[0]->format('Y-m-d');
        $this->dateTo = $dates[1]->format('Y-m-d');
    }

    public function updatedTimeRange($value)
    {
        if ($value !== 'CUSTOM') {
            $this->filterMode = 'PRESET';
            $dates = $this->getDateRange();
            $this->dateFrom = $dates[0]->format('Y-m-d');
            $this->dateTo = $dates[1]->format('Y-m-d');
        } else {
            $this->filterMode = 'CUSTOM';
        }
    }

    public function updatedDateFrom()
    {
        $this->timeRange = 'CUSTOM';
        $this->filterMode = 'CUSTOM';
    }

    public function updatedDateTo()
    {
        $this->timeRange = 'CUSTOM';
        $this->filterMode = 'CUSTOM';
    }

    public function enableCustomMode()
    {
        $this->timeRange = 'CUSTOM';
        $this->filterMode = 'CUSTOM';
    }

    public function applyCustomRange()
    {
        $this->validate([
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
        ], [
            'dateFrom.required' => 'Tanggal mulai wajib diisi.',
            'dateTo.required' => 'Tanggal akhir wajib diisi.',
            'dateTo.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
        ]);

        $this->timeRange = 'CUSTOM';
        $this->filterMode = 'CUSTOM';
        session()->flash('filter_applied', 'Filter periode berhasil diterapkan: ' . Carbon::parse($this->dateFrom)->format('d M Y') . ' s/d ' . Carbon::parse($this->dateTo)->format('d M Y'));
    }

    public function getDateRange()
    {
        if ($this->filterMode === 'CUSTOM' && $this->dateFrom && $this->dateTo) {
            $start = Carbon::parse($this->dateFrom)->startOfDay();
            $end = Carbon::parse($this->dateTo)->endOfDay();
            return [$start, $end];
        }

        $start = match ($this->timeRange) {
            '1_DAY' => Carbon::now()->startOfDay(),
            '7_DAYS' => Carbon::now()->subDays(7)->startOfDay(),
            '30_DAYS' => Carbon::now()->subDays(30)->startOfDay(),
            'THIS_MONTH' => Carbon::now()->startOfMonth(),
            'THIS_YEAR' => Carbon::now()->startOfYear(),
            default => Carbon::now()->subDays(30)->startOfDay(),
        };

        $end = Carbon::now()->endOfDay();
        return [$start, $end];
    }

    public function getSummaryProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $totalRentals = Rental::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['CANCELLED', 'VOID'])
            ->count();

        $totalRevenue = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $penaltyRevenue = Penalty::whereBetween('created_at', [$startDate, $endDate])
            ->where('is_settled', true)
            ->sum('amount');

        $totalDisputes = Penalty::whereBetween('created_at', [$startDate, $endDate])->count();
        $disputeRate = $totalRentals > 0 ? round(($totalDisputes / $totalRentals) * 100, 1) : 0;

        return [
            'totalRentals' => $totalRentals,
            'totalRevenue' => $totalRevenue,
            'penaltyRevenue' => $penaltyRevenue,
            'disputeRate' => $disputeRate,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function getAssetUtilizationProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        $totalUnits = ItemUnit::count();
        if ($totalUnits === 0) return [];

        return InventoryItem::withCount(['units'])
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $rentedCount = RentalDetail::whereHas('itemUnit', function ($q) use ($item) {
                    $q->where('item_id', $item->id);
                })->whereHas('rental', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                })->count();

                $utilizationPct = $item->units_count > 0 ? min(100, round(($rentedCount / ($item->units_count * 5)) * 100, 1)) : 0;

                return [
                    'name' => $item->name,
                    'category' => $item->category,
                    'units_count' => $item->units_count,
                    'rented_times' => $rentedCount,
                    'utilization_rate' => $utilizationPct,
                ];
            });
    }

    public function getTopItemsProperty()
    {
        [$startDate, $endDate] = $this->getDateRange();

        return InventoryItem::select('inventory_items.name', 'inventory_items.category', DB::raw('count(rental_details.id) as total_rents'), DB::raw('coalesce(sum(rental_details.price_per_day), 0) as total_earned'))
            ->leftJoin('item_units', 'inventory_items.id', '=', 'item_units.item_id')
            ->leftJoin('rental_details', 'item_units.id', '=', 'rental_details.item_unit_id')
            ->leftJoin('rentals', 'rental_details.rental_id', '=', 'rentals.id')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('rentals.created_at', [$startDate, $endDate])
                  ->orWhereNull('rentals.id');
            })
            ->groupBy('inventory_items.id', 'inventory_items.name', 'inventory_items.category')
            ->orderByDesc('total_rents')
            ->take(5)
            ->get();
    }

    public function exportCsv()
    {
        [$startDate, $endDate] = $this->getDateRange();
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        $filename = 'laporan_analitik_summitgear_' . $startDateStr . '_sd_' . $endDateStr . '.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $rentals = Rental::with(['customer', 'payments', 'penalties'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        $callback = function () use ($rentals, $startDateStr, $endDateStr) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Summary Header row
            fputcsv($file, ['SUMMITGEAR POS - LAPORAN ANALITIK & TRANSAKSI']);
            fputcsv($file, ['Periode Laporan', $startDateStr . ' s/d ' . $endDateStr]);
            fputcsv($file, ['Tanggal Export', Carbon::now()->format('Y-m-d H:i:s') . ' WIB']);
            fputcsv($file, ['Total Data', $rentals->count() . ' Transaksi']);
            fputcsv($file, []); // blank row

            // Data Table Headers
            fputcsv($file, [
                'Kode Transaksi',
                'Pelanggan',
                'No Telepon',
                'Tgl Mulai',
                'Tgl Selesai',
                'Status Rental',
                'Total Biaya Sewa (Rp)',
                'Total Pembayaran (Rp)',
                'Denda (Rp)',
                'Tgl Transaksi'
            ]);

            foreach ($rentals as $rental) {
                $totalPaid = $rental->payments->sum('amount');
                $totalPenalty = $rental->penalties->where('is_settled', true)->sum('amount');

                fputcsv($file, [
                    $rental->rental_code,
                    $rental->customer->name ?? '-',
                    $rental->customer->phone ?? '-',
                    $rental->start_date ? Carbon::parse($rental->start_date)->format('Y-m-d') : '-',
                    $rental->end_date ? Carbon::parse($rental->end_date)->format('Y-m-d') : '-',
                    $rental->status,
                    $rental->total_price ?? 0,
                    $totalPaid,
                    $totalPenalty,
                    $rental->created_at ? Carbon::parse($rental->created_at)->format('Y-m-d H:i') : '-'
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.analytics.dashboard', [
            'summary' => $this->summary,
            'utilization' => $this->assetUtilization,
            'topItems' => $this->topItems,
            'activeDateRange' => $this->getDateRange(),
        ])->layout('components.layouts.app');
    }
}
