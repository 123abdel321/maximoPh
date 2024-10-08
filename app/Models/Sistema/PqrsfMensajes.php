<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PqrsfMensajes extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "pqrsf_mensajes";

    protected $fillable = [
        'id_pqrsf',
        'id_usuario',
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
}
