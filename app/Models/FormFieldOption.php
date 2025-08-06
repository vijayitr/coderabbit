<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormFieldOption extends Model
{
    use HasFactory;

    protected $table = 'form_field_options';

    protected $fillable = [
        'form_field_id',
        'option',
        'order',
    ];

    /**
     * Get the form field this option belongs to.
     */
    public function formField()
    {
        return $this->belongsTo(FormField::class);
    }
}
