<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'form_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function details()
    {
        return $this->hasMany(TicketDetail::class, 'ticket_id');
    }
    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }
}
