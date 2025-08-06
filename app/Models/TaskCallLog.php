<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TaskCallLog extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_id', 'call_details','notes','checklist','user_id'];
    
    // protected $casts = [
    //     'call_details' => 'json',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function activity()
    {
        return $this->hasOne(AssignActivity::class, 'id', 'assignment_id');
    }

    public function assignedCalls()
    {
        return $this->hasOne(CallAllocation::class, 'call_id', 'id');
    }
}
