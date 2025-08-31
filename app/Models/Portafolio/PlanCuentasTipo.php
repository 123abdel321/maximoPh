<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuentasTipo extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "plan_cuentas_tipos";

    protected $fillable = [
        'id_cuenta',
        'id_tipo_cuenta'
    ];
}
