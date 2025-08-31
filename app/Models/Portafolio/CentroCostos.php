<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCostos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "centro_costos";

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'created_by',
        'updated_by'
    ];

}
