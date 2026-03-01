<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioEmail extends Model
{
    use HasFactory;

    public const PLANTILLA_WHATSAPP_FACTURACION = 'HX5f1e359d73b28281e05c4a796cda7c17';
    public const PLANTILLA_WHATSAPP_MEDIA = 'HXc33818899b39cd21583f8512b466e4e4';

    protected $connection = 'clientes';

    protected $table = "envio_emails";

    protected $fillable = [
        'id_empresa',
        'type',
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
