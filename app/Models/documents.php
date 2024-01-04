<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class documents extends Model
{
    use HasFactory;

    protected $fillable =[
    'document_owner_id',
    'document_verifier_name',
    'document_verifier_id',
    'document_type',
    'document_status',
    'document_ref_code',
    'document_path',
];
}
