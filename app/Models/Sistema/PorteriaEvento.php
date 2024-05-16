<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PorteriaEvento extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "porteria_eventos";

    protected $fillable = [
        'id_inmueble',
        'id_porteria',
        'tipo',
        'fecha_ingreso',
        'fecha_salida',
        'observacion',
        'created_at',
        'created_by',
        'updated_by',
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function inmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    public function persona()
    {
        return $this->belongsTo(Porteria::class, 'id_porteria');
    }

}
