<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketDetail extends Model
{
    use HasFactory;
    protected $casts = [
        'value' => 'json',
    ];

    protected $fillable = [
        'ticket_id',
        'field_id',
        'value_type', // e.g., 'id' or 'text'
        'value',
    ];

    public function ticket()
    {
        return $this->belongsTo(UserTicket::class, 'ticket_id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
