<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'user_id', 'message'];

    // Relationship: A Reply belongs to a Ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship: A Reply belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
