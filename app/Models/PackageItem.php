<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageItem extends Model
{
    
    
    protected $fillable = ['package_id', 'component_item_id', 'quantity'];

    public function componentItem()
    {
        return $this->belongsTo(InventoryItem::class, 'component_item_id');
    }

    public function package()
    {
        return $this->belongsTo(InventoryItem::class, 'package_id');
    }

    //
}
