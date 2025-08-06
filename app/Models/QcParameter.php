<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcParameter extends Model
{
    protected $table = 'qc_parameters';

    protected $fillable = [
        'name',
        'weight',
        'order',
        'status'
    ];
}
