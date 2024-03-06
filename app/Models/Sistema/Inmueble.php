<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inmueble extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "inmuebles";

    protected $fillable = [
        'id_zona',
        'id_concepto_facturacion',
        'nombre',
        'area',
        'coeficiente',
        'valor_total_administracion',
        'observaciones',
        'created_by',
        'updated_by'
    ];

    public function concepto()
    {
        return $this->belongsTo(ConceptoFacturacion::class, 'id_concepto_facturacion');
    }

    public function zona()
    {
        return $this->belongsTo(Zonas::class, 'id_zona');
    }

    public function personas()
    {
        return $this->hasMany(InmuebleNit::class, 'id_inmueble', 'id');
    }
    
}
