<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class StudentServiceRequest extends Model
{
    use HasFactory; //removed SoftDeletes trait

    protected $table = 'student_service_requests';

    protected $fillable = [
        'user_id',
        'service_category',
        'first_name',
        'last_name',
        'student_id',
        'account_email',
        'data_type',
        'new_data',
        'supporting_document',
        'preferred_date',
        'preferred_time',
        'description',
        'additional_notes',
        'status',
        'assigned_uitc_staff_id',
        'transaction_type',
        'admin_notes',
        'actions_taken',
        'completion_report',
        'completion_status'    
    ];

    // Use Carbon to handle timezone-aware timestamps
    public function getSubmittedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null;
    }

    // Ensure timestamps are stored in the database in the correct timezone
    public function setSubmittedAtAttribute($value)
    {
        $this->attributes['submitted_at'] = Carbon::now('Asia/Manila');
    }

    // Relationship with Admin (UITC Staff)
    public function assignedUITCStaff()
    {
        return $this->belongsTo(Admin::class, 'assigned_uitc_staff_id');
    }
    public function user()
    {
    return $this->belongsTo(User::class);
    }
}