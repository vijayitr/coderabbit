<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessQcFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'process_qc_field_values';

    // Define fillable attributes if needed
    protected $fillable = ['workflow_qc_field_id', 'globle_qc_field_id', 'value', 'process_id', 'assignment_id', 'status'];

    // Relationship with workflow_fields
    // public function workflowQcField()
    // {
    //     return $this->belongsTo(WorkflowQcField::class, 'field_id');
    // }


    public function workflowProcessName()
    {
        return $this->belongsTo(WorkflowProcessName::class, 'process_id');
    }

    public function option()
    {
        return $this->belongsTo(WorkflowQcFieldOption::class, 'value', 'id');
    }

    public function workflowQcField() {
        return $this->belongsTo(WorkflowQcField::class);
    }

    public function globleQcField() {
        return $this->belongsTo(GlobleQcField::class);
    }

    // You may define any other relationships or methods as needed
}
