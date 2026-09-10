<?php

namespace App\Livewire\Admin\Maintenance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ItemUnit;
use App\Models\MaintenanceLog;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;

class Kanban extends Component
{
    use WithPagination;

    // Search filter across Kanban
    public $search = '';

    // View mode: 'tabs' (Segmented Tab Grid) or 'kanban' (4-Column Board)
    public $viewMode = 'tabs';

    // Active Tab in tabs view mode: 'cleaning', 'maintenance', 'rented', 'available'
    public $activeTab = 'cleaning';

    // Units per page in kanban vs tab view
    public $perPage = 6;
    public $tabPerPage = 9;

    protected $queryString = [
        'viewMode' => ['except' => 'tabs'],
        'activeTab' => ['except' => 'cleaning'],
    ];

    // Modal state for recording service
    public $showLogModal = false;
    public $showServiceModal = false;
    public $selectedUnitId = null;
    public $serviceType = 'Pembersihan / Cuci';
    public $technicianName = '';
    public $notes = '';
    public $serviceNote = '';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
    }

    public function updatingSearch()
    {
        $this->resetPage('cleaningPage');
        $this->resetPage('maintenancePage');
        $this->resetPage('rentedPage');
        $this->resetPage('availablePage');
    }

    public function mount()
    {
        $this->technicianName = Auth::user()->name ?? 'Staf Gudang';
    }

    public function moveStatus($unitId, $newStatus)
    {
        $unit = ItemUnit::with('item')->findOrFail($unitId);
        $oldStatus = $unit->status;

        $unit->update([
            'status' => $newStatus,
        ]);

        // Catat otomatis di MaintenanceLog jika pindah ke Available dari Cleaning/Maintenance
        if ($newStatus === 'Available') {
            MaintenanceLog::create([
                'item_unit_id' => $unit->id,
                'type' => $oldStatus === 'Maintenance' ? 'Perbaikan & Servis' : 'Pembersihan Standar',
                'start_time' => now()->subHours(1),
                'end_time' => now(),
                'technician_name' => Auth::user()->name ?? 'Staf Gudang',
            ]);
        }

        AuditLogger::log('UPDATE', 'ItemUnit', $unit->id, "Memindahkan status unit {$unit->serial_number} dari {$oldStatus} ke {$newStatus}");

        session()->flash('message', "Status unit {$unit->serial_number} berhasil diubah ke {$newStatus}!");
    }

    public function openServiceLog($unitId)
    {
        $this->selectedUnitId = $unitId;
        $this->showLogModal = true;
    }

    // New: used by kanban inline service modal
    public function logServiceAndMove($unitId)
    {
        $this->selectedUnitId = $unitId;
        $this->serviceNote = '';
        $this->showServiceModal = true;
    }

    public function cancelModal()
    {
        $this->showServiceModal = false;
        $this->showLogModal = false;
        $this->selectedUnitId = null;
        $this->serviceNote = '';
    }

    public function confirmService()
    {
        if (!$this->selectedUnitId) return;

        $unit = ItemUnit::findOrFail($this->selectedUnitId);

        MaintenanceLog::create([
            'item_unit_id' => $unit->id,
            'type' => 'Perbaikan & Servis',
            'start_time' => now()->subHours(1),
            'end_time' => now(),
            'technician_name' => Auth::user()->name ?? 'Staf Gudang',
            'notes' => $this->serviceNote,
        ]);

        $unit->update([
            'status' => 'Cleaning',
            'condition_notes' => $this->serviceNote ?: 'Servis selesai - perlu cuci ulang',
        ]);

        AuditLogger::log('CREATE', 'MaintenanceLog', $unit->id, "Servis selesai untuk {$unit->serial_number}, dipindahkan ke Cleaning");

        $this->cancelModal();
        session()->flash('message', "Unit {$unit->serial_number} servis selesai & dipindahkan ke antrian cuci!");
    }

    public function saveServiceLog()
    {
        $this->validate([
            'selectedUnitId' => 'required|exists:item_units,id',
            'serviceType' => 'required|string',
            'technicianName' => 'required|string',
        ]);

        $unit = ItemUnit::findOrFail($this->selectedUnitId);

        MaintenanceLog::create([
            'item_unit_id' => $unit->id,
            'type' => $this->serviceType,
            'start_time' => now(),
            'end_time' => now(),
            'technician_name' => $this->technicianName,
        ]);

        $unit->update([
            'status' => 'Available',
            'condition_notes' => 'Telah diservis & siap pakai (' . now()->format('d M Y') . ')',
        ]);

        AuditLogger::log('CREATE', 'MaintenanceLog', $unit->id, "Mencatat log servis {$this->serviceType} oleh {$this->technicianName}");

        $this->showLogModal = false;
        session()->flash('message', "Log servis untuk unit {$unit->serial_number} berhasil dicatat & unit siap sewa!");
    }

    public function render()
    {
        // Real total counts for KPI cards & column header badges
        $cleaningCount = ItemUnit::where('status', 'Cleaning')->count();
        $maintenanceCount = ItemUnit::where('status', 'Maintenance')->count();
        $availableCount = ItemUnit::where('status', 'Available')->count();
        $rentedCount = ItemUnit::where('status', 'Rented')->count();

        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        // Query builder with search
        $buildQuery = function ($status) use ($likeOperator) {
            $search = trim($this->search);
            return ItemUnit::with(['item', 'activeRental.rental'])
                ->where('status', $status)
                ->when($search, function ($query) use ($search, $likeOperator) {
                    $query->where(function ($q) use ($search, $likeOperator) {
                        $q->where('serial_number', $likeOperator, '%' . $search . '%')
                          ->orWhereHas('item', fn($iq) => $iq->where('name', $likeOperator, '%' . $search . '%'));
                    });
                })
                ->orderBy('id', 'asc');
        };

        $effectivePerPage = $this->viewMode === 'tabs' ? $this->tabPerPage : $this->perPage;

        // Paginated units per column / tab
        $cleaningUnits = $buildQuery('Cleaning')->paginate($effectivePerPage, ['*'], 'cleaningPage');
        $maintenanceUnits = $buildQuery('Maintenance')->paginate($effectivePerPage, ['*'], 'maintenancePage');
        $availableUnits = $buildQuery('Available')->paginate($effectivePerPage, ['*'], 'availablePage');
        $rentedUnits = $buildQuery('Rented')->paginate($effectivePerPage, ['*'], 'rentedPage');

        $selectedUnit = $this->selectedUnitId ? ItemUnit::with('item')->find($this->selectedUnitId) : null;

        $recentLogs = MaintenanceLog::with('itemUnit.item')->latest()->take(5)->get();

        return view('livewire.admin.maintenance.kanban', [
            'cleaningUnits' => $cleaningUnits,
            'maintenanceUnits' => $maintenanceUnits,
            'availableUnits' => $availableUnits,
            'rentedUnits' => $rentedUnits,
            'cleaningCount' => $cleaningCount,
            'maintenanceCount' => $maintenanceCount,
            'availableCount' => $availableCount,
            'rentedCount' => $rentedCount,
            'recentLogs' => $recentLogs,
            'selectedUnit' => $selectedUnit,
            'viewMode' => $this->viewMode,
            'activeTab' => $this->activeTab,
        ])->layout('components.layouts.app');
    }
}
