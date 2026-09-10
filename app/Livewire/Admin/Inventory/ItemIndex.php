<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventoryItem;

class ItemIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $typeFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public $showDeleteModal = false;
    public $itemToDeleteId = null;
    public $itemToDeleteName = '';
    public $itemToDeleteSku = '';
    public $itemToDeleteCategory = '';
    public $itemToDeleteUnitsCount = 0;
    public $itemToDeleteHasBlockers = false;
    public $itemToDeleteBlockerReason = '';

    public function confirmDelete($id)
    {
        $item = InventoryItem::withCount('units')->findOrFail($id);
        $this->itemToDeleteId = $item->id;
        $this->itemToDeleteName = $item->name;
        $this->itemToDeleteSku = $item->sku;
        $this->itemToDeleteCategory = $item->category;
        $this->itemToDeleteUnitsCount = $item->units_count;

        $this->itemToDeleteHasBlockers = false;
        $this->itemToDeleteBlockerReason = '';

        if ($item->units_count > 0) {
            $this->itemToDeleteHasBlockers = true;
            $this->itemToDeleteBlockerReason = "Barang ini masih memiliki {$item->units_count} unit fisik terdaftar. Hapus seluruh unit fisik terkait terlebih dahulu di menu Kelola Unit Fisik.";
        } elseif ($item->packageItems()->count() > 0 || \App\Models\PackageItem::where('component_item_id', $id)->count() > 0) {
            $this->itemToDeleteHasBlockers = true;
            $this->itemToDeleteBlockerReason = "Barang ini terhubung sebagai komponen paket sewa aktif.";
        }

        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->itemToDeleteId = null;
        $this->itemToDeleteName = '';
        $this->itemToDeleteSku = '';
        $this->itemToDeleteCategory = '';
        $this->itemToDeleteUnitsCount = 0;
        $this->itemToDeleteHasBlockers = false;
        $this->itemToDeleteBlockerReason = '';
    }

    public function executeDelete()
    {
        if (!$this->itemToDeleteId || $this->itemToDeleteHasBlockers) {
            return;
        }

        $id = $this->itemToDeleteId;
        $this->cancelDelete();
        $this->delete($id);
    }

    public function delete($id)
    {
        $item = InventoryItem::findOrFail($id);
        
        // Cek jika item punya unit atau merupakan komponen dari paket lain
        if ($item->units()->count() > 0) {
            session()->flash('error', 'Barang ini masih memiliki unit fisik, tidak dapat dihapus.');
            return;
        }

        if ($item->packageItems()->count() > 0 || \App\Models\PackageItem::where('component_item_id', $id)->count() > 0) {
            session()->flash('error', 'Barang ini terkait dengan komponen paket, tidak dapat dihapus.');
            return;
        }

        $item->delete();
        \App\Services\AuditLogger::log('DELETE', 'InventoryItem', $id, "Menghapus master barang: {$item->name}");
        session()->flash('message', "Master barang {$item->name} berhasil dihapus.");
    }

    public function render()
    {
        $items = InventoryItem::withCount('units')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('sku', 'ilike', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->typeFilter !== '', function ($query) {
                $query->where('is_package', $this->typeFilter);
            })
            ->latest()
            ->paginate(10);

        // Kategori bisa diambil dari enum atau query distinct
        $categories = InventoryItem::select('category')->distinct()->pluck('category');

        return view('livewire.admin.inventory.item-index', [
            'items' => $items,
            'categories' => $categories
        ])->layout('components.layouts.app');
    }
}
