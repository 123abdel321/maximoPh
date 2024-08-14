<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConGastoDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_gasto_detalles";

    protected $fillable = [
        'id_gasto',
        'id_concepto_gastos',
        'id_cuenta_gasto',
        'id_cuenta_iva',
        'id_cuenta_retencion',
        'id_cuenta_retencion_declarante',
        'observacion',
        'subtotal',
        'aiu_porcentaje',
        'aiu_valor',
        'descuento_porcentaje',
        'rete_fuente_porcentaje',
        'rete_fuente_valor',
        'descuento_valor',
        'iva_porcentaje',
        'iva_valor',
        'total',
        'created_by',
        'updated_by',
    ];

    
}
