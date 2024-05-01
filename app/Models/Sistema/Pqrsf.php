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
        'descripcion',
        'created_by',
        'updated_by'
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function mensajes(){
		return $this->hasMany(PqrsfMensajes::class, "id_pqrsf", "id");
	}

    public function usuario(){
		return $this->belongsTo("App\Models\User","id_usuario");
	}

    public function creador(){
		return $this->belongsTo("App\Models\User","created_by");
	}
}
