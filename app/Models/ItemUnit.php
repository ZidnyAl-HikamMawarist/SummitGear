<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    
    protected $fillable = ['item_id', 'serial_number', 'status', 'condition_notes', 'replacement_value'];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function rentalDetails()
    {
        return $this->hasMany(RentalDetail::class, 'item_unit_id');
    }

    public function activeRental()
    {
        return $this->hasOne(RentalDetail::class, 'item_unit_id')
            ->whereHas('rental', function ($q) {
                $q->whereIn('status', ['RENTED_OUT', 'OVERDUE']);
            })->latest()->with('rental');
    }

    //
}
