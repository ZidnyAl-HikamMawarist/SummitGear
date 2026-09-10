<?php

namespace App\Livewire\Admin\Operations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Rental;
use Carbon\Carbon;

class HandoverIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'ALL';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $rentals = Rental::with(['customer', 'details.itemUnit.item'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('rental_code', 'ilike', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($cq) {
                          $cq->where('name', 'ilike', '%' . $this->search . '%')
                             ->orWhere('phone', 'ilike', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter !== 'ALL', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        $todayCheckouts = Rental::whereDate('start_date', Carbon::today())
            ->where('status', 'BOOKED')
            ->count();

        $todayReturns = Rental::whereDate('end_date', Carbon::today())
            ->whereIn('status', ['RENTED_OUT', 'OVERDUE'])
            ->count();

        return view('livewire.admin.operations.handover-index', [
            'rentals' => $rentals,
            'todayCheckouts' => $todayCheckouts,
            'todayReturns' => $todayReturns,
        ])->layout('components.layouts.app');
    }
}
