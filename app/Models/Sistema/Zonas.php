<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zonas extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "zonas";

    protected $fillable = [
        'nombre',
        'id_centro_costos',
        'tipo',
        'created_by',
        'updated_by',
    ];
}
