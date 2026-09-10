<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'item_unit_id',
        'type',
        'start_time',
        'end_time',
        'technician_name',
    ];

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class, 'item_unit_id');
    }
}
