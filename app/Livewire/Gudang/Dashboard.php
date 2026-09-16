<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use App\Models\ItemUnit;
use App\Models\InventoryItem;
use App\Models\Rental;
use App\Models\MaintenanceLog;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    // Filter kategori untuk pantauan stok cepat
    public $selectedCategory = 'all';
    public $searchUnit = '';

    public function quickMoveToAvailable($unitId)
    {
        $unit = ItemUnit::with('item')->findOrFail($unitId);
        $oldStatus = $unit->status;

        $unit->update([
            'status' => 'Available',
            'condition_notes' => 'Siap Sewa (Inspeksi Gudang Selesai)',
        ]);

        MaintenanceLog::create([
            'item_unit_id' => $unit->id,
            'type' => $oldStatus === 'Maintenance' ? 'Perbaikan & Servis Selesai' : 'Pembersihan Selesai',
            'start_time' => now()->subHours(1),
            'end_time' => now(),
            'technician_name' => Auth::user()->name ?? 'Staf Gudang',
            'notes' => 'Disetujui kembali ke rak siap sewa melalui Dashboard Gudang',
        ]);

        AuditLogger::log('UPDATE', 'ItemUnit', $unit->id, "Gudang memindahkan unit {$unit->serial_number} dari {$oldStatus} ke Available (Siap Sewa)");

        session()->flash('message', "Unit {$unit->serial_number} ({$unit->item->name}) kini telah SIAP SEWA di rak gudang!");
    }

    public function render()
    {
        $today = Carbon::today();

        // 1. Metrik Status Unit Fisik
        $totalUnits = ItemUnit::count();
        $availableUnits = ItemUnit::where('status', 'Available')->count();
        $rentedUnits = ItemUnit::where('status', 'Rented')->count();
        $cleaningUnits = ItemUnit::where('status', 'Cleaning')->count();
        $maintenanceUnits = ItemUnit::where('status', 'Maintenance')->count();
        $brokenOrMissingUnits = ItemUnit::whereIn('status', ['Maintenance', 'Lost', 'Broken', 'Missing'])->count();

        // Persentase Kesiapan Gudang
        $availabilityRate = $totalUnits > 0 ? round(($availableUnits / $totalUnits) * 100) : 0;

        // 2. Operasional Serah Terima Hari Ini (Handover Dispatch & Receiving)
        // Pengambilan Hari Ini (Check-out): Pelanggan yang akan mengambil alat hari ini
        $todayPickups = Rental::with(['customer', 'details.itemUnit.item'])
            ->whereDate('start_date', $today)
            ->whereIn('status', ['BOOKED', 'DP_PAID', 'PAID'])
            ->orderBy('start_date', 'asc')
            ->get();

        // Pengembalian Hari Ini (Check-in QC): Pelanggan yang harus mengembalikan alat hari ini
        $todayReturns = Rental::with(['customer', 'details.itemUnit.item'])
            ->whereDate('end_date', '<=', $today)
            ->whereIn('status', ['RENTED_OUT', 'OVERDUE'])
            ->orderBy('scheduled_return_time', 'asc')
            ->get();

        // 3. Peringatan Stok Menipis (Barang yang sisa Available <= 1)
        $lowStockItems = InventoryItem::where('is_package', 0)
            ->withCount([
                'units as available_units_count' => function ($query) {
                    $query->where('status', 'Available');
                },
                'units as total_units_count'
            ])
            ->havingRaw('COUNT(CASE WHEN item_units.status = \'Available\' THEN 1 END) <= 1')
            ->leftJoin('item_units', 'inventory_items.id', '=', 'item_units.item_id')
            ->groupBy('inventory_items.id')
            ->orderBy('available_units_count', 'asc')
            ->take(5)
            ->get();

        // 4. Antrean Perawatan Aktif (Cuci & Maintenance)
        $activeMaintenanceUnits = ItemUnit::with('item')
            ->whereIn('status', ['Cleaning', 'Maintenance'])
            ->when($this->searchUnit, function($q) {
                $q->where(function($sub) {
                    $sub->where('serial_number', 'ilike', '%' . $this->searchUnit . '%')
                        ->orWhereHas('item', function($iq) {
                            $iq->where('name', 'ilike', '%' . $this->searchUnit . '%');
                        });
                });
            })
            ->latest('updated_at')
            ->take(8)
            ->get();

        // 5. Riwayat Aktivitas Perawatan Terakhir
        $recentMaintenanceLogs = MaintenanceLog::with(['itemUnit.item'])
            ->latest()
            ->take(5)
            ->get();

        // 6. Ringkasan Kategori Unit
        $categoryBreakdown = InventoryItem::select('category', DB::raw('count(item_units.id) as total_units'), DB::raw('COUNT(CASE WHEN item_units.status = \'Available\' THEN 1 END) as ready_units'))
            ->leftJoin('item_units', 'inventory_items.id', '=', 'item_units.item_id')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('livewire.gudang.dashboard', [
            'totalUnits' => $totalUnits,
            'availableUnits' => $availableUnits,
            'rentedUnits' => $rentedUnits,
            'cleaningUnits' => $cleaningUnits,
            'maintenanceUnits' => $maintenanceUnits,
            'brokenOrMissingUnits' => $brokenOrMissingUnits,
            'availabilityRate' => $availabilityRate,
            'todayPickups' => $todayPickups,
            'todayReturns' => $todayReturns,
            'lowStockItems' => $lowStockItems,
            'activeMaintenanceUnits' => $activeMaintenanceUnits,
            'recentMaintenanceLogs' => $recentMaintenanceLogs,
            'categoryBreakdown' => $categoryBreakdown,
        ])->layout('components.layouts.app', ['title' => 'Dashboard Gudang & Manajemen Unit']);
    }
}
