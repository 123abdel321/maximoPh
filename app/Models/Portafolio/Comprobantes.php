<?php

namespace App\Models\Portafolio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobantes extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "comprobantes";

    public const CONSECUTIVO_MENSUAL = 1;
	public const CONSECUTIVO_NORMAL = 0;

    const TIPO_INGRESOS = 0;
	const TIPO_EGRESOS = 1;
	const TIPO_COMPRAS = 2;
	const TIPO_VENTAS = 3;
	const TIPO_OTROS = 4;
	const TIPO_CIERRE = 5;

    public const TIPO_COMPROBANTE = [
		'ingresos',
		'egresos',
		'compras',
		'ventas',
		'otros',
		'cierre'
	];

	protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'tipo_comprobante',
        'tipo_consecutivo',
        'consecutivo_siguiente',
        'imprimir_en_capturas',
		'tesoreria',
		'maestra_padre'
    ];

	public function getTipoResolucionLabelAttribute()
	{
		if($tipo = self::TIPO_COMPROBANTE[$this->tipo_resolucion]) {
			return $tipo;
		}

		return '';
	}

	public function resolucion()
    {
        return $this->hasOne(FacResoluciones::class, "id_comprobante");
    }

}
