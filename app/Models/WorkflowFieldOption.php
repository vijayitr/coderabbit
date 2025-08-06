<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_field_id', 
        'option_value',
        'option_order'
    ];

    protected $table = 'workflow_field_options';

    // Relationship with WorkflowFieldOption
    public function options()
    {
        return $this->hasMany(WorkflowFieldOption::class);
    }

    // Relationship with Workflow
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
