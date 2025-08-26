<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entorno extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "entornos";

    protected $fillable = [
        'nombre',
        'valor',
        'created_by',
        'updated_by'
    ];

    public function concepto_facturacion()
    {
        return $this->belongsTo(ConceptoFacturacion::class, 'valor');
    }

    public function cuenta()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'valor');
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'valor');
    }

    public function formas_pago()
    {
        return $this->belongsTo("App\Models\Portafolio\FacFormasPago", 'valor');
    }
}
