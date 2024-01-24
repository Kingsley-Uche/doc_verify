<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalDocuments extends Model
{
    use HasFactory;
    protected $fillable =[
        'doc_owner_id',
        'course',
        'doc_verifier_name',
        'verifier_id',
        'country_code',
        'studentId',
        'qualification',
        'enrollment_status',
        'start_year',
        'end_year',
        'add_info',
        'doc_path',
        'ref_id',
        'status',
        'uploaded_by_user_id',

    ];
}
