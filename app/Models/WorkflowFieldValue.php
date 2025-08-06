<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'workflow_field_values';

    // Define fillable attributes if needed
    protected $fillable = ['field_id', 'value', 'process_id', 'assignment_id'];

    // Relationship with workflow_fields
    public function workflowField()
    {
        return $this->belongsTo(WorkflowField::class, 'field_id');
    }


    public function workflowProcessName()
    {
        return $this->belongsTo(WorkflowProcessName::class, 'process_id');
    }

    public function option()
    {
        return $this->belongsTo(WorkflowFieldOption::class, 'value', 'id');
    }

    // You may define any other relationships or methods as needed
}
