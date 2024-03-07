<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacFormasPago extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_formas_pagos";

    protected $fillable = [ 
        'id_cuenta',
        'id_tipo_formas_pago',
        'nombre',
        'created_by',
        'updated_by'
    ];

    public function cuenta()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta");
	}
}