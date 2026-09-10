<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [
        'rental_detail_id',
        'stage',
        'condition_category',
        'notes',
    ];

    public function rentalDetail()
    {
        return $this->belongsTo(RentalDetail::class, 'rental_detail_id');
    }
}
