<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "notificaciones";

    protected $fillable = [
        'notificacion_id',
        'notificacion_type',
        'id_usuario',
        'id_rol',
        'mensaje',
        'menu',
        'function',
        'data',
        'estado',
        'tipo',
        'created_by',
        'updated_by'
    ];

    public function notificacion()
    {
        return $this->morphTo();
    }

    public function creador()
    {
        return $this->belongsTo("App\Models\User", "created_by");
    }
}
