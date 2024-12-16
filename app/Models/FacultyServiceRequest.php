<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ms_options',
        'attendance_date',
        'attendance_option',
        'first_name',
        'last_name',
        'college',
        'department',
        'position',
        'dob',
        'phone',
        'address',
        'blood_type',
        'emergency_contact',
        'tup_web_options',
        'tup_web_other',
        'internet_telephone',
        'location',
        'ict_equip_options',
        'ict_equip_date',
        'ict_equip_other',
        'data_docs_report',
        'author',
        'editor',
        'publication_date',
        'end_publication',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}