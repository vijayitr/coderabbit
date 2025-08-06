<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'qc_agent_id',
        'assigned_by',
        'status',
    ];

    /**
     * Get the call associated with this allocation.
     */
    public function call()
    {
        return $this->belongsTo(TaskCallLog::class, 'call_id');
    }

    /**
     * Get the QC agent assigned to this call.
     */
    public function qcAgent()
    {
        return $this->belongsTo(User::class, 'qc_agent_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function score()
    {
        return $this->hasOne(QcScore::class, 'call_allocation_id');
    }

}
