<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Rental;

class IncomingBookingBadge extends Component
{
    public $type = 'button'; // 'button' | 'sidebar'

    public function getCountProperty()
    {
        return Rental::where('source', 'online')
            ->where(function ($q) {
                $q->whereIn('status', ['DP_PAID', 'PAID', 'BOOKED'])
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'PENDING_PAYMENT')
                         ->where(function ($ssq) {
                             $ssq->whereNull('expires_at')
                                 ->orWhere('expires_at', '>', \Carbon\Carbon::now());
                         });
                  });
            })
            ->count();
    }

    public function render()
    {
        return view('livewire.components.incoming-booking-badge', [
            'count' => $this->count,
        ]);
    }
}
