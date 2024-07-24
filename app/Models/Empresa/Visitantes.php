<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitantes extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'visitantes';

    protected $fillable = [
        'id_usuario',
        'ip',
        'device',
        'browser',
        'platform',
    ];
}
