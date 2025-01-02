<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class StudentServiceRequest extends Model
{
 use HasFactory; //removed SoftDeletes trait

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
     ];
}