<?php
namespace App\Enums;

enum ConditionCategory: string {
    case GOOD = 'Baik';
    case FAIR = 'Cukup';
    case NEEDS_ATTENTION = 'Perlu Perhatian';
    case BROKEN = 'Rusak';
    case LOST = 'Hilang';
}
