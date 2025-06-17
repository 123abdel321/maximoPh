<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioEmail extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "envio_emails";

    protected $fillable = [
        'id_empresa',
        'id_nit',
        'message_id',
        'sg_message_id',
        'email',
        'contexto',
        'status',
    ];

    public function detalle()
    {
        return $this->belongsTo(EnvioEmailDetalle::class, 'id_email');
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }
}
