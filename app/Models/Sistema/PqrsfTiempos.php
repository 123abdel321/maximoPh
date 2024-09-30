<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PqrsfTiempos extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "pqrsf_tiempos";

    protected $fillable = [
        'id_pqrsf',
        'id_usuario',
        'fecha_inicio',
        'fecha_fin',
        'tiempo_total',
        'created_by',
        'updated_by',
    ];
}
