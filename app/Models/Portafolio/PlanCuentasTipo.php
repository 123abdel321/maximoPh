<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuentasTipo extends Model
{
    use HasFactory;

    const TIPO_CUENTA_CAJA_BANCOS = 2;
    const TIPO_CUENTA_CXC = 3;
    const TIPO_CUENTA_CXP = 4;
    const TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC = 7;
    const TIPO_CUENTA_ANTICIPO_CLIENTES_XP = 8;

    protected $connection = 'sam';

    protected $table = "plan_cuentas_tipos";

    protected $fillable = [
        'id_cuenta',
        'id_tipo_cuenta'
    ];
}
