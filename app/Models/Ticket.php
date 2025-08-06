<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'status'];

    // Relationship: A Ticket belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A Ticket has many Replies
    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }
}
