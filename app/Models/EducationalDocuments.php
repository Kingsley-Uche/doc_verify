<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalDocuments extends Model
{
    use HasFactory;
    protected $fillable =[
        'doc_owner_id',
        'course',
        'verifier_name',
        'verifier_id',
        'verifier_city',
        'country_code',
        'doc_type',
        'studentId',
        'enrollment_status',
        'start_year',
        'end_year',
        'add_info',
        'doc_path',
        'doc_info',
        'ref_id',
        'application_id',
        'exam_board',
        'status',
        'uploaded_by_user_id',
    ];
}
