<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalDetail extends Model
{
    protected $fillable = [
        'rental_id',
        'item_unit_id',
        'price_per_day',
        'return_status',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class, 'rental_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class, 'item_unit_id');
    }

    public function inventoryItem()
    {
        return $this->hasOneThrough(
            InventoryItem::class,
            ItemUnit::class,
            'id', // Foreign key on item_units table...
            'id', // Foreign key on inventory_items table...
            'item_unit_id', // Local key on rental_details table...
            'item_id' // Local key on item_units table...
        );
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'rental_detail_id');
    }
}
