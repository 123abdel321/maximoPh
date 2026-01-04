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
	public $redondeoIntereses;
    public $redondeoProntoPago;
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
        $this->redondeoIntereses = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeoIntereses = $this->redondeoIntereses ? $this->redondeoIntereses->valor : 0;
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
            $qrFactura = Entorno::where('nombre', 'qr_facturas')->first();
            $qrFactura = $qrFactura ? $qrFactura->valor : null;
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

            $inicioMes = Carbon::now()->startOfMonth()->format('Y-m-d');
            $facturasMesDescuento = $this->getFacturaMes($id_nit, $inicioMes, $totales->fecha_manual);

            foreach ($facturaciones as $facturacion) {
    
                if (floatval($facturacion->saldo_anterior) > 0) {
                    $tieneSaldoAnterior = true;
                    $dataDescuento = [];
                    // Si hay saldo anterior, no se puede aplicar pronto pago normal, solo para morosos
                }

                $descuento = 0;
                $concepto = $facturacion->concepto == 'SALDOS INICIALES' ? $facturacion->nombre_cuenta : $facturacion->concepto;

                $conceptoFactura = DB::connection('max')
                    ->table('concepto_facturacions')
                    ->where('id_cuenta_cobrar', $facturacion->id_cuenta)
                    ->first();
                
                if ($conceptoFactura && $conceptoFactura->porcentaje_pronto_pago > 0) {
                    $diaHoy = intval(Carbon::now()->format('d'));
                    $keyDescuento = Carbon::now()->format('Ym').$conceptoFactura->dias_pronto_pago;
                    
                    // CASO 1: Pronto pago para morosos (aplica siempre, incluso con saldo anterior)
                    if ($conceptoFactura->pronto_pago_morosos == 1) {
                        $tieneDescuentoProntoPago = true;
                        
                        // Para morosos, el descuento se aplica sobre el total de facturas del mes
                        if ($facturasMesDescuento && isset($facturasMesDescuento->detalle[$facturacion->id_cuenta])) {
                            if ($facturasMesDescuento->detalle[$facturacion->id_cuenta]->aprobado == false) {
                                $descuento = $facturasMesDescuento->detalle[$facturacion->id_cuenta]->descuento;
                                $facturasMesDescuento->detalle[$facturacion->id_cuenta]->aprobado = true;
                            }
                        } else {
                            // Calcular descuento sobre total_facturas si no hay datos del mes
                            $descuento = $facturacion->total_facturas * ($conceptoFactura->porcentaje_pronto_pago / 100);
                        }
                        
                        $totalDescuento += $descuento;
                        
                        if (array_key_exists($keyDescuento, $dataDescuento)) {
                            $dataDescuento[$keyDescuento]['descuento'] += $descuento;
                        } else {
                            $dataDescuento[$keyDescuento] = [
                                'fecha_limite' => Carbon::now()->format('Y-m-'.$conceptoFactura->dias_pronto_pago),
                                'descuento' => $descuento
                            ];
                        }
                    }
                    // CASO 2: Pronto pago normal (solo si NO hay saldo anterior y está dentro del plazo)
                    else if (!$tieneSaldoAnterior && $conceptoFactura->pronto_pago && $conceptoFactura->dias_pronto_pago >= $diaHoy) {
                        $tieneDescuentoProntoPago = true;
                        
                        // Usar el descuento calculado por getFacturaMes() si está disponible
                        if ($facturasMesDescuento && isset($facturasMesDescuento->detalle[$facturacion->id_cuenta])) {
                            if ($facturasMesDescuento->detalle[$facturacion->id_cuenta]->aprobado == false) {
                                $descuento = $facturasMesDescuento->detalle[$facturacion->id_cuenta]->descuento;
                                $facturasMesDescuento->detalle[$facturacion->id_cuenta]->aprobado = true;
                            }
                        } else {
                            // Calcular descuento sobre las facturas del mes (no sobre total_facturas que incluye saldo anterior)
                            $descuento = $facturacion->total_facturas * ($conceptoFactura->porcentaje_pronto_pago / 100);
                        }
                        
                        $totalDescuento += $descuento;
                        
                        if (array_key_exists($keyDescuento, $dataDescuento)) {
                            $dataDescuento[$keyDescuento]['descuento'] += $descuento;
                        } else {
                            $dataDescuento[$keyDescuento] = [
                                'fecha_limite' => Carbon::now()->format('Y-m-'.$conceptoFactura->dias_pronto_pago),
                                'descuento' => $descuento
                            ];
                        }
                    }
                }

                $dataCuentas[] = (object)[
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
            
            $max_length = 40;
            $apartamentos_original = $getNit->apartamentos;
            if (mb_strlen($apartamentos_original, 'UTF-8') > $max_length) $apartamentos_limitado = mb_substr($apartamentos_original, 0, $max_length, 'UTF-8') . '...';
            else $apartamentos_limitado = $apartamentos_original;

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

            array_push($dataFacturas, (object)[
                'nit' => $nit,
                'nombre_cuenta' => '',
                'cuentas' => $dataCuentas,
                'descuentos' => $dataDescuento,
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
                'saldo_final' => $totales->saldo_final,
                'pronto_pago' => $tieneDescuentoProntoPago
            ]);
        }
        
        $texto1 = Entorno::where('nombre', 'factura_texto1')->first();
        $texto2 = Entorno::where('nombre', 'factura_texto2')->first();
        
        return [
            'texto_1' => $texto1 ? $texto1->valor : '',
            'texto_2' => $texto2 ? $texto2->valor : '',
            'qrFactura' => $qrFactura,
            'empresa' => $this->empresa,
            'facturas' => $dataFacturas,
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
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                CF.pronto_pago_morosos AS pronto_pago_morosos,
                FD.documento_referencia,
                0 AS aprobado,
                SUM(FD.valor) AS subtotal,

                -- Calcula si aplica descuento
                CASE
                    WHEN CF.pronto_pago_morosos = 1 
                        OR CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}') THEN 
                        ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
                    ELSE 0
                END AS descuento,

                -- Calcula valor total
                CASE
                    WHEN CF.pronto_pago_morosos = 1 
                        OR CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}') THEN 
                        SUM(FD.valor) - (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                    ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FA.id IS NOT NULL
                AND FD.fecha_manual = '{$inicioMes}'
                AND FD.naturaleza_opuesta = 0
                AND CF.porcentaje_pronto_pago > 0
                AND FA.pronto_pago IS NULL
                
            GROUP BY FD.id_cuenta_por_cobrar
        ");

        $facturas = collect($facturas);
        
        if (!count($facturas)) return false;

        $data = (object)[
            'id_factura' => $facturas[0]->id_factura,
            'has_pronto_pago' => $facturas[0]->has_pronto_pago,
            'subtotal' => 0,
            'descuento' => 0,
            'valor_total' => 0,
            'detalle' => []
        ];

        foreach ($facturas as $factura) {
            $data->subtotal+= $factura->subtotal;
            $data->descuento+= $factura->descuento;
            $data->valor_total+= $factura->valor_total;
            $data->detalle[$factura->id_cuenta_por_cobrar] = $factura;
        }

        if ($this->redondeoProntoPago) {
            $data->descuento = $this->roundNumber($data->descuento, $this->redondeoProntoPago);
        }

        return $data;
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

    private function roundNumber($number, $redondeo = null)
    {        
        // Caso 1: Si el valor de redondeo es 0, elimina todos los decimales (redondea a entero)
        if ($redondeo && $redondeo == 0) {
            return (int) round($number); // Cast a int para eliminar decimales
        }
        // Caso 2: Si el valor de redondeo es mayor que 0, aplica el redondeo específico
        elseif ($redondeo && $redondeo > 0) {
            return round($number / $redondeo) * $redondeo;
        }
        // Caso 3: Si no hay configuración, retorna el número sin cambios
        else {
            return $number;
        }
    }
}