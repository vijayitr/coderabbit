<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserAllocation;
use App\Models\Workflow;
use App\Models\Role;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'microsoft_token',
        'profile_image',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeUsersWithLowerRole(Builder $query, $currentUser)
    {
        $maxPosition = $currentUser->roles->min('position');

        return $query->whereHas('roles', function ($roleQuery) use ($maxPosition) {
            $roleQuery->where('position', '>', $maxPosition);
        });
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class, 'created_by');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'assigned_to');
    }

    public function allocatedUsers()
    {
        return $this->hasMany(UserAllocation::class, 'parent_id');
    }

}