<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageDepartmentTask extends Model
{
    use HasFactory;

    // Table name (optional if follows Laravel's convention)
    protected $table = 'manage_department_tasks';

    // Fillable fields
    protected $fillable = [
        'field_id',
        'option_value',
        'extra_fields',
    ];

    // Relationships (if any)
    public function department()
    {
        return $this->belongsTo(ManageDepartment::class, 'field_id');
    }
}
