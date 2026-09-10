<?php
namespace App\Enums;

enum ReturnStatus: string {
    case PENDING = 'Pending';
    case RETURNED = 'Returned';
    case LATE = 'Late';
}
