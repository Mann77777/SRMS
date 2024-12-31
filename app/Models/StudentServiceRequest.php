<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentServiceRequest extends Model
{
    use HasFactory;

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
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}