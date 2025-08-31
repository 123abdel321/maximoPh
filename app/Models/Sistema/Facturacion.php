<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturacion extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "facturacions";

    protected $fillable = [
        'id_comprobante',
        'id_nit',
        'fecha_manual',
        'token_factura',
        'pronto_pago',
        'valor',
        'valor_admon',
        'valor_intereses',
        'count_intereses',
        'valor_anticipos',
        'valor_cuotas_multas',
        'count_cuotas_multas',
        'saldo_base',
        'mensajes',
        'anulado',
        'created_by',
        'updated_by'
    ];

    public function detalle()
    {
        return $this->hasMany(FacturacionDetalle::class, 'id_factura');
    }
}
