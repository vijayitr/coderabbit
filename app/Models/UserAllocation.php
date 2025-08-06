<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAllocation extends Model
{
    use HasFactory;

    protected $table = 'user_allocation';

    protected $fillable = ['user_id', 'parent_id'];

    public function children()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // public function children()
    // {
    //     return $this->hasManyThrough(User::class, UserAllocation::class, 'parent_id', 'id', 'id', 'user_id');
    // }
}
