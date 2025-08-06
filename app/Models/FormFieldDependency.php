<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormFieldDependency extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_id', 'depends_on_option_id', 'expected_value'
    ];

    public function field()
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    public function dependsOn()
    {
        return $this->belongsTo(FormFieldOption::class, 'depends_on_option_id');
    }
}
