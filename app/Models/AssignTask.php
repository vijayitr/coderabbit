<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignTask extends Model
{
    use HasFactory;

    protected $table = 'assign_tasks'; // Explicitly define table name

    protected $fillable = [
        'task_id',
        'assigned_to',
        'assigned_by',
        'archive',
        'status',
    ];

    /**
     * Get the related task.
     */
    public function task()
    {
        return $this->belongsTo(ImportedTask::class, 'task_id');
    }

    /**
     * Get the user to whom the task is assigned.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who assigned the task.
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
