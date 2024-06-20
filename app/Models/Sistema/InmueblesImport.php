<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InmueblesImport extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "inmuebles_imports";

    protected $fillable = [
        'id_inmueble',
        'id_zona',
        'id_nit',
        'id_concepto_facturacion',
        'nombre_concepto_facturacion',
        'nombre_inmueble',
        'nombre_zona',
        'area',
        'coheficiente',
        'valor_inmueble',
        'porcentaje_aumento',
        'valor_aumento',
        'nombre_nit',
        'numero_documento',
        'tipo',
        'porcentaje_administracion',
        'valor_administracion',
        'observacion',
        'estado',
    ];

    public function concepto()
    {
        return $this->belongsTo(ConceptoFacturacion::class, 'id_concepto_facturacion');
    }
    
}
