<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowField extends Model
{
    use HasFactory;
    protected $table = 'workflow_fields';

    // Allow mass assignment for specific fields
    protected $fillable = [
        'workflow_id',
        'field_name',
        'field_type',
        'is_primary',
        'order',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
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
    
    public function fieldValues()
    {
        return $this->hasMany(WorkflowFieldValue::class, 'field_id');
    }
}
