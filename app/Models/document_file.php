<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class document_file extends Model
{
    use HasFactory;
    protected $fillable =[
        'file_name',
        'document_id',
        'file_path',
    ];
}
