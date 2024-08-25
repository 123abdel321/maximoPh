<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConRecibosImport extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "con_recibos_imports";

    protected $fillable = [
        'id_inmueble',
        'id_nit',
        'codigo',
        'numero_documento',
        'nombre_inmueble',
        'nombre_zona',
        'nombre_nit',
        'fecha_manual',
        'pago',
        'descuento',
        'saldo',
        'saldo_nuevo',
        'anticipos',
        'observacion',
        'estado',
    ];

}
