<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    
    
    protected $fillable = [
        'name',
        'email',
        'nik',
        'phone',
        'address',
        'consent_at',
        'is_blacklisted',
        'blacklist_notes',
    ];

    protected $casts = [
        'is_blacklisted' => 'boolean',
        'consent_at' => 'datetime',
    ];

    public function rentals()
    {
        return $this->hasMany(Rental::class, 'customer_id');
    }
}
