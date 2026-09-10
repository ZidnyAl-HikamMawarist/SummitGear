<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    
    
    protected $fillable = ['user_id', 'action', 'entity', 'entity_id', 'reason', 'approved_by', 'timestamp'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    //
}
