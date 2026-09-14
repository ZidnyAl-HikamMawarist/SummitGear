<?php

namespace App\Mail;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rental $rental;

    public function __construct(Rental $rental)
    {
        $this->rental = $rental->load(['customer', 'details.itemUnit.item']);
    }

    public function build()
    {
        return $this->subject("[Invoice Booking] SummitGear Outdoor — {$this->rental->rental_code}")
                    ->view('emails.booking-invoice');
    }
}
