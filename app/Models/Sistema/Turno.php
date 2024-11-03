<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "turnos";

    protected $fillable = [
        'id_usuario',
        'id_nit',
        'id_proyecto',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'asunto',
        'descripcion',
        'estado',
        'created_by',
        'updated_by',
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function notificacion()
    {
        return $this->morphTo(Notificaciones::class, 'notificacion');
	}

    public function eventos()
    {
		return $this->hasMany(TurnoEvento::class, "id_turno", "id");
	}

    public function creador()
    {
		return $this->belongsTo("App\Models\User","created_by");
	}

    public function responsable()
    {
		return $this->belongsTo("App\Models\User","id_usuario");
	}

    public function nit(){
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
	}
}
