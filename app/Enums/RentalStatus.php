<?php
namespace App\Enums;

enum RentalStatus: string {
    case BOOKED = 'Booked';
    case ACTIVE = 'Active';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
}
