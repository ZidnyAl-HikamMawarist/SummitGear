<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penalty extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rental_id',
        'reason',
        'amount',
        'is_settled',
        'is_override',
        'override_reason',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }
}
