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
        'account_email',
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
        'dtr_months',
        'dtr_with_details',
        'data_type',
        'new_data',
        'supporting_document',
        'description',
        'middle_name',
        'college',
        'department',
        'plantilla_position',
        'date_of_birth',
        'phone_number',
        'address',
        'blood_type',
        'emergency_contact_person',
        'emergency_contact_number',
        'location',
        'led_screen_details',
        'application_name',
        'installation_purpose',
        'installation_notes',
        'publication_author',
        'publication_editor',
        'publication_start_date',
        'publication_end_date',
        'publication_details',
        'data_documents_details',
    ];

    protected $casts = [
        'months' => 'array',
        'ms_options' => 'array',
        'preferred_date' => 'date',
        'preferred_time' => 'datetime',
        'new_data' => 'string',
        'supporting_document' => 'string',
        'description' => 'string',
        'middle_name' => 'string',
        'college' => 'string',
        'department' => 'string',
        'plantilla_position' => 'string',
        'date_of_birth' => 'date',
        'phone_number' => 'string',
        'address' => 'string',
        'blood_type' => 'string',
        'emergency_contact_person' => 'string',
        'emergency_contact_number' => 'string',
        'location' => 'string',
        'led_screen_details' => 'string', 
        'application_name' => 'string',
        'installation_purpose' => 'string',
        'installation_notes' => 'string',
        'publication_author' => 'string',
        'publication_editor' => 'string',
        'publication_start_date' => 'date',
        'publication_end_date' => 'date',
        'data_documents_details' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}