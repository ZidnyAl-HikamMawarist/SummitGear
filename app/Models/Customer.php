<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    
    
    protected $fillable = ['name', 'nik', 'phone', 'address', 'consent_at'];

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'customer_id');
    }
}
