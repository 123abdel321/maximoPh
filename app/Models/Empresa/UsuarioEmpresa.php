<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioEmpresa extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'usuario_empresas';

    protected $fillable = [
        'id_usuario',
        'id_empresa',
        'id_rol',
        'id_nit',
        'estado'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, "id_empresa");
    }

    public function usuario()
    {
        return $this->belongsTo("App\User", "id_usuario");
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }
}
