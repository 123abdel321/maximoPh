<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadisticaDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estadistica_detalles";

    protected $fillable = [
        'id',
        'id_estadisticas',
        'id_nit',
        'id_cuenta',
        'total_area',
        'total_coheficiente',
        'saldo_anterior',
        'total_facturas',
        'total_abono',
        'saldo',
        'registros',
        'errores',
        'total',
    ];

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }

    public function cuenta()
    {
        return $this->belongsTo("App\Models\Portafolio\PlanCuentas", 'id_cuenta');
    }
}
