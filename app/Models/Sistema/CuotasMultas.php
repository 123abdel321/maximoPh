<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotasMultas extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "cuotas_multas";

    protected $fillable = [
        'id_nit',
        'id_inmueble',
        'id_concepto_facturacion',
        'tipo_concepto',
        'fecha_inicio',
        'fecha_fin',
        'valor_total',
        'valor_coeficiente',
        'observacion',
        'created_by',
        'updated_by',
    ];

    public function inmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    public function concepto()
    {
        return $this->belongsTo(ConceptoFacturacion::class, 'id_concepto_facturacion');
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }
}
