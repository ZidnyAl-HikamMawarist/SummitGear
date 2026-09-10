<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Rental extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rental_code',
        'customer_id',
        'start_date',
        'end_date',
        'scheduled_return_time',
        'status',
        'total_price',
        'discount',
        'deposit_amount',
        'source',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(RentalDetail::class, 'rental_id');
    }

    public function rentalDetails()
    {
        return $this->hasMany(RentalDetail::class, 'rental_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'rental_id');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'rental_id');
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'rental_id');
    }

    public function getTotalPriceAttribute()
    {
        if (isset($this->attributes['total_price']) && $this->attributes['total_price'] > 0) {
            return (int) $this->attributes['total_price'];
        }

        $paid = $this->payments->sum('amount');
        if ($paid > 0) {
            return $paid;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        $days = max(1, $start->diffInDays($end) + 1);
        return $this->details->sum('price_per_day') * $days;
    }

    public function getGroupedDetailsAttribute()
    {
        $grouped = [];
        foreach ($this->details as $detail) {
            $itemId = $detail->inventoryItem->id ?? ($detail->itemUnit->item_id ?? 0);
            $itemName = $detail->inventoryItem->name ?? ($detail->itemUnit->item->name ?? 'Alat');
            $sn = $detail->itemUnit->serial_number ?? null;

            if (!isset($grouped[$itemId])) {
                $grouped[$itemId] = [
                    'item_id' => $itemId,
                    'item_name' => $itemName,
                    'quantity' => 0,
                    'price_per_day' => (float) $detail->price_per_day,
                    'total_price_per_day' => 0,
                    'serial_numbers' => [],
                    'details' => collect([]),
                ];
            }

            $grouped[$itemId]['quantity'] += 1;
            $grouped[$itemId]['total_price_per_day'] += (float) $detail->price_per_day;
            if ($sn) {
                $grouped[$itemId]['serial_numbers'][] = $sn;
            }
            $grouped[$itemId]['details']->push($detail);
        }

        return collect($grouped)->values();
    }
}
