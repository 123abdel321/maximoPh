<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Versiones extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'versiones';

    protected $fillable = [
        'nombre',
        'estado'
    ];
        
}
