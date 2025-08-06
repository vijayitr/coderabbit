<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QcScore extends Model
{
    protected $fillable = [
        'call_allocation_id', 'score', 'details', 'parameter_scores', 'created_by'
    ];

    protected $casts = [
        'parameter_scores' => 'array',
    ];
}
