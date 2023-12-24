<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    use HasFactory;
    protected $table = 'companies';
    protected $primaryKey ='id';
    protected $fillable= [
        'company_name',
        'company_industry',
        'company_country_id',
        'company_created_by_user_id'
    ];


    public function users()
    {
        return $this->hasMany(User::class, 'user_company_id');
    }
}
