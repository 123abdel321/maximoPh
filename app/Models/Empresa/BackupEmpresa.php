<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupEmpresa extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'backup_empresas';

    protected $fillable = [
        'id_empresa',
        'url_file',
        'file_name'
    ];

}
