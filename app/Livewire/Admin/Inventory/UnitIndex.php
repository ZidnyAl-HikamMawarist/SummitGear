<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryItem;
use App\Models\ItemUnit;
use App\Services\AuditLogger;

class UnitIndex extends Component
{
    use WithPagination;

    public $item;
    public $search = '';
    public $statusFilter = '';

    // Modal state for edit
    public $isEditModalOpen = false;
    public $editUnitId = null;
    public $editStatus = '';
    public $editCondition = '';
    public $editReplacementValue = 0;

    // Modal state for generate
    public $isGenerateModalOpen = false;
    public $generateQty = 1;
    public $generateReplacementValue = 0;

    public function openGenerateModal()
    {
        $this->generateQty = 1;
        $this->generateReplacementValue = 0;
        $this->isGenerateModalOpen = true;
    }

    public function submitGenerateUnits()
    {
        $this->validate([
            'generateQty' => 'required|integer|min:1|max:100',
            'generateReplacementValue' => 'required|numeric|min:0',
        ]);

        $this->generateUnits($this->generateQty, $this->generateReplacementValue);
        $this->isGenerateModalOpen = false;
    }

    public function mount($itemId)
    {
        $this->item = InventoryItem::findOrFail($itemId);
        if ($this->item->is_package) {
            abort(404, 'Paket tidak memiliki unit fisik.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function generateUnits($qty, $defaultReplacementValue)
    {
        // Generate SN logic: [KAT]-[ITEM_ID]-[NOMOR]
        // Kategori = 3 huruf pertama kategori, uppercase
        $katCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $this->item->category), 0, 3));
        if (strlen($katCode) < 3) $katCode = str_pad($katCode, 3, 'X');
        
        $itemCode = str_pad($this->item->id, 3, '0', STR_PAD_LEFT);

        // Cari nomor urut terakhir
        $lastUnit = ItemUnit::where('item_id', $this->item->id)
            ->where('serial_number', 'like', "{$katCode}-{$itemCode}-%")
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = 0;
        if ($lastUnit) {
            $parts = explode('-', $lastUnit->serial_number);
            if (count($parts) === 3) {
                $lastNumber = intval($parts[2]);
            }
        }

        $created = 0;
        for ($i = 1; $i <= $qty; $i++) {
            $nextNumber = $lastNumber + $i;
            $sn = sprintf("%s-%s-%04d", $katCode, $itemCode, $nextNumber);
            
            ItemUnit::create([
                'item_id' => $this->item->id,
                'serial_number' => $sn,
                'status' => 'Available',
                'condition_notes' => 'Baru',
                'replacement_value' => $defaultReplacementValue,
            ]);
            $created++;
        }

        AuditLogger::log('CREATE', 'ItemUnit', $this->item->id, "Generate {$created} unit baru untuk barang: {$this->item->name}");
        session()->flash('message', "Berhasil menambahkan {$created} unit fisik baru.");
    }

    public function editUnit($id)
    {
        $unit = ItemUnit::findOrFail($id);
        $this->editUnitId = $unit->id;
        $this->editStatus = $unit->status;
        $this->editCondition = $unit->condition_notes;
        $this->editReplacementValue = $unit->replacement_value;
        $this->isEditModalOpen = true;
    }

    public function updateUnit()
    {
        $this->validate([
            'editStatus' => 'required|string',
            'editCondition' => 'nullable|string',
            'editReplacementValue' => 'required|numeric|min:0',
        ]);

        $unit = ItemUnit::findOrFail($this->editUnitId);
        $unit->update([
            'status' => $this->editStatus,
            'condition_notes' => $this->editCondition,
            'replacement_value' => $this->editReplacementValue,
        ]);

        AuditLogger::log('UPDATE', 'ItemUnit', $unit->id, "Update unit {$unit->serial_number}");
        
        $this->isEditModalOpen = false;
        session()->flash('message', "Unit {$unit->serial_number} berhasil diperbarui.");
    }

    public $showDeleteModal = false;
    public $unitToDeleteId = null;
    public $unitToDeleteSn = '';
    public $unitToDeleteStatus = '';
    public $unitToDeleteCondition = '';
    public $unitToDeleteValue = 0;
    public $unitToDeleteIsRented = false;

    public function confirmDeleteUnit($id)
    {
        $unit = ItemUnit::findOrFail($id);
        $this->unitToDeleteId = $unit->id;
        $this->unitToDeleteSn = $unit->serial_number;
        $this->unitToDeleteStatus = $unit->status;
        $this->unitToDeleteCondition = $unit->condition_notes ?? '-';
        $this->unitToDeleteValue = $unit->replacement_value;
        $this->unitToDeleteIsRented = ($unit->status === 'Rented');
        $this->showDeleteModal = true;
    }

    public function cancelDeleteUnit()
    {
        $this->showDeleteModal = false;
        $this->unitToDeleteId = null;
        $this->unitToDeleteSn = '';
        $this->unitToDeleteStatus = '';
        $this->unitToDeleteCondition = '';
        $this->unitToDeleteValue = 0;
        $this->unitToDeleteIsRented = false;
    }

    public function executeDeleteUnit()
    {
        if (!$this->unitToDeleteId || $this->unitToDeleteIsRented) {
            return;
        }

        $id = $this->unitToDeleteId;
        $this->cancelDeleteUnit();
        $this->deleteUnit($id);
    }

    public function deleteUnit($id)
    {
        $unit = ItemUnit::findOrFail($id);
        if ($unit->status === 'Rented') {
            session()->flash('error', 'Unit sedang disewa, tidak dapat dihapus.');
            return;
        }

        $sn = $unit->serial_number;
        $unit->delete();
        
        AuditLogger::log('DELETE', 'ItemUnit', $id, "Hapus unit {$sn}");
        session()->flash('message', "Unit {$sn} berhasil dihapus.");
    }

    public function render()
    {
        $units = ItemUnit::where('item_id', $this->item->id)
            ->when($this->search, function ($query) {
                $query->where('serial_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.inventory.unit-index', [
            'units' => $units
        ])->layout('components.layouts.app');
    }
}
