<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoEvento extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "turno_eventos";

    protected $fillable = [
        'id_turno',
        'id_usuario',
        'descripcion',
        'created_by',
        'updated_by'
    ];

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function creador()
    {
		return $this->belongsTo("App\Models\User","created_by");
	}
    
}
