<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'imported_by',
        'status',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function assignedTask()
    {
        return $this->hasMany(AssignTask::class, 'task_id', 'id');
    }
}
