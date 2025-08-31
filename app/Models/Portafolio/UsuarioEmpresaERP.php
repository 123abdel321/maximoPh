<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioEmpresaERP extends Model
{
    use HasFactory;

	protected $connection = 'cliporta';

	protected $table = 'usuario_empresas';

    protected $fillable = [
		'id_usuario',
		'id_empresa',
		'id_rol',
		'estado'
	];
}
