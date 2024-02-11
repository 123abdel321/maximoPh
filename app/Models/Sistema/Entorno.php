<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entorno extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "entornos";

    protected $fillable = [
        'nombre',
        'valor',
        'created_by',
        'updated_by'
    ];
}
