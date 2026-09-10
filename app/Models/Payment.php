<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    
    protected $fillable = ['rental_id', 'type', 'method', 'amount', 'paid_at'];

    //
}
