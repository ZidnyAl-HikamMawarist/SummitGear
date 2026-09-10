<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\InventoryItem;
use App\Models\PricingRule;
use App\Models\PackageItem;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageOptimizer;

class ItemForm extends Component
{
    use WithFileUploads;

    public $itemId = null;
    
    // Basic Info
    public $sku = '';
    public $name = '';
    public $category = '';
    public $is_package = 0;
    public $rental_type = 'daily';
    public $price_per_day = 0;
    public $photo;
    public $existing_photo_url;

    // Pricing Rules
    public $pricingRules = [];

    // Package Items
    public $packageItems = [];

    public $activeTab = 'basic'; // basic, pricing, package

    public function mount($id = null)
    {
        if ($id) {
            $item = InventoryItem::with(['pricingRules', 'packageItems.componentItem'])->findOrFail($id);
            $this->itemId = $item->id;
            $this->sku = $item->sku;
            $this->name = $item->name;
            $this->category = $item->category;
            $this->is_package = $item->is_package;
            $this->rental_type = $item->rental_type;
            $this->price_per_day = $item->price_per_day ?? 0;
            $this->existing_photo_url = $item->photo_url;

            foreach ($item->pricingRules as $rule) {
                $this->pricingRules[] = [
                    'id' => $rule->id,
                    'day_type' => $rule->day_type,
                    'price_multiplier' => $rule->price_multiplier,
                ];
            }

            foreach ($item->packageItems as $pi) {
                $this->packageItems[] = [
                    'id' => $pi->id,
                    'component_item_id' => $pi->component_item_id,
                    'quantity' => $pi->quantity,
                    'name' => $pi->componentItem->name ?? 'Unknown',
                ];
            }
        }
    }

    public function addPricingRule()
    {
        $this->pricingRules[] = ['id' => null, 'day_type' => 'weekday', 'price_multiplier' => 1.00];
    }

    public function removePricingRule($index)
    {
        unset($this->pricingRules[$index]);
        $this->pricingRules = array_values($this->pricingRules); // reindex
    }

    public function addPackageItem($componentId, $componentName)
    {
        // Cek apakah sudah ada
        foreach ($this->packageItems as $pi) {
            if ($pi['component_item_id'] == $componentId) {
                return;
            }
        }

        $this->packageItems[] = [
            'id' => null,
            'component_item_id' => $componentId,
            'quantity' => 1,
            'name' => $componentName,
        ];
    }

    public function removePackageItem($index)
    {
        unset($this->packageItems[$index]);
        $this->packageItems = array_values($this->packageItems); // reindex
    }

    protected function rules()
    {
        return [
            'sku' => [
                'required',
                'string',
                Rule::unique('inventory_items', 'sku')->ignore($this->itemId),
            ],
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'is_package' => 'boolean',
            'rental_type' => 'required|in:daily,hourly,trip',
            'price_per_day' => 'required|numeric|min:0',
            'photo' => 'nullable|image|max:2048', // Max 2MB
            'pricingRules.*.day_type' => 'required|in:weekday,weekend,holiday',
            'pricingRules.*.price_multiplier' => 'required|numeric|min:0',
            'packageItems.*.component_item_id' => 'required_if:is_package,1|exists:inventory_items,id',
            'packageItems.*.quantity' => 'required_if:is_package,1|integer|min:1',
        ];
    }

    public function save()
    {
        $this->validate();

        $photoPath = $this->existing_photo_url;
        if ($this->photo) {
            $photoPath = ImageOptimizer::optimizeAndStore($this->photo, 'items', 'public', 800, 80);
            if ($this->existing_photo_url && Storage::disk('public')->exists($this->existing_photo_url)) {
                Storage::disk('public')->delete($this->existing_photo_url);
            }
        }

        DB::beginTransaction();
        try {
            if ($this->itemId) {
                $item = InventoryItem::findOrFail($this->itemId);
                $item->update([
                    'sku' => $this->sku,
                    'name' => $this->name,
                    'category' => $this->category,
                    'is_package' => $this->is_package,
                    'rental_type' => $this->rental_type,
                    'price_per_day' => $this->price_per_day,
                    'photo_url' => $photoPath,
                ]);
                AuditLogger::log('UPDATE', 'InventoryItem', $item->id, "Mengupdate master barang: {$this->name}");
            } else {
                $item = InventoryItem::create([
                    'sku' => $this->sku,
                    'name' => $this->name,
                    'category' => $this->category,
                    'is_package' => $this->is_package,
                    'rental_type' => $this->rental_type,
                    'price_per_day' => $this->price_per_day,
                    'photo_url' => $photoPath,
                ]);
                AuditLogger::log('CREATE', 'InventoryItem', $item->id, "Menambah master barang baru: {$this->name}");
            }

            // Sync Pricing Rules
            // Hapus yang lama lalu insert yang baru untuk simpelnya
            $item->pricingRules()->delete();
            foreach ($this->pricingRules as $rule) {
                $item->pricingRules()->create([
                    'day_type' => $rule['day_type'],
                    'price_multiplier' => $rule['price_multiplier'],
                ]);
            }

            // Sync Package Items (Jika ini paket)
            $item->packageItems()->delete();
            if ($this->is_package) {
                foreach ($this->packageItems as $pi) {
                    $item->packageItems()->create([
                        'component_item_id' => $pi['component_item_id'],
                        'quantity' => $pi['quantity'],
                    ]);
                }
            }

            DB::commit();
            session()->flash('message', 'Master barang berhasil disimpan.');
            return redirect()->route('admin.inventory.items');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Get available items for package components (exclude packages to avoid nesting)
        $availableComponents = InventoryItem::where('is_package', 0)
            ->when($this->itemId, function ($q) {
                $q->where('id', '!=', $this->itemId);
            })
            ->get();

        return view('livewire.admin.inventory.item-form', [
            'availableComponents' => $availableComponents
        ])->layout('components.layouts.app');
    }
}
