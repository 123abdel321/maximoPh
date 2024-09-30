<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaERP extends Model
{
    use HasFactory;

    protected $connection = 'cliporta';

    protected $table = 'empresas';

    protected $fillable = [
        'id_nit',
        'estado',
        'servidor',
        'token_db',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'tipo_contribuyente',
        'razon_social',
        'nit',
        'dv',
        'codigos_responsabilidades',
        'notas_negociacion',
        'logo',
        'fecha_retiro',
        'direccion',
        'telefono',
        'hash',
        'id_empresa_referido',
        'id_usuario_owner',
        'fecha_ultimo_cierre'
	];
}
