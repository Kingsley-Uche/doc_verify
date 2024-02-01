<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class serviceCharge extends Model
{
    use HasFactory;
protected $fillable = ['doc_cat','doc_charge','category_user_id','created_admin_id'];
}
