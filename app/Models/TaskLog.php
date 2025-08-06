<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'time_entry_id', 'action', 'status'
    ];

    public function timeEntry()
    {
        return $this->belongsTo(TimeEntry::class, 'time_entry_id');
    }
}
