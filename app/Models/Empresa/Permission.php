<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'permissions';

    protected $fillable = [
        "id",
        "name",
        "id_componente_menu",
        "guard_name"
    ];
}
