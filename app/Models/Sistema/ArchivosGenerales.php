<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivosGenerales extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "archivos_generales";

    protected $fillable = [
        'relation_id',
        'relation_type',
        'tipo_archivo',
        'url_archivo',
        'estado',
        'created_by',
        'updated_by'
    ];

    public function relation()
    {
        return $this->morphTo();
    }


    
}
