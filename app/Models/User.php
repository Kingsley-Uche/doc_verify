<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Events\Registered;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * In addition to these, the user model has id and timestamp
     */

//Category_id is the same as company_id


    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'phone',
        'password',
        'category',
        'category_id',
        'user_company_id',
        'created_by_user_id',
        'email_verified_at',
        'status',
        'company_ref',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'category_id');
    }
    public function category(){
        return $this->belongsTo(category_user::class, 'category_user_id','id');
    }
}



