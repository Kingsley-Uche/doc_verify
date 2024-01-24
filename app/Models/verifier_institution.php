<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verifier_institution extends Model
{
    use HasFactory;
    protected $fillable =['institution_id','company_id','verified_admin_id','verifier_status'];
}
