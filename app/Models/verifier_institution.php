<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class verifier_institution extends Model
{
    use HasFactory;
    protected $fillable =['institution_id','institution_name','country_name','country_code','verifier_status', 'registered_by_admin_id', 'contact_user_id','inst_ref'];
}
