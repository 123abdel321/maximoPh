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
        'nombre_concepto',
        'id_cuenta_ingreso',
        'id_cuenta_interes',
        'id_cuenta_cobrar',
        'id_cuenta_iva',
        'intereses',
        'valor',
        'created_by',
        'updated_by',
    ];
}
