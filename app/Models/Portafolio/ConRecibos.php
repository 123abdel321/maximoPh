<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConRecibos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_recibos";

    protected $fillable = [
        'id_nit',
        'id_comprobante',
        'fecha_manual',
        'consecutivo',
        'total_abono',
        'total_anticipo',
        'observacion',
        'created_by',
        'updated_by'
    ];
}
