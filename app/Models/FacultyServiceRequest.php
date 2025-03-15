<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        // User information
        'user_id',
        'service_category',
        'first_name',
        'last_name',
        'email',
        
        // Account related
        'account_email',
        
        // DTR related
        'dtr_months',
        'dtr_with_details',
        'months',
        'year',
        
        // Location information
        'location',
        
        // Change of data
        'data_type',
        'new_data',
        'supporting_document',
        
        // Problem description
        'description',
        'problem_encountered',  // Changed from problems_encountered to problem_encountered
        'repair_maintenance',
        
        // LED screen request
        'preferred_date',
        'preferred_time',
        'led_screen_details',
        
        // Application installation
        'application_name',
        'installation_purpose',
        'installation_notes',
        
        // Publication
        'publication_author',
        'publication_editor',
        'publication_start_date',
        'publication_end_date',
        'publication_details',
        
        // Data documents
        'data_documents_details',
        
        // Status
        'status',
        'assigned_uitc_staff_id',
        'admin_notes',
        'transaction_type',
    ];

    protected $casts = [
        'months' => 'array',
        'preferred_date' => 'date',
        'preferred_time' => 'datetime',
        'new_data' => 'string',
        'supporting_document' => 'string',
        'description' => 'string',
        'problem_encountered' => 'string',  // Changed from problems_encountered to problem_encountered
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

    public function assignedUITCStaff()
    {
        return $this->belongsTo(Admin::class, 'assigned_uitc_staff_id');
    }
}