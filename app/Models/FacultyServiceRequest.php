<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_category',
        'first_name',
        'last_name',
        'email',
        'college',
        'department',
        'data_type',
        'new_data',
        'location',
        'description',
        'months',
        'year',
        'supporting_document',
        'problem_encountered',
        'repair_maintenance',
        'preferred_date',
        'preferred_time',
        'status',
        'ms_options',
    ];

    protected $casts = [
        'months' => 'array',
        'ms_options' => 'array',
        'preferred_date' => 'date',
        'preferred_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}