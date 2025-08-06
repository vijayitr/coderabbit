<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageDepartment extends Model
{
    use HasFactory;

    // Table name (optional if follows Laravel's convention)
    protected $table = 'manage_departments';

    // Fillable fields
    protected $fillable = [
        'field_name',
        'field_type',
    ];

    // Relationships (if any)
    public function tasks()
    {
        return $this->hasMany(ManageDepartmentTask::class, 'field_id');
    }
}
