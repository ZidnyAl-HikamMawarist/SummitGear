<?php

namespace App\Mail;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PickupReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rental $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental->load(['customer', 'details.itemUnit.item']);
    }

    public function build()
    {
        return $this->subject("[Pengingat Hari-H] Jadwal Pengambilan Alat Outdoor Hari Ini — {$this->rental->rental_code}")
                    ->view('emails.pickup-reminder');
    }
}
