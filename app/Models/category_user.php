<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category_user extends Model
{
    use HasFactory;
    protected $table = 'category_users';
    protected $primaryKey = 'id';


    protected $fillable = [
        'category_name',
        'category_user_id'
        // other fillable attributes
    ];



}
