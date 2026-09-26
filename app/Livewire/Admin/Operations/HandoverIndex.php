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
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $rentals = Rental::with(['customer', 'details.itemUnit.item'])
            ->when($this->search, function ($query) use ($likeOperator) {
                $query->where(function ($q) use ($likeOperator) {
                    $q->where('rental_code', $likeOperator, '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($cq) use ($likeOperator) {
                          $cq->where('name', $likeOperator, '%' . $this->search . '%')
                             ->orWhere('phone', $likeOperator, '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->statusFilter !== 'ALL', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        $todayCheckouts = Rental::whereDate('start_date', Carbon::today())
            ->whereIn('status', ['BOOKED', 'DP_PAID', 'PAID'])
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
