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
        'middle_name',
        'account_email',
        'data_type',
        'new_data',
        'supporting_document',
        'additional_notes',
        'months',
        'year',
        'department',
        'college',
        'position',
        'date_of_birth',
        'phone_number',
        'address',
        'blood_type',
        'emergency_contact',
        'location',
        'repair_maintenance',
        'preferred_date',
        'preferred_time',
        'author',
        'editor',
        'publication_date',
        'end_publication',
        'description',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}