<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id', 'name', 'type', 'is_required', 'options', 'order', 'prepopulate_by'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function dependencies()
    {
        return $this->hasMany(FormFieldDependency::class, 'field_id');
    }

    public function selectOptions()
    {
        return $this->hasMany(FormFieldOption::class, 'form_field_id');
    }

    public function dependsOn()
    {
        return $this->hasMany(FormFieldDependency::class, 'depends_on_field_id');
    }
}
