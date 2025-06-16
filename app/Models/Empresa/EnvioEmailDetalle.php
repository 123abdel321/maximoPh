<?php

namespace App\Models\Empresa;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioEmailDetalle extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "envio_email_detalles";

    protected $fillable = [
        'id_email',
        'email',
        'event',
        'ip',
        'response',
        'sg_event_id',
        'sg_message_id',
        'smtp_id',
        'timestamp',
        'tls',
    ];

    public function email()
    {
        return $this->belongsTo(EnvioEmail::class, 'id_email');
    }
}
