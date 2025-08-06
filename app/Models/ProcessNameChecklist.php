<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessNameChecklist extends Model
{
    use HasFactory;

    protected $fillable = ['process_id', 'item'];

    // Relationship with WorkflowProcessName Model
    public function process()
    {
        return $this->belongsTo(WorkflowProcessName::class, 'process_id');
    }
}
