<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'process_id',
        'assignment_id',
        'activity',
        'client_id',
        'status',
        'comments',
        'category',
        'sub_category',
        'start_time',
        'end_time',
        'type',
        'break_option'
    ];

    /**
     * Get the user that owns the time entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the process associated with the time entry.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function process()
    {
        return $this->belongsTo(WorkflowProcessName::class, 'process_id');
    }

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class, 'time_entry_id');
    }

    public  function isIdle()
    {
        return $this->where('type', 'idle')
                    ->whereNull('end_time')
                    ->where('user_id', Auth::guard('web')->user()->id)
                    ->exists();
    }

    public  function isBreak()
    {
        return $this->where('type', 'break')
                    ->whereNull('end_time')
                    ->where('user_id', Auth::guard('web')->user()->id)
                    ->exists();
    }

    public function assignActivity()
    {
        return $this->hasOne(AssignActivity::class, 'id', 'assignment_id');
    }

}
