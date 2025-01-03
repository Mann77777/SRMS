<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    // Specify the table name if it's not "admins"
    protected $table = 'admins';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'name', 
        'username', 
        'password', 
        'role',
        'availability_status',
        'profile_image'
    ];

    // Hide sensitive fields
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Define the attributes that should be cast
    protected $casts = [
        'availability_status' => 'string',
    ];
}