<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConGastos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_gastos";

    protected $fillable = [
        'id_proveedor',
        'id_comprobante',
        'id_centro_costos',
        'fecha_manual',
        'consecutivo',
        'documento_referencia',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'total_rete_ica',
        'id_cuenta_rete_fuente',
        'porcentaje_rete_fuente',
        'total_gasto',
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
