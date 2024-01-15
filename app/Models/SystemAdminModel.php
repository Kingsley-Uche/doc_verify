<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Illuminate\Contracts\Auth\MustVerifyEmail; // Fix the import
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\MustVerifyEmail;

class SystemAdminModel extends Authenticatable
{
    use HasFactory;
    use Notifiable, MustVerifyEmail;
    use HasApiTokens;

    protected $guarded = 'system_admin';
    protected $table = 'system_admin';
    protected $fillable = ['firstName', 'lastName', 'email', 'phone', 'is_system_admin','last_logged_in', 'password', 'email_verified_at'];
    protected $hidden =['password','remember_token'];
    public  function getAuthPassword(){
        return $this->password;
    }
    public function getIsSystemAdminAttribute()
    {
        return $this->attributes['is_system_admin'];
    }
}
