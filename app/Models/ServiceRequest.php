<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [ // Allows you to mass assign values during saving
        'user_id',
        'ms_options',
        'tup_web_options',
        'ict_equip_options',
        'ms_other',
        'tup_web_other',
        'ict_equip_date',
        'status'
    ];

    // If you have a relationship with the users table
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}