<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class FacultyServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

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
       'problem_encountered',
       'repair_maintenance',
        'preferred_date',
        'preferred_time',
        'author',
        'editor',
        'publication_date',
         'end_publication',
       'description',
       'ms_options',
       'tup_web_options',
       'internet_telephone',
        'ict_equip_options',
         'attendance_option',
         'other_options',
         'status'
    ];
    protected $appends = ['ms_options','tup_web_options', 'internet_telephone','ict_equip_options','attendance_option','other_options'];
      public function getMsOptionsAttribute($value)
    {
        return json_decode($value);
    }
      public function getTupWebOptionsAttribute($value)
    {
        return json_decode($value);
    }
      public function getInternetTelephoneAttribute($value)
      {
          return json_decode($value);
      }
      public function getIctEquipOptionsAttribute($value)
      {
          return json_decode($value);
      }
       public function getAttendanceOptionAttribute($value)
      {
          return json_decode($value);
      }
       public function getOtherOptionsAttribute($value)
      {
          return json_decode($value);
      }
}