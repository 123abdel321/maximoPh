<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoFacturacion extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "concepto_facturacions";

    protected $fillable = [
        'codigo',
        'nombre_concepto',
        'id_cuenta_ingreso',
        'id_cuenta_interes',
        'id_cuenta_cobrar',
        'id_cuenta_iva',
        'intereses',
        'pronto_pago',
        'id_cuenta_gasto',
        'id_cuenta_anticipo',
        'dias_pronto_pago',
        'porcentaje_pronto_pago',
        'tipo_concepto',
        'valor',
        'created_by',
        'updated_by',
    ];

    public function cuenta_ingreso()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_ingreso');
    }

    public function cuenta_interes()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_interes');
    }

    public function cuenta_cobrar()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_cobrar');
    }

    public function cuenta_iva()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_iva');
    }

    public function cuenta_gasto()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_gasto');
    }

    public function cuenta_anticipo()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta_anticipo');
    }
}
