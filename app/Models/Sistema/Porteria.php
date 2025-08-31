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
        'id_inmueble',
        'id_usuario',
        'id_nit',
        'tipo_porteria',
        'tipo_vehiculo',
        'tipo_mascota',
        'telefono',
        'genero',
        'email',
        'fecha_nacimiento',
        'nombre',
        'documento',
        'dias',
        'placa',
        'hoy',
        'estado',
        'observacion',
        'created_by',
        'updated_by',
    ];

    public function chats()
    {
        return $this->morphMany(Chat::class, 'relation');
	}

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

    public function inmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }

}
