<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class countries extends Model
{
    use HasFactory;



    protected $table = 'country';
    protected $primaryKey = 'id';
    //
    protected $fillable =[
        'enabled',
        'code3I',
        'code2I',
        'name',
        'name_official',
        'latitude',
        'longitude',
        'zoom',
    ];

}


