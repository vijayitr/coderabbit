<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;
    // Define the table name if it's not the default 'workflows'
    protected $table = 'workflows';

    // Add workflow_name to fillable for mass assignment
    protected $fillable = [
        'workflow_name',
        'client_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'status',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Relationship with WorkflowField
    public function fields()
    {
        return $this->hasMany(WorkflowField::class);
    }

    // Relationship with WorkflowProcessName
    public function processNames()
    {
        return $this->hasMany(WorkflowProcessName::class);
    }

    public function workflowProcessNames()
    {
        return $this->hasMany(WorkflowProcessName::class);
    }

    public function qcFields()
    {
        return $this->hasMany(WorkflowQcField::class);
    }
}
