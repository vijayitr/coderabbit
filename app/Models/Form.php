<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug','form_details','status', 'roles'];

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }
}
