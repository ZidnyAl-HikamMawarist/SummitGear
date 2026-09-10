<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\InventoryItem;

class LandingPage extends Component
{
    public $searchQuery = '';
    public $selectedCategory = 'all';

    public function getCategoriesProperty()
    {
        return InventoryItem::select('category')->distinct()->pluck('category');
    }

    public function getFilteredItemsProperty()
    {
        $query = InventoryItem::withCount(['units as available_count' => function ($q) {
            $q->where('status', 'Available');
        }]);

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->searchQuery . '%')
                  ->orWhere('sku', 'ilike', '%' . $this->searchQuery . '%');
            });
        }

        if ($this->selectedCategory !== 'all') {
            $query->where('category', $this->selectedCategory);
        }

        return $query->get()->filter(function ($item) {
            return !empty($item->photo_url) && file_exists(public_path($item->photo_url));
        });
    }

    public function getItemsProperty()
    {
        return $this->filteredItems->take(12);
    }

    public function getHasMoreItemsProperty()
    {
        return $this->filteredItems->count() > 12;
    }

    public function getRemainingCountProperty()
    {
        return max(0, $this->filteredItems->count() - 12);
    }

    public function getTotalItemsProperty()
    {
        return InventoryItem::count();
    }

    public function rentNow()
    {
        return redirect()->route('booking');
    }

    public function render()
    {
        return view('livewire.public.landing-page')
            ->layout('components.layouts.guest');
    }
}
