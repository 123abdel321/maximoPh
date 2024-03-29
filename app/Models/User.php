<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Always encrypt the password when it is updated.
     *
     * @param $value
    * @return string
    */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function empresasExternas(){
		return $this->hasMany("App\Models\Empresas\UsuarioEmpresa","id_usuario");
	}

	public function empresasPropias(){
		return $this->hasMany("App\Models\Empresas\Empresa","id_usuario_owner")->select(["*",DB::raw("1 as propio")]);
	}

    public function permisos(){
        return $this->hasMany("App\Models\Empresas\UsuarioPermisos","id_user");
    }

    public function getEmpresasAttribute(){
		$clientesPropios = $this->empresasPropias()->get()->pluck("empresa");
		$clientesExternos = $this->empresasExternas()->get()->pluck("empresa");
        // dd($clientesPropios, $clientesExternos);
		return $clientesPropios->merge($clientesExternos);
	}

    public function checkRelacionEmpresa($empresa,$campo = "hash"){
		return $this->empresas->where($campo,$empresa)->first();
	}
}
