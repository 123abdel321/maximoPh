<?php

namespace App\Models\Portafolio;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacResoluciones extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_resoluciones";

    public const TIPO_RESOLUCION = [
		'POS',
		'facturación electrónica',
		'contingencia',
		'nota débito',
		'nota crédito',
		'documento equivalente/soporte',
	];

    const TIPO_POS = 0;
	const TIPO_FACTURA_ELECTRONICA = 1;
	const TIPO_CONTINGENCIA = 2;
	const TIPO_NOTA_DEBITO = 3;
	const TIPO_NOTA_CREDITO = 4;
	const TIPO_DOCUEMNTO_EQUIVALENTE = 5;

    protected $fillable = [
        'id_comprobante',
        'nombre',
        'prefijo',
        'consecutivo',
        'numero_resolucion',
        'tipo_impresion',
        'tipo_resolucion',
        'fecha',
        'vigencia',
        'consecutivo_desde',
        'consecutivo_hasta',
        'created_by',
        'updated_by',
    ];

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'id_comprobante');
	}

	public function scopeActive($query)
	{
		return $query->whereRaw('consecutivo BETWEEN consecutivo_desde AND consecutivo_hasta')
			->where('fecha', '<=', date('Y-m-d'))
			->whereRaw('? < DATE_ADD(fecha, INTERVAL vigencia MONTH)', [date('Y-m-d')]);
	}

    public function getNombreCompletoAttribute()
	{
		return "{$this->nombre} - ({$this->prefijo}{$this->consecutivo_desde} - {$this->prefijo}{$this->consecutivo_hasta})";
	}

    public function getIsValidAttribute()
	{
		return $this->consecutivo >= $this->consecutivo_desde && $this->consecutivo <= $this->consecutivo_hasta;
	}

    public function getIsActiveAttribute()
	{
		$maxDateResolucion = Carbon::parse($this->fecha)
			->addMonthsNoOverflow($this->vigencia)
			->format('Y-m-d');

		$dateNow = date('Y-m-d');
		$isVigente = $dateNow >= $this->fecha && $dateNow < $maxDateResolucion ;

		return $isVigente;
	}
    
}
