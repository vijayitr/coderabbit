<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowQcField extends Model
{
    protected $fillable = [
        'process_id',
        'field_name',
        'field_type',
        'order',
    ];

    public function options()
    {
        return $this->hasMany(WorkflowQcFieldOption::class, 'workflow_qc_field_id');
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
