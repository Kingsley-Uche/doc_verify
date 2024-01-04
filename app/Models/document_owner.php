<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class document_owner extends Model
{
    use HasFactory;

    protected $fillable =[
        'docOwnerFirstName',
        'docOwnerMiddleName',
        'docOwnerLastName',
        'docOwnerDOB',
        'reference',
    ];

}
