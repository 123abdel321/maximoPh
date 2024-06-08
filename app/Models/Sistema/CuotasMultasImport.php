<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotasMultasImport extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "cuotas_multas_imports";

    protected $fillable = [
        'id_nit',
        'id_inmueble',
        'id_concepto_facturacion',
        'numero_documento',
        'nombre_inmueble',
        'nombre_nit',
        'codigo_concepto',
        'fecha_inicio',
        'fecha_fin',
        'valor_total',
        'observacion',
        'estado',
    ];

    public function concepto()
    {
        return $this->belongsTo(ConceptoFacturacion::class, 'id_concepto_facturacion');
    }
}
