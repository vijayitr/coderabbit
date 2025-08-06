<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Workflow;

class Client extends Model
{
    use HasFactory;

    // Specify the table if it doesn't follow Laravel's naming convention
    protected $table = 'clients';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'client_name',
        'assigned_to',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'status',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedTo()
    {
        return $this->hasMany(AssignClient::class, 'client_id');
    }
    
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class, 'client_id');
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class, 'client_id');
    }
}
