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
        'id_nit',
        'id_inmueble',
        'tipo',
        'asunto',
        'descripcion',
        'created_by',
        'updated_by'
    ]; 
}
