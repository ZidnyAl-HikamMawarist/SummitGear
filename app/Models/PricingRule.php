<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    
    
    protected $fillable = ['item_id', 'day_type', 'price_multiplier'];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    //
}
