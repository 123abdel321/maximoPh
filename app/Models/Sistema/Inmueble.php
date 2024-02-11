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
        'observaciones'
    ];
}
