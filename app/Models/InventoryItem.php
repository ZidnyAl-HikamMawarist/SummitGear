<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    
    
    protected $fillable = ['sku', 'name', 'category', 'is_package', 'rental_type', 'photo_url', 'price_per_day'];

    public function units()
    {
        return $this->hasMany(ItemUnit::class, 'item_id');
    }

    public function pricingRules()
    {
        return $this->hasMany(PricingRule::class, 'item_id');
    }

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class, 'package_id');
    }

    //
}
