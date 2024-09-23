<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $connection = 'max';

    protected $table = "proyectos";

    protected $fillable = [
        'nombre',
        'valor_total',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'created_by',
        'updated_by',
    ];
}
