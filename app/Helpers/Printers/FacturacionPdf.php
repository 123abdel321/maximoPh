<?php

namespace App\Helpers\Printers;

use DB;
use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Portafolio\DocumentosGeneral;

class FacturacionPdf extends AbstractPrinterPdf
{
    public $id_nit;
	public $empresa;
	public $periodo;
    public $redondeoIntereses;
    public $redondeoProntoPago;
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

    public function __construct(Empresa $empresa, $id_nit = null, $periodo)
	{
		parent::__construct($empresa);

		copyDBConnection('max', 'max');
        setDBInConnection('max', $empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db_portafolio);
		$this->id_nit = $id_nit;
		$this->empresa = $empresa;
		$this->periodo = "$periodo";
	}

    public function view()
	{
		return 'pdf.facturacion.facturaciones';
	}

    public function name()
	{
		return 'factura_'.uniqid();
	}

    public function paper()
	{
		// if ($this->tipoEmpresion == 1) return 'landscape';
		// if ($this->tipoEmpresion == 2) return 'portrait';

		return 'landscape';
	}

    public function data()
    {
		
		$nit = null;
		$getNit = Nits::whereId($this->id_nit)->with('ciudad')->first();
        $this->redondeoIntereses = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeoIntereses = $this->redondeoIntereses ? $this->redondeoIntereses->valor : 0;
        $this->redondeoProntoPago = Entorno::where('nombre', 'redondeo_pronto_pago')->first();
        $this->redondeoProntoPago = $this->redondeoProntoPago ? floatval($this->redondeoProntoPago->valor) : 0;
        $detallar_facturas = Entorno::where('nombre', 'detallar_facturas')->first();
        $detallar_facturas = $detallar_facturas ? $detallar_facturas->valor : 0;
        $qrFactura = Entorno::where('nombre', 'qr_facturas')->first();
        $qrFactura = $qrFactura ? $qrFactura->valor : null;
        $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first();
        $id_cuenta_anticipos = $id_cuenta_anticipos ? $id_cuenta_anticipos->valor : null;

        $max_length = 40;
        $apartamentos_original = $getNit->apartamentos;
        if (mb_strlen($apartamentos_original, 'UTF-8') > $max_length) $apartamentos_limitado = mb_substr($apartamentos_original, 0, $max_length, 'UTF-8') . '...';
        else $apartamentos_limitado = $apartamentos_original;
		
		if($getNit){ 
			$nit = (object)[
				'nombre_nit' => $getNit->nombre_completo,
				'telefono' =>  $getNit->telefono_1,
				'email' => $getNit->email,
				'direccion' => $getNit->direccion,
				'tipo_documento' => $getNit->tipo_documento->nombre,
				'numero_documento' => $getNit->numero_documento,
				"ciudad" => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
                'apartamentos' => $apartamentos_limitado
			];
		}

		$query = $this->carteraDocumentosQuery();
		$query->unionAll($this->carteraAnteriorQuery());
        
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
        
        $inicioMesMenosDia = Carbon::parse("{$this->periodo} 23:59:59")->subDay()->format('Y-m-d H:i:m');

        // dd($inicioMesMenosDia);
        $cxp = (new Extracto(
            $this->id_nit,
            [4,8],
            null,
            $inicioMesMenosDia,
            $id_cuenta_anticipos
        ))->anticipos()->first();

		$facturaciones = DB::connection('sam')
			->table(DB::raw("({$query->toSql()}) AS cartera"))
			->mergeBindings($query)
			->select(
				'id_nit',
				'numero_documento',
				'nombre_nit',
				'razon_social',
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
            ->groupByRaw($detallar_facturas ? 'id_nit, id_cuenta, documento_referencia' : 'id_nit, id_cuenta')
        ->get();

        $dataCuentas = [];
        $dataDescuento = [];
        $totalDescuento = 0;
        $tieneDescuentoProntoPago = false;

        $inicioMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $facturasMesDescuento = $this->getFacturaMes($this->id_nit, $inicioMes, $totales->fecha_manual);

        $count = 0;
        foreach ($facturaciones as $facturacion) {
            
            $tieneSaldoAnterior = false;
            if (floatval($facturacion->saldo_anterior) > 0) {
                $tieneSaldoAnterior = true;
                // $dataDescuento = [];
                // Si hay saldo anterior, no se puede aplicar pronto pago normal, solo para morosos
            }
            
            $descuento = 0;
            $concepto = $facturacion->concepto == 'SALDOS INICIALES' ? $facturacion->nombre_cuenta : $facturacion->concepto;

            $conceptoFactura = DB::connection('max')
                ->table('concepto_facturacions')
                ->where('id_cuenta_cobrar', $facturacion->id_cuenta)
                ->first();
            
            if ($conceptoFactura && $conceptoFactura->pronto_pago) {
                $count++;
                $diaHoy = intval(Carbon::now()->format('d'));
                $keyDescuento = Carbon::now()->format('Ym').$conceptoFactura->dias_pronto_pago;
                $dataDescuento[$keyDescuento] = [
                    'fecha_limite' => Carbon::now()->format('Y-m-'.$conceptoFactura->dias_pronto_pago),
                    'descuento' => $facturasMesDescuento->descuento
                ];
            }

            $dataCuentas[] = (object)[
                'id_cuenta' => $facturacion->id_cuenta,
                'nombre_cuenta' => $facturacion->nombre_cuenta,
                'concepto' => $concepto,
                'saldo_anterior' => $facturacion->saldo_anterior,
                'total_facturas' => $facturacion->total_facturas,
                'total_abono' => $facturacion->total_abono,
                'descuento' => $descuento,
                'documento_referencia' => $facturacion->documento_referencia,
                'porcentaje_descuento' => $conceptoFactura ? $conceptoFactura->porcentaje_pronto_pago : ' ',
                'saldo_final' => $facturacion->saldo_final
            ];

            if ($tieneSaldoAnterior) {
                $dataDescuento = [];
            }            
        }

        if ($this->redondeoProntoPago) {
            $totalDescuento = $this->roundNumber($totalDescuento, $this->redondeoProntoPago);
        }
        
        foreach ($dataDescuento as $key => $descuento) {
            if ($descuento['descuento'] < 0) {
                $dataDescuento[$key]['descuento'] = 0;
            } else {
                $dataDescuento[$key]['descuento'] = $totales->saldo_final - $this->roundNumber($dataDescuento[$key]['descuento'], $this->redondeoProntoPago);
            }
        }

        $fechaMes = Carbon::parse($totales->fecha_manual)->format('m');
        $fechaYear = Carbon::parse($totales->fecha_manual)->format('Y');
        $fechaPlazo = Carbon::parse($totales->fecha_manual)->endOfMonth()->format('Y-m-d');
        
        $totalDescuento = $totalDescuento < 0 ? 0 : $totalDescuento;
        if ($this->redondeoProntoPago) {
            $totalDescuento = $this->roundNumber($totalDescuento, $this->redondeoProntoPago);
        }
        
        $totalAnticipos = $cxp ? $cxp->saldo : 0;
        $totalAnticipos = $totalAnticipos - ($totales->total_facturas - $totalDescuento);
        $totalAnticipos = $totalAnticipos < 0 ? 0 : $totalAnticipos;
        
        $totalData = (object)[
            'nombre_cuenta' => '',
            'saldo_anterior' => $totales->saldo_anterior,
            'total_facturas' => $totales->total_facturas,
            'total_abono' => $totales->total_abono,
            'total_anticipos' => $cxp ? $cxp->saldo : 0,
            'anticipos_disponibles' => $totalAnticipos,
            'descuento' => $totalDescuento,
            'consecutivo' => $totales->consecutivo,
            'fecha_manual' => Carbon::parse($totales->fecha_manual)->format('Y-m-d'),
            'fecha_plazo' => $fechaPlazo,
            'fecha_texto' => $this->meses[intval($fechaMes) - 1].' - '.$fechaYear,
            'saldo_final' => $totales->saldo_final
        ];

        $texto1 = Entorno::where('nombre', 'factura_texto1')->first();
        $texto2 = Entorno::where('nombre', 'factura_texto2')->first();
        
        return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'cuentas' => $dataCuentas,
			'totales' => $totalData,
            'texto_1' => $texto1 ? $texto1->valor : '',
            'texto_2' => $texto2 ? $texto2->valor : '',
            'pronto_pago' => true,
            'descuentos' => $dataDescuento,
            'qrFactura' => $qrFactura,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
    }

    private function getFacturaMes($id_nit, $inicioMes, $fechaManual)
    {
        $fechaManual = Carbon::parse($fechaManual)->format("Y-m-d");
        $facturas = DB::connection('max')->select("SELECT
                FA.id AS id_factura,
                FD.id AS id_factura_detalle,
                FD.fecha_manual,
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                CF.nombre_concepto,
                CF.porcentaje_pronto_pago,
                CF.dias_pronto_pago,
                CF.pronto_pago_morosos AS pronto_pago_morosos,
                CF.valor_fijo_pronto_pago,
                FD.documento_referencia,
                DATEDIFF('{$fechaManual}', '{$inicioMes}') AS datadiff,
                0 AS aprobado,
                SUM(FD.valor) AS subtotal,
                
                CASE
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') < CF.dias_pronto_pago THEN 
                        CASE 
                            WHEN CF.valor_fijo_pronto_pago IS NOT NULL AND CF.valor_fijo_pronto_pago > 0 
                                THEN CF.valor_fijo_pronto_pago
                            ELSE ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
                        END
                    ELSE 0
                END AS descuento,

                CASE
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') < CF.dias_pronto_pago THEN 
                        SUM(FD.valor) - (
                            CASE 
                                WHEN CF.valor_fijo_pronto_pago IS NOT NULL AND CF.valor_fijo_pronto_pago > 0 
                                    THEN CF.valor_fijo_pronto_pago
                                ELSE (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                            END
                        )
                    ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FA.id IS NOT NULL
                AND FD.id_concepto_facturacion IS NOT NULL
                AND FD.fecha_manual = '{$inicioMes}'
                
            GROUP BY FD.documento_referencia
        ");

        $facturas = collect($facturas);

        if (!count($facturas)) return false;
        
        $data = (object)[
            'id_factura' => $facturas[0]->id_factura,
            'has_pronto_pago' => $facturas[0]->has_pronto_pago,
            'subtotal' => 0,
            'descuento' => 0,
            'valor_total' => 0,
            'saldo_pendiente' => 0,
            'usado' => false,
            'detalle' => []
        ];

        $extracto = (new Extracto($id_nit, [3,7], null, $fechaManual))->completo()->first();
        $saldoPendiente = $extracto ? $extracto->saldo : 0;
        $data->saldo_pendiente = $saldoPendiente;

        foreach ($facturas as $factura) {
            $fechaFormateada = date('Y-m', strtotime($factura->fecha_manual));
            $tieneProntoPago = $this->tieneProntoPago($id_nit, $factura->id_cuenta_gasto, $fechaFormateada);

            if ($tieneProntoPago) {
                $factura->descuento = 0;
            }

            $data->subtotal += $factura->subtotal;
            $data->descuento += $factura->descuento;
            $data->valor_total += $factura->valor_total;
            $data->usado = false;
            $data->detalle[$factura->documento_referencia] = $factura;
        }
        
        $descuentoSinRedondear = $data->descuento;
        
        if ($this->redondeoProntoPago) {
            $descuentoRedondeado = $this->roundNumber($data->descuento, $this->redondeoProntoPago);
            $diferencia = $descuentoRedondeado - $descuentoSinRedondear;
            $data->descuento = $descuentoRedondeado;
            
            if ($diferencia != 0 && count($facturas) > 0) {
                $this->repartirDiferenciaDescuento($data->detalle, $diferencia);
                $data->valor_total = 0;
                foreach ($data->detalle as $detalle) {
                    $data->valor_total += $detalle->valor_total;
                }
            }
        }

        return $data;
    }

    private function repartirDiferenciaDescuento(&$detalles, $diferencia)
    {
        if ($diferencia == 0) return;
        
        uasort($detalles, function($a, $b) {
            return $b->descuento <=> $a->descuento;
        });
        
        $numItems = count($detalles);
        $diferenciaAbs = abs($diferencia);
        $diferenciaPorItem = floor($diferenciaAbs / $numItems);
        $diferenciaRestante = $diferenciaAbs % $numItems;
        
        $i = 0;
        foreach ($detalles as $key => $detalle) {
            $ajuste = $diferenciaPorItem;
            if ($i < $diferenciaRestante) {
                $ajuste++;
            }
            if ($diferencia > 0) {
                $detalle->descuento += $ajuste;
            } else {
                $detalle->descuento -= $ajuste;
            }
            $detalle->valor_total = $detalle->subtotal - $detalle->descuento;
            $i++;
        }
    }

    private function tieneProntoPago($id_nit = null, $id_cuenta_gasto = null, $fechaManual = null)
    {
        if (!$id_nit || !$id_cuenta_gasto) {
            return false;
        }
        return DocumentosGeneral::where('id_nit', $id_nit)
            ->where('id_cuenta', $id_cuenta_gasto)
            ->where('fecha_manual', 'LIKE', $fechaManual . '%')
            ->exists();
    }

	private function carteraDocumentosQuery()
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
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
                $startOfMonth = Carbon::parse($this->periodo)->startOfMonth();
                $endOfMonth = Carbon::parse($this->periodo)->endOfMonth();

                $query->whereBetween('DG.fecha_manual', [$startOfMonth, $endOfMonth]);
            })
            ->when($this->id_nit, function ($query) {
				$query->where('DG.id_nit', '=', $this->id_nit);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery()
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
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->periodo);
			})
            ->when($this->id_nit, function ($query) {
				$query->where('DG.id_nit', '=', $this->id_nit);
			});

        return $anterioresQuery;
    }

    private function roundNumber($number, $redondeo = null)
    {
        if ($redondeo == 0) {
            return (int) round($number);
        } elseif ($redondeo > 0) {
            return round($number / $redondeo) * $redondeo;
        } else {
            return $number;
        }
    }
}