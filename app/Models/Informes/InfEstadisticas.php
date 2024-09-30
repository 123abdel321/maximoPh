<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadisticas extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estadisticas";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_zona',
        'id_concepto_facturacion',
        'id_nit',
        'fecha_desde',
        'fecha_hasta',
        'agrupar',
        'detalle',
        'created_by',
        'updated_by',
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Informes\InfEstadisticaDetalle', 'id_estadisticas');
    }
}
