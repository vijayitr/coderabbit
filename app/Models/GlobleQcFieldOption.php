<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobleQcFieldOption extends Model
{
    protected $fillable = ['globle_qc_field_id', 'option_text', 'order'];

    public function field()
    {
        return $this->belongsTo(GlobleQcField::class, 'globle_qc_field_id');
    }
}
