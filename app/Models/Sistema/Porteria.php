<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Porteria extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "porterias";

    protected $fillable = [
        'id_usuario',
        'tipo_porteria',
        'tipo_vehiculo',
        'tipo_mascota',
        'nombre',
        'dias',
        'placa',
        'hoy',
        'observacion',
        'created_by',
        'updated_by',
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

}
