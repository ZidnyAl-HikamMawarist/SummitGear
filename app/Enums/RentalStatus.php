<?php
namespace App\Enums;

enum RentalStatus: string {
    case PENDING_PAYMENT = 'PENDING_PAYMENT';
    case DP_PAID = 'DP_PAID';
    case PAID = 'PAID';
    case BOOKED = 'BOOKED';
    case ACTIVE = 'ACTIVE';
    case RENTED_OUT = 'RENTED_OUT';
    case OVERDUE = 'OVERDUE';
    case PENDING_SETTLEMENT = 'PENDING_SETTLEMENT';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case VOID = 'VOID';
    case DEFAULTED = 'DEFAULTED';
}
