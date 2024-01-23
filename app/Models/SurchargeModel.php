<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurchargeModel extends Model
{
    use HasFactory;
    protected $fillable =['institution_id', 'institution_charge','institution_created_admin_id'];
}
