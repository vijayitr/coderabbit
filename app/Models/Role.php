<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Permission;

class Role extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        // Automatically clear permission cache on save or delete
        static::saved(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
