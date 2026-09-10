<?php
namespace App\Enums;

enum ItemUnitStatus: string {
    case AVAILABLE = 'Available';
    case BOOKED = 'Booked';
    case RENTED = 'Rented';
    case RETURNED = 'Returned/Cleaning';
    case MAINTENANCE = 'Maintenance';
    case LOST = 'Lost/Broken';
}
