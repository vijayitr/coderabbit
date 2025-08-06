<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowProcessName extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id', 
        'process_name'
    ];

    // Optional: Define the table name if it doesn't follow Laravel's naming convention
    protected $table = 'workflow_process_names';
    
    // Relationship with Workflow
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function processNames()
    {
        return $this->hasMany(WorkflowProcessName::class);
    }

    public function workflowFields()
    {
        return $this->hasMany(WorkflowField::class);
    }

    public function workflowFieldValues()
    {
        return $this->hasMany(WorkflowFieldValue::class, 'process_id');
    }

    public function assignActivities()
    {
        return $this->hasMany(AssignActivity::class, 'activity_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'process_id');
    }

    public function qcFields()
    {
        return $this->hasMany(WorkflowQcField::class, 'process_id');
    }
}
