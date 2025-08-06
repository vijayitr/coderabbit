<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobleQcField extends Model
{
    protected $fillable = ['field_name', 'field_type', 'order'];

    public function options()
    {
        return $this->hasMany(GlobleQcFieldOption::class, 'globle_qc_field_id');
    }
}
