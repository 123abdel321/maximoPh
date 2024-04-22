<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConReciboDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_recibo_detalles";

    protected $fillable = [
        'id_recibo',
        'id_cuenta',
        'id_nit',
        'fecha_manual',
        'documento_referencia',
        'consecutivo',
        'concepto',
        'total_factura',
        'total_abono',
        'total_saldo',
        'nuevo_saldo',
        'total_anticipo',
        'created_by',
        'updated_by'
    ];

    public function cuenta()
    {
        return $this->belongsTo(PlanCuentas::class, 'id_cuenta');
    }
}
