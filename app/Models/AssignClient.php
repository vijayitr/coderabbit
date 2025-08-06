<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignClient extends Model
{
    use HasFactory;

    protected $table = 'assign_clients';

    protected $fillable = [
        'user_id',
        'client_id',
        'assigned_by',
        'status',
    ];

    /**
     * Relationship: User (Who is assigned the client)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship: Client (Assigned client)
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relationship: Assigned By (Who assigned the client)
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope: Get only pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get only completed assignments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Get only in-progress assignments
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }
}
