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
            ->whereIn('status', ['PENDING_PAYMENT', 'BOOKED'])
            ->count();
    }

    public function render()
    {
        return view('livewire.components.incoming-booking-badge', [
            'count' => $this->count,
        ]);
    }
}
