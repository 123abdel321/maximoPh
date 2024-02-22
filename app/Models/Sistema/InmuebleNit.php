<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InmuebleNit extends Model
{
    use HasFactory;

    protected $connection = 'max';

    protected $table = "inmueble_nits";

    protected $fillable = [
        'id_nit',
        'id_inmueble',
        'porcentaje_administracion',
        'valor_total',
        'tipo',
        'paga_administracion',
        'enviar_notificaciones_mail',
        'enviar_notificaciones_fisica',
        'created_by',
        'updated_by'
    ]; 
    
    public function inmueble()
    {
        return $this->belongsTo(Inmueble::class, 'id_inmueble');
    }

    public function nit()
    {
        return $this->belongsTo("App\Models\Portafolio\Nits", 'id_nit');
    }
}
