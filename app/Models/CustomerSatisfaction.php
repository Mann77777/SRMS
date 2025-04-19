<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSatisfaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'request_id',
        'request_type',
        'responsiveness',
        'reliability',
        'access_facilities',
        'communication',
        'costs',
        'integrity',
        'assurance',
        'outcome',
        'average_rating',
        'additional_comments',
    ];

    /**
     * Get the user that owns the satisfaction survey.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student service request associated with this survey.
     */
    public function studentServiceRequest()
    {
        if ($this->request_type === 'Student') {
            return $this->belongsTo(StudentServiceRequest::class, 'request_id');
        }
        return null;
    }

    /**
     * Get the faculty service request associated with this survey.
     */
    public function facultyServiceRequest()
    {
        if ($this->request_type === 'Faculty & Staff') {
            return $this->belongsTo(FacultyServiceRequest::class, 'request_id');
        }
        return null;
    }
}