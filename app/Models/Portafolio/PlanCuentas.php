<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuentas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "plan_cuentas";

    protected $fillable = [
        'id_padre',
        'id_tipo_cuenta',
        'id_impuesto',
        'id_exogena_formato',
        'id_exogena_formato_concepto',
        'id_exogena_formato_columna',
        'cuenta',
        'nombre',
        'auxiliar',
        'exige_nit',
        'exige_documento_referencia',
        'exige_concepto',
        'exige_centro_costos',
		'naturaleza_cuenta',
		'naturaleza_ingresos',
		'naturaleza_egresos',
		'naturaleza_compras',
		'naturaleza_ventas',
		'cuenta_corriente',
        'created_by',
        'updated_by'
    ];
    
    public function tipos_cuenta()
    {
        return $this->hasMany(PlanCuentasTipo::class, "id_cuenta");
    }
}
