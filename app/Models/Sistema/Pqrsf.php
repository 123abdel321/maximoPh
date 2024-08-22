<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pqrsf extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "pqrsf";

    protected $fillable = [
        'id_usuario',
        'id_nit',
        'tipo',
        'dias',
        'hoy',
        'asunto',
        'estado',
        'descripcion',
        'created_by',
        'updated_by'
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function notificacion()
    {
        return $this->morphTo(Notificaciones::class, 'notificacion');
	}

    public function mensajes(){
		return $this->hasMany(PqrsfMensajes::class, "id_pqrsf", "id");
	}

    public function tiempos(){
		return $this->hasMany(PqrsfTiempos::class, "id_pqrsf", "id");
	}

    public function tiempo(){
		return $this->belongsTo(PqrsfTiempos::class, "id", "id_pqrsf")->orderBy('id', 'ASC');
	}

    public function usuario(){
		return $this->belongsTo("App\Models\User","id_usuario");
	}

    public function creador(){
		return $this->belongsTo("App\Models\User","created_by");
	}

    public function nit(){
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
	}
}
