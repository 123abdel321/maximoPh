<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'empresas';

    protected $fillable = [
        'id_nit',
        'estado',
        'servidor',
        'token_db_maximo',
        'token_db_portafolio',
        'token_api_portafolio',
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
        'descripcion',
        'logo',
        'fecha_retiro',
        'direccion',
        'telefono',
        'hash',
        'valor_suscripcion_mensual',
        'id_usuario_owner',
        'fecha_ultimo_cierre'
	];

}
