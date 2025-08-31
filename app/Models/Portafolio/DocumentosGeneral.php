<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosGeneral extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $fillable = [
        'id_nit',
        'id_cuenta',
        'id_comprobante',
        'id_centro_costos',
        'consecutivo',
        'documento_referencia',
        'debito',
        'credito',
        'saldo',
        'anulado',
        'concepto',
        'fecha_manual',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_nit');
	}

    public function cuenta()
    {
        return $this->belongsTo(PlanCuentas::class, 'id_cuenta');
	}

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'id_comprobante');
	}

    public function centro_costos()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
    }

}
