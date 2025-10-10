<?php

namespace App\Helpers\Printers;

use DB;
use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Zonas;
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\InmuebleNit;

class FacturacionPdfMultiple extends AbstractPrinterPdf
{
    public $nits;
    public $id_zona;
	public $empresa;
	public $periodo;
	public $redondeo;
	public $detallar_facturas;
    public $meses = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
    ];

    public function __construct(Empresa $empresa, $nits = [], $periodo, $id_zona)
	{
		parent::__construct($empresa);

		$this->nits = $nits;
		$this->id_zona = $id_zona;
		$this->empresa = $empresa;
		$this->periodo = $periodo;
        $this->detallar_facturas = Entorno::where('nombre', 'detallar_facturas')->first();
        $this->detallar_facturas = $this->detallar_facturas ? $this->detallar_facturas->valor : 0;
        $this->redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeo = $this->redondeo ? $this->redondeo->valor : 0;
	}

    public function view()
	{
		return 'pdf.facturacion.facturaciones_multiples';
	}

    public function name()
	{
		return 'facturas_'.uniqid();
	}

    public function paper()
	{
		// if ($this->tipoEmpresion == 1) return 'landscape';
		// if ($this->tipoEmpresion == 2) return 'portrait';

		return 'landscape';
	}

    public function data()
    {
        $dataFacturas = [];

        foreach ($this->nits as $id_nit) {

            $getNit = Nits::whereId($id_nit)->with('ciudad')->first();

            if (!$getNit) {
                continue;
            }

            $query = $this->carteraDocumentosQuery($id_nit);
            $query->unionAll($this->carteraAnteriorQuery($id_nit));

            $totales = DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS cartera"))
                ->mergeBindings($query)
                ->select(
                    DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                    DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                    DB::raw('SUM(debito) AS debito'),
                    DB::raw('SUM(credito) AS credito'),
                    DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                    DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                    DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                    'fecha_manual',
                    'consecutivo'
                )
                ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
            ->groupByRaw('id_nit')->first();

            if (!$totales) {
                $totales = (object)[
                    'saldo_final' => 0,
                    'saldo_anterior' => 0,
                    'debito' => 0,
                    'credito' => 0,
                    'saldo_final' => 0,
                    'total_abono' => 0,
                    'total_facturas' => 0,
                    'consecutivo' => 0,
                    'fecha_manual' => $this->periodo
                ];
            }

            $inicioMesMenosDia = Carbon::parse("$this->periodo 23:59:59")->subDay()->format('Y-m-d H:i:m');
            $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first();
            $id_cuenta_anticipos = $id_cuenta_anticipos ? $id_cuenta_anticipos->valor : null;

            $cxp = (new Extracto(
                $id_nit,
                [4,8],
                null,
                $inicioMesMenosDia,
                $id_cuenta_anticipos
            ))->completo()->first();

            $facturaciones = DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS cartera"))
                ->mergeBindings($query)
                ->select(
                    'id_nit',
                    'numero_documento',
                    'nombre_nit',
                    'apartamentos',
                    'razon_social',
                    'direccion',
                    'tipo_documento',
                    "telefono",
                    'id_cuenta',
                    'cuenta',
                    'naturaleza_cuenta',
                    'auxiliar',
                    'nombre_cuenta',
                    'documento_referencia',
                    'id_centro_costos',
                    'codigo_cecos',
                    'nombre_cecos',
                    'id_comprobante',
                    'codigo_comprobante',
                    'nombre_comprobante',
                    'consecutivo',
                    'concepto',
                    'fecha_manual',
                    'created_at',
                    'fecha_creacion',
                    'fecha_edicion',
                    'created_by',
                    'updated_by',
                    'anulado',
                    'plazo',
                    DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                    DB::raw('SUM(debito) AS debito'),
                    DB::raw('SUM(credito) AS credito'),
                    DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                    DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                    DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                    DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                    DB::raw('SUM(total_columnas) AS total_columnas')
                )
                ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
                ->havingRaw('saldo_anterior != 0 OR total_abono != 0 OR total_facturas != 0 OR saldo_final != 0')
                ->groupByRaw($this->detallar_facturas ? 'id_nit, id_cuenta, documento_referencia' : 'id_nit, id_cuenta')
            ->get();

            $dataCuentas = [];
            $dataDescuento = [];
            $totalDescuento = 0;
            $tieneSaldoAnterior = false;
            $tieneDescuentoProntoPago = false;

            foreach ($facturaciones as $facturacion) {

                if (floatval($facturacion->saldo_anterior) > 0) {
                    $tieneSaldoAnterior = true;
                    $dataDescuento = [];
                }

                $descuento = 0;
                $concepto = $facturacion->concepto == 'SALDOS INICIALES' ? $facturacion->nombre_cuenta : $facturacion->concepto;

                $conceptoFactura = DB::connection('max')
                    ->table('concepto_facturacions')
                    ->where('id_cuenta_cobrar', $facturacion->id_cuenta)
                    ->first();

                if (!$tieneSaldoAnterior && $conceptoFactura && $conceptoFactura->pronto_pago) {
                    $diaHoy = intval(Carbon::now()->format('d'));
                    //VALIDAMOS FECHA LIMITE DE PRONTO PAGO
                    if ($conceptoFactura->dias_pronto_pago >= $diaHoy) {
                        $keyDescuento = Carbon::now()->format('Ym').$conceptoFactura->dias_pronto_pago;
                        $tieneDescuentoProntoPago = true;
                        $descuento = $facturacion->total_facturas * ($conceptoFactura->porcentaje_pronto_pago / 100);
                        $totalDescuento+= $descuento;
                        if (array_key_exists($keyDescuento, $dataDescuento)) {
                            $dataDescuento[$keyDescuento]['descuento']+= $descuento;
                        } else {
                            $dataDescuento[$keyDescuento] = [
                                'fecha_limite' => Carbon::now()->format('Y-m-'.$conceptoFactura->dias_pronto_pago),
                                'descuento' => $totales->saldo_final - $descuento
                            ];
                        }
                    }
                }

                $dataCuentas[] = (object)[
                    'nombre_cuenta' => $facturacion->nombre_cuenta,
                    'concepto' =>  $concepto,
                    'saldo_anterior' => $facturacion->saldo_anterior,
                    'total_facturas' => $facturacion->total_facturas,
                    'total_abono' => 0,
                    'descuento' => $descuento,
                    'documento_referencia' => $facturacion->documento_referencia,
                    'porcentaje_descuento' => $conceptoFactura ? $conceptoFactura->porcentaje_pronto_pago : ' ',
                    'saldo_final' => $facturacion->total_facturas,
                ];

                // $dataCuentas[] = (object)[
                //     'nombre_cuenta' => $facturacion->nombre_cuenta,
                //     'concepto' =>  $concepto,
                //     'saldo_anterior' => $facturacion->saldo_anterior,
                //     'total_facturas' => $facturacion->total_facturas,
                //     'total_abono' => $facturacion->total_abono,
                //     'descuento' => $descuento,
                //     'documento_referencia' => $facturacion->documento_referencia,
                //     'porcentaje_descuento' => $conceptoFactura ? $conceptoFactura->porcentaje_pronto_pago : ' ',
                //     'saldo_final' => $facturacion->saldo_final,
                // ];
            }

            foreach ($dataDescuento as $key => $descuento) {
                if ($descuento['descuento'] < 0) {
                    $dataDescuento[$key]['descuento'] = 0;
                }
            }

            $fechaMes = Carbon::parse($totales->fecha_manual)->format('m');
            $fechaYear = Carbon::parse($totales->fecha_manual)->format('Y');
            $fechaPlazo = Carbon::parse($totales->fecha_manual)->endOfMonth()->format('Y-m-d');

            $totalDescuento = $totalDescuento < 0 ? 0 : $totalDescuento;
            $totalDescuento = $this->roundNumber($totalDescuento);

            $totalAnticipos = $cxp ? $cxp->saldo : 0;
            $totalAnticipos = $totalAnticipos - ($totales->total_facturas - $totalDescuento);
            $totalAnticipos = $totalAnticipos < 0 ? 0 : $totalAnticipos;            

            $nit = (object)[
				'nombre_nit' => $getNit->nombre_completo,
				'telefono' =>  $getNit->telefono_1,
				'email' => $getNit->email,
				'direccion' => $getNit->direccion,
				'tipo_documento' => $getNit->tipo_documento->nombre,
				'numero_documento' => $getNit->numero_documento,
				"ciudad" => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
                'apartamentos' => $getNit->apartamentos
			];

            array_push($dataFacturas, (object)[
                'nit' => $nit,
                'nombre_cuenta' => '',
                'cuentas' => $dataCuentas,
                'descuentos' => $dataDescuento,
                'saldo_anterior' => $totales->saldo_anterior,
                'total_facturas' => $totales->total_facturas,
                'total_abono' => 0,
                'total_anticipos' => $cxp ? $cxp->saldo : 0,
                'anticipos_disponibles' => $totalAnticipos,
                'descuento' => $totalDescuento,
                'consecutivo' => $totales->consecutivo,
                'fecha_manual' => Carbon::parse($totales->fecha_manual)->format('Y-m-d'),
                'fecha_plazo' => $fechaPlazo,
                'fecha_texto' => $this->meses[intval($fechaMes) - 1].' - '.$fechaYear,
                'saldo_final' => $totales->total_facturas,
                'pronto_pago' => $tieneDescuentoProntoPago
            ]);

            // array_push($dataFacturas, (object)[
            //     'nit' => $nit,
            //     'nombre_cuenta' => '',
            //     'cuentas' => $dataCuentas,
            //     'descuentos' => $dataDescuento,
            //     'saldo_anterior' => $totales->saldo_anterior,
            //     'total_facturas' => $totales->total_facturas,
            //     'total_abono' => $totales->total_abono,
            //     'total_anticipos' => $cxp ? $cxp->saldo : 0,
            //     'anticipos_disponibles' => $totalAnticipos,
            //     'descuento' => $totalDescuento,
            //     'consecutivo' => $totales->consecutivo,
            //     'fecha_manual' => Carbon::parse($totales->fecha_manual)->format('Y-m-d'),
            //     'fecha_plazo' => $fechaPlazo,
            //     'fecha_texto' => $this->meses[intval($fechaMes) - 1].' - '.$fechaYear,
            //     'saldo_final' => $totales->saldo_final,
            //     'pronto_pago' => $tieneDescuentoProntoPago
            // ]);
        }
        
        $texto1 = Entorno::where('nombre', 'factura_texto1')->first();
        $texto2 = Entorno::where('nombre', 'factura_texto2')->first();
        
        return [
            'texto_1' => $texto1 ? $texto1->valor : '',
            'texto_2' => $texto2 ? $texto2->valor : '',
            'empresa' => $this->empresa,
            'facturas' => $dataFacturas,
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
        ];
    }

	private function carteraDocumentosQuery($id_nit)
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.direccion",
                "N.apartamentos",
                "N.telefono_1 AS telefono",
                "TD.nombre AS tipo_documento",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
                $startDate = Carbon::parse($this->periodo)->startOfDay();
                $endDate = Carbon::parse($this->periodo)->endOfMonth()->endOfDay();
                
                $query->whereBetween('DG.fecha_manual', [$startDate, $endDate]);
				// $query->where('DG.fecha_manual', '>=', $this->periodo);
			})
            ->when($id_nit, function ($query) use ($id_nit) {
				$query->where('DG.id_nit', $id_nit);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery($id_nit)
    {
        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.direccion",
                "N.apartamentos",
                "N.telefono_1 AS telefono",
                "TD.nombre AS tipo_documento",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->periodo);
			})
            ->when($id_nit, function ($query) use ($id_nit) {
				$query->where('DG.id_nit', $id_nit);
			});

        return $anterioresQuery;
    }

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }
}