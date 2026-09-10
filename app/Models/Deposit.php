<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rental_id',
        'type',
        'amount',
        'doc_type',
        'status',
        'retention_deadline',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }
}
