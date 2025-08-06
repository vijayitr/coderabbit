<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignActivity extends Model
{
    use HasFactory;

    protected $table = 'assign_activities';

    protected $fillable = [
        'user_id',
        'activity_id',
        'assigned_by',
        'status',
    ];

    /**
     * Relationship: Assigned User (Who received the assignment)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Activity (Workflow process)
     */
    public function activity()
    {
        return $this->belongsTo(WorkflowProcessName::class, 'activity_id');
    }

    public function workflowFieldValues()
    {
        return $this->hasMany(WorkflowFieldValue::class, 'assignment_id');
    }

    /**
     * Relationship: Assigned By (Who assigned the task)
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope: Get only pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get only completed assignments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function TimeEntry()
    {
        return $this->hasMany(TimeEntry::class, 'assignment_id');
    }

    public function qc()
    {
        return $this->hasMany(ProcessQcFieldValue::class, 'assignment_id');
    }
}
