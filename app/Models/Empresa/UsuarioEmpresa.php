<?php

namespace App\Models\Empresas;

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
        'estado'
    ];

    public function empresa()
    {
        return $this->belongsTo("App\Models\Empresas\Empresa", "id_empresa");
    }

    public function usuario()
    {
        return $this->belongsTo("App\User", "id_usuario");
    }
}
