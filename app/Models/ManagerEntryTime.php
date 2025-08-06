<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagerEntryTime extends Model
{
    use HasFactory;

    protected $fillable = ['manage_field_id', 'manage_field_option_id', 'details', 'extra_data', 'user_id', 'start_time', 'end_time'];

    /**
     * Get the department associated with the entry.
     */
    public function department()
    {
        return $this->belongsTo(ManageDepartment::class, 'manage_field_id');
    }

    /**
     * Get the task associated with the entry.
     */
    public function task() // Renamed from `tasks` to `task` since it's a single task
    {
        return $this->belongsTo(ManageDepartmentTask::class, 'manage_field_option_id');
    }

    /**
     * Get the user who created the entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
