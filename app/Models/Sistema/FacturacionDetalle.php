<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturacionDetalle extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "facturacion_detalles";

    protected $fillable = [
        'id_factura',
        'id_nit',
        'id_cuenta_por_cobrar',
        'id_cuenta_ingreso',
        'id_comprobante',
        'id_centro_costos',
        'fecha_manual',
        'documento_referencia',
        'saldo',
        'valor',
        'concepto',
        'naturaleza_opuesta',
        'created_by',
        'updated_by',
    ];

}
