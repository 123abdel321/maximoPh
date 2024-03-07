<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolesGenerales extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'roles_generales';

    protected $fillable = [
        'nombre',
        'ids_permission',
        'tipo'
    ];
}
