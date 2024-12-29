<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Novedades extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "novedades";

    protected $fillable = [
        'id_porteria',
        'area',
        'tipo',
        'fecha',
        'asunto',
        'mensaje',
        'created_by',
        'updated_by'  
    ];

    public function chats()
    {
        return $this->morphMany(Chat::class, 'relation');
	}

    public function archivos()
    {
        return $this->morphMany(ArchivosGenerales::class, 'relation');
	}

    public function responsable()
    {
        return $this->belongsTo(Porteria::class, 'id_porteria');
    }
}
