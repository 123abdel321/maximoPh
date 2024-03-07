<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioPermisos extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "usuario_permisos";

    protected $fillable = [
        'id',
        'id_user',
        'id_rol',
        'id_empresa',
        'ids_permission',
        'ids_bodegas_responsable',
        'ids_resolucion_responsable',
    ];

    public function rol(){
        return $this->belongsTo(RolesGenerales::class, "id_rol");
    }

}
