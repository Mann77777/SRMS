<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Staff extends Authenticatable
{
    use HasFactory;

     protected $fillable = [
        'name',
        'username',
        'password',
        'availability_status',
    ];

    protected $hidden = [
        'password',
         'remember_token',
    ];
}