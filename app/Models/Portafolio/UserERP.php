<?php

namespace App\Models\Portafolio;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use DB;

class UserERP extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;

    protected $connection = 'cliporta';

    protected $table = 'users';

    protected $guard_name = 'sanctum';
    
    protected $fillable = [
        'username',
        'firstname',
        'lastname',
        'email',
        'password',
        'address',
        'city',
        'country',
        'postal',
        'has_empresa',
        'about',
        'id_empresa',
        'avatar',
        'telefono',
        'created_by',
        'updated_by',
        'fondo_sistema'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
