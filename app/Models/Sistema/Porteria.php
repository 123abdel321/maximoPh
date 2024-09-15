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
        'id_nit',
        'tipo_porteria',
        'tipo_vehiculo',
        'tipo_mascota',
        'nombre',
        'documento',
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

    public function propietario()
    {
		return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
	}

    public function eventos()
    {
		return $this->hasMany(PorteriaEvento::class, "id_porteria", "id");
	}

    public function usuario()
    {
		return $this->belongsTo("App\Models\User", 'id_usuario');
	}

}
