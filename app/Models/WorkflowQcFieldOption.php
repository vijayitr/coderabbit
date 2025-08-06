<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowQcFieldOption extends Model
{
    protected $fillable = [
        'workflow_qc_field_id',
        'option_text',
        'order',
    ];

    public function field()
    {
        return $this->belongsTo(WorkflowQcField::class, 'workflow_qc_field_id');
    }
}
