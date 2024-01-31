<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialDocuments extends Model
{
    use HasFactory;
    protected $fillable =[
        'doc_owner_id',
        'bank_name',
        'country_code',
        'description',
        'doc_path',
        'ref_id',
        'status',
        'viewer_code',
        'ref_id',
        'application_id',
        'uploaded_by_user_id',


    ];
}

