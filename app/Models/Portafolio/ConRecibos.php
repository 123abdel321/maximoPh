<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConRecibos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_recibos";

    protected $fillable = [
        'id_nit',
        'id_comprobante',
        'request_id',
        'fecha_manual',
        'consecutivo',
        'total_abono',
        'total_anticipo',
        'observacion',
        'estado',
        'created_by',
        'updated_by'
    ];

    public function documentos()
    {
        return $this->morphMany(DocumentosGeneral::class, 'relation');
	}

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_nit');
	}

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'id_comprobante');
	}

    public function detalles()
	{
		return $this->hasMany(ConReciboDetalles::class, 'id_recibo');
	}

    public function pagos()
	{
		return $this->hasMany(ConReciboPagos::class, 'id_recibo');
	}
}
