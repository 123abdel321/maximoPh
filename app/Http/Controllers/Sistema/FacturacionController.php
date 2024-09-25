<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Config;
use App\Helpers\Extracto;
use App\Mail\GeneralEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Printers\FacturacionPdf;
use App\Helpers\Printers\FacturacionPdfMultiple;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PortafolioERP\FacturacionERP;
use App\Helpers\PortafolioERP\EliminarFactura;
use App\Helpers\PortafolioERP\EliminarFacturas;
use App\Jobs\ProcessFacturacionGeneral;
use App\Jobs\ProcessFacturacionGeneralDelete;
use App\Jobs\ProcessFacturacionGeneralCausar;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\CuotasMultas;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Sistema\FacturacionDetalle;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Portafolio\DocumentosGeneral;

class FacturacionController extends Controller
{
    protected $facturas = null;
    protected $saldoBase = 0;
    protected $prontoPago = false;
    protected $countIntereses = 0;
    protected $extractosAgrupados = [];
    protected $valoresBaseProximaAdmin = 0;
    protected $documento_referencia_agrupado = 0;
    
    public function index ()
    {
        $totalInmuebles = Inmueble::count();
        $areaM2Total = Inmueble::sum('area');
        $coeficienteTotal = Inmueble::sum('coeficiente');
        $valorRegistroPresupuesto = InmuebleNit::sum('valor_total');
        $numero_total_unidades = Entorno::where('nombre', 'numero_total_unidades')->first();
        $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first();
        $valor_total_presupuesto = Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first();
        $causacion_mensual_rapida = Entorno::where('nombre', 'causacion_mensual_rapida')->first();
        $presupuesto_mensual = Entorno::where('nombre', 'presupuesto_mensual')->first();
        $valor_total_presupuesto = $valor_total_presupuesto && $valor_total_presupuesto->valor ? $valor_total_presupuesto->valor : 0;
        $presupuesto_mensual = $presupuesto_mensual && $presupuesto_mensual->valor ? $presupuesto_mensual->valor : 0;

        if (!$presupuesto_mensual) $valor_total_presupuesto = $valor_total_presupuesto / 12;

        $data = [
            'numero_total_unidades' => $numero_total_unidades ? $numero_total_unidades->valor : '0',
            'numero_registro_unidades' => $totalInmuebles,
            'area_total_m2' => $area_total_m2 ? $area_total_m2->valor : '0',
            'area_registro_m2' => $areaM2Total,
            'valor_total_presupuesto' => $valor_total_presupuesto ? $valor_total_presupuesto : '0',
            'causacion_mensual_rapida' => $causacion_mensual_rapida ? $causacion_mensual_rapida->valor : '0',
            'valor_registro_presupuesto' => $valorRegistroPresupuesto,
            'valor_registro_coeficiente' => $coeficienteTotal / 100,
        ];

        return view('pages.operaciones.facturacion.facturacion-view', $data);
    }

    public function read (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
            
            $nitSsearch = $search ? $this->nitsSearch($search) : [];
            $query = $this->inmueblesNitsQuery($empresa, $search, $nitSsearch, $request);
            $query->unionAll($this->cuotasMultasQuery($empresa, $search, $nitSsearch, $request));

            $facturacion = DB::connection('max')
                ->table(DB::raw("({$query->toSql()}) AS facturaciondata"))
                ->mergeBindings($query)
                ->select(
                    'nombre_inmueble',
                    'area_inmueble',
                    'id_nit',
                    'tipo',
                    'porcentaje_administracion',
                    'valor_total',
                    'nombre_concepto',
                    'tipo_factura'
                );

            $facturacionTotals = $facturacion->get();

            $facturacionPaginate = $facturacion->skip($start)
                ->take($rowperpage);

            $dataTotals = (object)[
                'nombre_inmueble' => 'TOTAL FACTURA',
                'area_inmueble' => '',
                'id_nit' => '',
                'tipo' => '',
                'porcentaje_administracion' => '',
                'valor_total' => $facturacionTotals->sum('valor_total'),
                'nombre_concepto' => '',
                'tipo_factura' => 2,
            ];

            $dataFactura = $facturacionPaginate->get()->toArray();
            $dataFactura = $this->asignarNombreNit($dataFactura);
            
            array_push($dataFactura, $dataTotals);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $facturacionTotals->count(),
                'iTotalDisplayRecords' => $facturacionTotals->count(),
                'data' => $dataFactura,
                'perPage' => $rowperpage,
                'message'=> 'Facturas generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function readDetalle(Request $request)
    {
        try {
            
            $dataFactura = [];
            $valorInmuebles = 0;
            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $finMes = date('Y-m-t', strtotime($periodo_facturacion));

            $inmuebleNit = InmuebleNit::whereNotNull('valor_total')
                ->groupBy('id_nit')
                ->get();
                
            foreach ($inmuebleNit as $nit) {

                $factura = Facturacion::where('id_nit', $nit->id_nit)
                    ->where('fecha_manual', $finMes)
                    ->first();
                
                $facturaNit = $this->dataDetalleFactura($nit, $factura, $request->get('reprocesar'));
                $valorInmuebles+= $facturaNit['valor_inmuebles'];
                $dataFactura[] = $facturaNit;
            }

            $totales = [
                'total_facturas' => count($inmuebleNit),
                'valor_inmuebles' => $valorInmuebles
            ];

            return response()->json([
                "success"=>true,
                'data' => $dataFactura,
                'totales' => $totales,
                "message"=> ''
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generarIndividual (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_cuenta_pronto_pago = Entorno::where('nombre', 'id_cuenta_pronto_pago')->first();
            $this->documento_referencia_agrupado = Entorno::where('nombre', 'documento_referencia_agrupado')->first();
            $this->documento_referencia_agrupado = $this->documento_referencia_agrupado ? $this->documento_referencia_agrupado->valor : 0;

            $inicioMes = date('Y-m', strtotime($periodo_facturacion));
            $finMes = date('Y-m-t', strtotime($periodo_facturacion));

            $dataGeneral = [
                'inmuebles' => [],
                'extras' => []
            ];
            
            $inmueblesFacturar = $this->inmueblesNitFacturar($request->get('id'));
            $cuotasMultasFacturarCxC = $this->extrasNitFacturarCxC($request->get('id'), $periodo_facturacion);
            $cuotasMultasFacturarCxP = $this->extrasNitFacturarCxP($request->get('id'), $periodo_facturacion);
            // dd($inmueblesFacturar);
            $this->eliminarFactura($request->get('id'), $inicioMes);

            $factura = Facturacion::create([//CABEZA DE FACTURA
                'id_comprobante' => $id_comprobante_ventas,
                'id_nit' => $request->get('id'),
                'fecha_manual' => $inicioMes.'-01',
                'token_factura' => $this->generateTokenDocumento(),
                'valor' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $valoresExtra = 0;
            $valoresAdmon = 0;
            $totalInmuebles = 0;
            $valoresIntereses = 0;

            //COBRAR INTERESES
            $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
                $factura->id_nit,
                [3,7],
                null,
                $periodo_facturacion
            ))->actual()->get();
            // dd($periodo_facturacion, $extractos);
            //AGRUPAMOS 
            $this->extractosAgrupados = [];
            foreach ($extractos as $extracto) {
                $extracto = (object)$extracto;
                if (!$this->cobrarIntereses($extracto->id_cuenta)) continue;

                $this->countIntereses++;
                if (array_key_exists($extracto->id_cuenta, $this->extractosAgrupados)) {
                    $this->extractosAgrupados[$extracto->id_cuenta]->total_abono+= $extracto->total_abono;
                    $this->extractosAgrupados[$extracto->id_cuenta]->total_facturas+= $extracto->total_facturas;
                    $this->extractosAgrupados[$extracto->id_cuenta]->saldo+= $extracto->saldo;
                } else {
                    $this->extractosAgrupados[$extracto->id_cuenta] = (object)[
                        'id_nit' => $extracto->id_nit,
                        'concepto' => $extracto->concepto,
                        'total_abono' => $extracto->total_abono,
                        'total_facturas' => $extracto->total_facturas,
                        'documento_referencia' => $extracto->documento_referencia,
                        'saldo' => $extracto->saldo,
                    ];
                }
            }

            $primerInmueble = count($inmueblesFacturar) ? $inmueblesFacturar[0] : false;
            [$valores, $detalleFacturasInteres] = $this->generarFacturaInmuebleIntereses($factura, $primerInmueble, request()->user()->id_empresa, $periodo_facturacion);

            $valoresIntereses+= $valores;

            if ($valoresIntereses) {
                $dataGeneral['extras']['intereses'] = (object)[
                    'items' => 1,
                    'id_concepto_facturacion' => 'intereses',
                    'valor_causado' => $valoresIntereses
                ];
            };

            //TRAER ANTICIPOS
            $anticiposNit = $this->totalAnticipos($factura->id_nit, request()->user()->id_empresa);
            $anticiposDisponibles = $anticiposNit;
            
            //RECORREMOS CUOTAS Y MULTAS CXP
            foreach ($cuotasMultasFacturarCxP as $cuotaMultaFactura) {
                if (array_key_exists($cuotaMultaFactura->id_concepto_facturacion, $dataGeneral['extras'])) {
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->items+= 1;
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->valor_causado+= $cuotaMultaFactura->valor_total;
                } else {
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion] = (object)[
                        'items' => 1,
                        'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
                        'valor_causado' => $cuotaMultaFactura->valor_total
                    ];
                }
                $valoresExtra+= $cuotaMultaFactura->valor_total;
                $anticiposDisponibles+= $cuotaMultaFactura->valor_total;
                
                $documentoReferencia = $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                $this->facturas[] = (object)[
                    'documento_referencia' => $documentoReferencia,
                    'saldo' => floatval($cuotaMultaFactura->valor_total)
                ];
            }

            $this->prontoPago = $this->calcularTotalDeuda($inmueblesFacturar, $cuotasMultasFacturarCxC, $anticiposDisponibles, $valoresIntereses);
            if ($anticiposDisponibles > 0 && $valoresIntereses) {
                $anticiposDisponibles = $this->generarCruceIntereses($factura, $detalleFacturasInteres, $anticiposDisponibles);
            }

            //RECORREMOS CUOTAS Y MULTAS CXC
            foreach ($cuotasMultasFacturarCxC as $cuotaMultaFactura) {
                if (array_key_exists($cuotaMultaFactura->id_concepto_facturacion, $dataGeneral['extras'])) {
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->items+= 1;
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->valor_causado+= $cuotaMultaFactura->valor_total;
                } else {
                    $dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion] = (object)[
                        'items' => 1,
                        'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
                        'valor_causado' => $cuotaMultaFactura->valor_total
                    ];
                }
                
                $valoresExtra+= $cuotaMultaFactura->valor_total;
                $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                $documentoReferencia = date('Y-m', strtotime($periodo_facturacion));
                if ($anticiposDisponibles > 0) {
                    $anticiposDisponibles = $this->generarFacturaAnticipos($factura, $cuotaMultaFactura, 0, $anticiposDisponibles, $documentoReferencia);
                }
            }
            
            //RECORREMOS INMUEBLES DEL NIT
            foreach ($inmueblesFacturar as $inmuebleFactura) {

                if (count($inmueblesFacturar) > 1) $totalInmuebles++;
                if (array_key_exists($inmuebleFactura->id_concepto_facturacion, $dataGeneral['inmuebles'])) {
                    $dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion]->items+= 1;
                    $dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion]->valor_causado+= $inmuebleFactura->valor_total;
                    
                } else {
                    $dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion] = (object)[
                        'items' => 1,
                        'id_concepto_facturacion' => $inmuebleFactura->id_concepto_facturacion,
                        'valor_causado' => $inmuebleFactura->valor_total
                    ];
                }
                $valoresAdmon+= $inmuebleFactura->valor_total;
                $documentoReferencia = $this->generarFacturaInmueble($factura, $inmuebleFactura, $totalInmuebles);
                
                if ($anticiposDisponibles > 0) {
                    $anticiposDisponibles = $this->generarFacturaAnticipos($factura, $inmuebleFactura, $totalInmuebles, $anticiposDisponibles, $documentoReferencia);
                }
            }

            $response = (new FacturacionERP(
                $inicioMes.'-01',
                $request->get('id')
            ))->send(request()->user()->id_empresa);

            // if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            //     dd($response['response']);
            //     DB::connection('max')->rollback();
                
            //     return response()->json([
            //         "success"=>false,
            //         'data' => [],
            //         "message"=> $response['response']->message
            //     ], 422);
            // }

            $factura->valor = ($valoresExtra + $valoresAdmon + $valoresIntereses);
            $factura->valor_admon = $valoresAdmon;
            $factura->valor_intereses = $valoresIntereses;
            $factura->count_intereses = $this->countIntereses;
            $factura->saldo_base = $this->saldoBase;
            $factura->valor_anticipos = $anticiposNit - $anticiposDisponibles;
            $factura->valor_cuotas_multas = $valoresExtra;
            $factura->count_cuotas_multas = count($cuotasMultasFacturarCxC);
            $factura->mensajes = json_encode($dataGeneral);
            $factura->save();

            DB::connection('max')->commit();
            
            return response()->json([
                "success"=>true,
                'data' => $factura,
                "message"=>'Facturación confirmada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generarGeneral ()
    {
        try {

            ProcessFacturacionGeneral::dispatch(request()->user()->id, request()->user()->id_empresa);

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Documentos generada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generarGeneralDelete ()
    {
        try {

            ProcessFacturacionGeneralDelete::dispatch(request()->user()->id, request()->user()->id_empresa);

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Eliminación confirmada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generarGeneralCausar ()
    {
        try {

            ProcessFacturacionGeneralCausar::dispatch(request()->user()->id, request()->user()->id_empresa);
            
            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Causación confirmada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function confirmar ()
    {
        try {
            DB::connection('max')->beginTransaction();

            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;

            Entorno::where('nombre', 'periodo_facturacion')
                ->update([
                    'valor' => date("Y-m-d", strtotime("+1 month", strtotime($periodo_facturacion)))
                ]);

            DB::connection('max')->commit();

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Facturación confirmada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generar (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $nitsFacturacion = InmuebleNit::select('id_nit')->groupBy('id_nit')->get();

            $response = (new EliminarFacturas(
                $periodo_facturacion
            ))->send(request()->user()->id_empresa);

            if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
                DB::connection('max')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> $response['response']->message
                ], 422);
            }

            //ELIMINAMOS LAS FACTURACIONES EN LA MISMA FECHA
            Facturacion::where('fecha_manual', $periodo_facturacion)->delete();
            FacturacionDetalle::where('fecha_manual', $periodo_facturacion)->delete();
            
            //RECORREMOS NITS CON INMUEBLES
            foreach ($nitsFacturacion as $nit) {

                $factura = Facturacion::create([//CABEZA DE FACTURA
                    'id_comprobante' => $id_comprobante_ventas,
                    'id_nit' => $nit->id_nit,
                    'fecha_manual' => $periodo_facturacion,
                    'token_factura' => $this->generateTokenDocumento(),
                    'valor' => 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);

                $valor = 0;
                $inicioMes = date('Y-m', strtotime($periodo_facturacion));
                $finMes = date('Y-m-t', strtotime($periodo_facturacion));

                $inmueblesFacturar = InmuebleNit::with('inmueble.concepto', 'inmueble.zona')//INMUEBLES DEL NIT
                    ->where('id_nit', $nit->id_nit)
                    ->get();

                $cuotasMultasFacturar = CuotasMultas::with('inmueble.zona', 'concepto')//CUOTAS Y MULTAS DEL NIT
                    ->where('id_nit', $nit->id_nit)
                    ->whereDate("fecha_inicio", '<=', $inicioMes.'-01')
                    ->whereDate("fecha_fin", '>=', $finMes)
                    ->get();

                $totalAnticipos = $this->totalAnticipos($factura->id_nit, request()->user()->id_empresa);
                $totalInmuebles = 0;

                //RECORREMOS INMUEBLES DEL NIT
                foreach ($inmueblesFacturar as $inmuebleFactura) {
                    $cxcIntereses = $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar;
                    if (count($inmueblesFacturar) > 1) $totalInmuebles++;
                    
                    $inicioMes = date('Y-m', strtotime($periodo_facturacion));
                    $valor+= $inmuebleFactura->valor_total;

                    $documentoReferencia = $this->generarFacturaInmueble($factura, $inmuebleFactura, $totalInmuebles);
                    if ($totalAnticipos > 0) {
                        $totalAnticipos = $this->generarFacturaAnticipos($factura, $inmuebleFactura, $totalInmuebles, $totalAnticipos, $documentoReferencia);
                    }
                }
                //RECORREMOS CUOTAS Y MULTAS
                foreach ($cuotasMultasFacturar as $cuotaMultaFactura) {
                    $valor+= $cuotaMultaFactura->valor_total;
                    $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                }
                //COBRAR INTERESES
                $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
                    $factura->id_nit,
                    [3,7],
                    null,
                    $periodo_facturacion
                ))->actual()->get();

                //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
                if (!count($extractos)) return;
                //AGRUPAMOS 
                $this->extractosAgrupados = [];
                foreach ($extractos as $extracto) {
                    $extracto = (object)$extracto;
                    
                    if (!$this->cobrarIntereses($extracto->id_cuenta)) continue;
                    $this->countIntereses++;
                    if (array_key_exists($extracto->id_cuenta, $this->extractosAgrupados)) {
                        $this->extractosAgrupados[$extracto->id_cuenta]->total_abono+= $extracto->total_abono;
                        $this->extractosAgrupados[$extracto->id_cuenta]->total_facturas+= $extracto->total_facturas;
                        $this->extractosAgrupados[$extracto->id_cuenta]->saldo+= $extracto->saldo;
                    } else {
                        $this->extractosAgrupados[$extracto->id_cuenta] = (object)[
                            'id_nit' => $extracto->id_nit,
                            'concepto' => $extracto->concepto,
                            'total_abono' => $extracto->total_abono,
                            'total_facturas' => $extracto->total_facturas,
                            'saldo' => $extracto->saldo,
                        ];
                    }
                }

                [$valores, $detalleFacturas] = $this->generarFacturaInmuebleIntereses($factura, $inmueblesFacturar[0], request()->user()->id_empresa);
                $valor+= $valores;
                $factura->valor = $valor;
                $factura->save();
            }

            Entorno::where('nombre', 'periodo_facturacion')
                ->update([
                    'valor' => date("Y-m-d", strtotime("+1 month", strtotime($periodo_facturacion)))
                ]);

            (new FacturacionERP(
                $periodo_facturacion
            ))->send(request()->user()->id_empresa);

            DB::connection('max')->commit();

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Facturación creada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function totales ()
    {
        $extrasConceptos = [];
        $inmueblesConceptos = [];
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));
        $causacion_mensual_rapida = Entorno::where('nombre', 'causacion_mensual_rapida')->first();
        $causacion_mensual_rapida = $causacion_mensual_rapida ? $causacion_mensual_rapida->valor : 0;

        $inmuebles = DB::connection('max')->table('inmueble_nits')->select(
                'CFA.id_cuenta_cobrar',
                'CFA.nombre_concepto AS nombre_concepto',
                DB::raw("INM.id_concepto_facturacion"),
                DB::raw("COUNT(INM.id) AS items"),
                DB::raw("SUM(inmueble_nits.valor_total) AS valor_total")
            )
            ->leftJoin('inmuebles AS INM', 'inmueble_nits.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS ZO', 'INM.id_zona', 'ZO.id')
            ->leftJoin('concepto_facturacions AS CFA', 'INM.id_concepto_facturacion', 'CFA.id')
            ->groupBy('id_concepto_facturacion')
            ->get()
            ->toArray();

        $fecha_facturar = date('Y-m', strtotime($periodo_facturacion));
        $cuotasExtra = CuotasMultas::select(
                DB::raw("id_concepto_facturacion"),
                DB::raw("COUNT(id) AS items"),
                DB::raw("SUM(valor_total) AS valor_total")
            )
            ->where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar)
            ->groupBy('id_concepto_facturacion')
            ->get();

        $causadoTotal = 0;
        $causadoCount = 0;
        $countInmuebles = 0;

        foreach ($inmuebles as $inmueble) {
            if (!$inmueble->id_concepto_facturacion) continue;
            $countInmuebles+= $inmueble->items;
            $inmueblesConceptos[$inmueble->id_cuenta_cobrar] = (object)[
                'id_concepto_facturacion' => $inmueble->id_concepto_facturacion,
                'concepto_facturacion' => $inmueble->nombre_concepto,
                'items' => $inmueble->items,
                'saldo_anterior' => 0,
                'valor_total' => round($inmueble->valor_total),
                'causado_total'=> 0,
                'causado_count'=> 0,
                'diferencia'=> 0,
            ];
        }

        $countCuotas = 0;
        foreach ($cuotasExtra as $cuotas) {
            if (!$cuotas->id_concepto_facturacion) continue;
            $countCuotas+= $cuotas->items;
            $concepto = ConceptoFacturacion::find($cuotas->id_concepto_facturacion);
            $extrasConceptos[$concepto->id_cuenta_cobrar] = (object)[
                'id_concepto_facturacion' => $cuotas->id_concepto_facturacion,
                'concepto_facturacion' => $concepto->nombre_concepto,
                'items' => $cuotas->items,
                'saldo_anterior' => 0,
                'valor_total' => round($cuotas->valor_total),
                'causado_total'=> 0,
                'causado_count'=> 0,
                'diferencia'=> 0,
            ];
        }

        //INTERESES
        $fechaPeriodo = date('Y-m-d', strtotime(date('Y-m', strtotime($periodo_facturacion)).'-01'. ' - 1 day'));
        
        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            null,
            [3,7],
            null,
            $fechaPeriodo
        ))->actual()->get();

        $extractosNits = [];

        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $extractosNits[$extracto->id_nit][] = $extracto;
            if (array_key_exists($extracto->id_cuenta, $inmueblesConceptos)) {
                $inmueblesConceptos[$extracto->id_cuenta]->saldo_anterior+= $extracto->saldo;
            }

            if (array_key_exists($extracto->id_cuenta, $extrasConceptos)) {
                $extrasConceptos[$extracto->id_cuenta]->saldo_anterior+= $extracto->saldo;
            }
        }

        //ANTICIPOS
        $anticipos = (new Extracto(
            null,
            [4,8],
            null,
            $fechaPeriodo
        ))->actual()->get();

        $anticiposNits = [];

        foreach ($anticipos as $anticipo) {
            $anticipo = (object)$anticipo;
            $anticiposNits[$anticipo->id_nit][] = $anticipo;
        }

        $total_anticipos = 0;
        $count_anticipos = 0;
        $total_intereses = 0;
        $count_intereses = 0;

        $totales = DB::connection('max')->table('inmueble_nits')->select(
            DB::raw("SUM(valor_total) AS valor_total")
        )->first();
        $totalSaldoAnteriorInmuebles = array_sum(array_column($inmueblesConceptos,'saldo_anterior'));
        $inmueblesConceptos[] = (object)[
            'id_concepto_facturacion' => 'total_inmuebles',
            'concepto_facturacion' => 'TOTALES',
            'items' => $countInmuebles,
            'saldo_anterior' => $totalSaldoAnteriorInmuebles,
            'valor_total' => round($totales->valor_total),
            'causado_total'=> 0,
            'causado_count'=> 0,
            'diferencia'=> 0,
        ];

        $inmuebleNitData = []; 
        $query = $this->getInmueblesNitsQuery();
        $query->unionAll($this->getCuotasMultasNitsQuery(date('Y-m', strtotime($periodo_facturacion))));

        $facturarNit = DB::connection('max')
            ->table(DB::raw("({$query->toSql()}) AS nits"))
            ->mergeBindings($query)
            ->select(
                'id_nit'
            )
            ->groupByRaw('id_nit')
            ->get();

        // dd($facturarNit);
        $saldo_anterior = 0;
        $count_saldo_anterior = 0;
        $saldo_base = 0;
        $count_saldo_base = 0;

        foreach ($facturarNit as $nit) {

            if (!$causacion_mensual_rapida) {
                $nits = Nits::find($nit->id_nit);
                if($nits) {
                    $inmuebleNitData[] = (object)[
                        'id_nit' => $nits->id,
                        'nombre_nit' => $nits->nombre_completo,
                        'documento_nit' => $nits->numero_documento,
                        'facturado' => false
                    ];
                }
            }

            $sumaRapida = 0;
            if (array_key_exists($nit->id_nit, $extractosNits)) {
                $count_saldo_anterior++;
                $tieneCXC = false;                
                foreach ($extractosNits[$nit->id_nit] as $extracto) {
                    $saldo = floatval($extracto->saldo);
                    $saldo_anterior+= $saldo;
                    if (!$this->cobrarIntereses($extracto->id_cuenta)) continue;
                    $tieneCXC = true;
                    $sumaRapida+= floatval($extracto->saldo);
                    $saldo_base+= $saldo;
                    $total_intereses+= $this->roundNumber($saldo * ($porcentaje_intereses_mora / 100));
                }
                if ($tieneCXC) { 
                    $count_saldo_base++;
                    $count_intereses++;
                };
            }

            if (array_key_exists($nit->id_nit, $anticiposNits)) {
                foreach ($anticiposNits[$nit->id_nit] as $anticipos) {
                    $anticipo = floatval($anticipos->saldo);
                    $total_anticipos+= $anticipo;
                    $count_anticipos++;
                }
            }
        }
        
        $extrasConceptos[] = (object)[
            'id_concepto_facturacion' => 'intereses',
            'concepto_facturacion' => 'INTERESES %'.$porcentaje_intereses_mora,
            'items' => $count_intereses,
            'saldo_anterior' => $totalSaldoAnteriorInmuebles,
            'valor_total' => $this->roundNumber($total_intereses),
            'causado_total'=> 0,
            'causado_count'=> 0,
            'diferencia'=> 0,
        ];

        $cuotasMultas = CuotasMultas::where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar);

        $extrasConceptos[] = (object)[
            'id_concepto_facturacion' => 'total_extras',
            'concepto_facturacion' => 'TOTALES',
            'items' => $count_intereses + $countCuotas,
            'saldo_anterior' => array_sum(array_column($extrasConceptos,'saldo_anterior')),
            'valor_total' => round($cuotasMultas->sum('valor_total') + $total_intereses),
            'causado_total'=> 0,
            'causado_count'=> 0,
            'diferencia'=> 0,
        ];

        $existe_facturacion = Facturacion::where('fecha_manual', $finMes)->count();

        return response()->json([
            "success"=>true,
            'data' => [
                'inmuebles' => array_values($inmueblesConceptos),
                'cuotas' => array_values($extrasConceptos),
                'periodo_facturacion' => $periodo_facturacion,
                'existe_facturacion' => $existe_facturacion,
                'saldo_anterior' => $saldo_anterior,
                'count_saldo_anterior' => $count_saldo_anterior,
                'saldo_base' => $saldo_base,
                'count_saldo_base' => $count_saldo_base,
                'total_anticipos' => $total_anticipos,
                'count_anticipos' => $count_anticipos,
                'nits' => $inmuebleNitData,
                'area_registro_m2' => Inmueble::sum('area'),
                'area_total_m2' => Entorno::where('nombre', 'area_total_m2')->first()->valor,
                'valor_total_presupuesto' => Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor / 12,
                'valor_registro_presupuesto' => floatval(InmuebleNit::sum('valor_total')),
                'numero_total_unidades' => floatval(Entorno::where('nombre', 'numero_total_unidades')->first()->valor),
                'numero_registro_unidades' => Inmueble::count(),
                'valor_registro_coeficiente' => Inmueble::sum('coeficiente'),
            ],
            "message"=>'Preview facturación generado con exito'
        ], 200);
    }    

    public function indexPdf(Request $request)
    {
        $periodo = Facturacion::select(
            \DB::raw("DATE_FORMAT(fecha_manual, '%Y%m%d') AS id"),
            \DB::raw("fecha_manual as text")
        )->groupBy('fecha_manual')
        ->orderBy('fecha_manual', 'DESC')
        ->first();

        $data = [
            'periodo_facturaciones' => $periodo
        ];

        return view('pages.informes.facturaciones.facturaciones-view', $data);
    }

    public function readPdf(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
            
            $query = $this->carteraDocumentosQuery($request);
            $query->unionAll($this->carteraAnteriorQuery($request));

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
                ->groupByRaw('id_nit')
                ->orderByRaw('cuenta, id_nit, documento_referencia, created_at');

            $facturacionTotals = $facturaciones->get();

            $facturacionPaginate = $facturaciones->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $facturacionTotals->count(),
                'iTotalDisplayRecords' => $facturacionTotals->count(),
                'data' => $facturacionPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Facturas generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function showPdf(Request $request)
    {
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
        // $data = (new FacturacionPdf($empresa, $request->get('id_nit'), $request->get('periodo')))->buildPdf()->getData();
        // return view('pdf.facturacion.facturaciones', $data);
        return (new FacturacionPdf($empresa, $request->get('id_nit'), $request->get('periodo')))
            ->buildPdf()
            ->showPdf();
    }

    public function showMultiplePdf(Request $request)
    {
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
        // if ($request->get('factura_fisica')
        $nits = $this->nitFacturaFisica($request->get('factura_fisica'));
        // $data = (new FacturacionPdfMultiple($empresa, $nits, $request->get('periodo')))->buildPdf()->getData();
        // return view('pdf.facturacion.facturaciones_multiples', $data);
        return (new FacturacionPdfMultiple($empresa, $nits, $request->get('periodo')))
            ->buildPdf()
            ->showPdf();
    }

    public function comboPeriodos (Request $request)
    {
        $periodo = Facturacion::select(
                \DB::raw("DATE_FORMAT(fecha_manual, '%Y%m%d') AS id"),
                \DB::raw("fecha_manual as text")
            )->groupBy('fecha_manual')
            ->orderBy('fecha_manual', 'DESC');

        if ($request->get("search")) {
            $periodo->where('fecha_manual', 'like', '%' .$request->get("search"). '%');
        }

        return $periodo->paginate(40);
    }

    public function email (Request $request)
    {
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();

        $query = $this->carteraDocumentosQuery($request);
        $query->unionAll($this->carteraAnteriorQuery($request));

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
            ->select(
                'id_nit',
                'email',
                'email_1',
                'email_2',
                'nombre_nit',
                'consecutivo',
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
            )
            ->mergeBindings($query)
            ->groupByRaw('id_nit')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->chunk(34, function ($nits) use($empresa, $request) {
                foreach ($nits as $nit) {
                    
                    $facturaPdf = (new FacturacionPdf($empresa, $nit->id_nit, $request->get('periodo')))
                        ->buildPdf()
                        ->saveStorage();

                    if ($nit->email) {
                        Mail::to($nit->email)
                        ->cc('noreply@maximoph.com')
                        ->bcc('bcc@maximoph.com')
                        ->queue(new GeneralEmail($empresa->razon_social, 'emails.factura', [
                            'nombre' => $nit->nombre_nit,
                            'factura' => $nit->consecutivo,
                            'valor' => $nit->saldo_final,
                        ], $facturaPdf));
                    }

                    if ($nit->email_1 && $nit->email != $nit->email_1) {
                        Mail::to($nit->email_1)
                        ->cc('noreply@maximoph.com')
                        ->bcc('bcc@maximoph.com')
                        ->queue(new GeneralEmail($empresa->razon_social, 'emails.factura', [
                            'nombre' => $nit->nombre_nit,
                            'factura' => $nit->consecutivo,
                            'valor' => $nit->saldo_final,
                        ], $facturaPdf));
                    }

                    if ($nit->email_2 && $nit->email != $nit->email_2 && $nit->email_1 != $nit->email_2) {
                        Mail::to($nit->email_2)
                        ->cc('noreply@maximoph.com')
                        ->bcc('bcc@maximoph.com')
                        ->queue(new GeneralEmail($empresa->razon_social, 'emails.factura', [
                            'nombre' => $nit->nombre_nit,
                            'factura' => $nit->consecutivo,
                            'valor' => $nit->saldo_final,
                        ], $facturaPdf));
                    }

                    Storage::disk('do_spaces')->delete($facturaPdf);
                }
            });

        return response()->json([
            "success"=> true,
            "message"=> 'Emails enviados con exito'
        ], 200);
    }

    private function getInmueblesNitsQuery()
    {
        $nits = DB::connection('max')->table('inmueble_nits AS IN')
            ->select(
                'IN.id_nit'
            );
        
        return $nits;
    }

    private function getCuotasMultasNitsQuery($fecha_facturar)
    {
        $nits = DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                'CM.id_nit'
            )
            ->where("CM.fecha_inicio", '<=', $fecha_facturar)
            ->where("CM.fecha_fin", '>=', $fecha_facturar);
        
        return $nits;
    }

    private function generarFacturaCuotaMulta(Facturacion $factura, $cuotaMultaFactura)
    {
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));
        
        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $cuotaMultaFactura->id_nit,
            'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
            'id_cuenta_por_cobrar' => $cuotaMultaFactura->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $cuotaMultaFactura->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_ventas,
            'id_centro_costos' => $cuotaMultaFactura->id_centro_costos,
            'fecha_manual' => $inicioMes.'-01',
            'documento_referencia' => $inicioMes,
            'valor' => round($cuotaMultaFactura->valor_total),
            'concepto' => $cuotaMultaFactura->nombre_concepto.' '.$cuotaMultaFactura->observacion,
            'naturaleza_opuesta' => false,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $inicioMes;
    }

    private function generarFacturaInmueble(Facturacion $factura, $inmuebleFactura, $totalInmuebles)
    {
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));

        $documentoReferenciaNumeroInmuebles = $this->generarDocumentoReferencia($inmuebleFactura, $totalInmuebles, $inicioMes);
        
        $this->valoresBaseProximaAdmin+= 0;

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_concepto_facturacion' => $inmuebleFactura->id_concepto_facturacion,
            'id_cuenta_por_cobrar' => $inmuebleFactura->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_ventas,
            'id_centro_costos' => $inmuebleFactura->id_centro_costos,
            'fecha_manual' => $inicioMes.'-01',
            'documento_referencia' => $documentoReferenciaNumeroInmuebles,
            'valor' => round($inmuebleFactura->valor_total),
            'concepto' => $inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre_zona.' '.$inmuebleFactura->nombre.' Coef:'.$inmuebleFactura->coeficiente,
            'naturaleza_opuesta' => false,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
        return $documentoReferenciaNumeroInmuebles;
    }

    private function generarCruceIntereses (Facturacion $factura, $detalleFacturas, $totalAnticipos)
    {
        $id_comprobante_notas = Entorno::where('nombre', 'id_comprobante_notas')->first()->valor;
        $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));
        
        foreach ($detalleFacturas as $detalleFactura) {
            if ($totalAnticipos <= 0) continue;
            $detalleFactura = (object)$detalleFactura;
            $totalAnticipar = 0;
            if ($totalAnticipos >= $detalleFactura->valor) {
                $totalAnticipar = $detalleFactura->valor;
                $totalAnticipos-= $detalleFactura->valor;
            } else {
                $totalAnticipar = $totalAnticipos;
                $totalAnticipos = 0;
            }
            
            foreach ($this->facturas as $key => $facturacxp) {
                if ($totalAnticipar <= 0) continue;

                $totalCruce = $totalAnticipar >= $facturacxp->saldo ? $facturacxp->saldo : $totalAnticipar;
                
                $facturaDetalle = FacturacionDetalle::create([
                    'id_factura' => $factura->id,
                    'id_nit' => $detalleFactura->id_nit,
                    'id_concepto_facturacion' => null,
                    'id_cuenta_por_cobrar' => $id_cuenta_anticipos,
                    'id_cuenta_ingreso' => $detalleFactura->id_cuenta_por_cobrar,
                    'id_comprobante' => $id_comprobante_notas,
                    'id_centro_costos' => $detalleFactura->id_centro_costos,
                    'fecha_manual' => $inicioMes.'-01',
                    'documento_referencia' => $detalleFactura->documento_referencia,
                    'documento_referencia_anticipo' => $facturacxp->documento_referencia,
                    'valor' => round($totalCruce),
                    'concepto' => 'CRUCE ANTICIPOS '.$detalleFactura->concepto,
                    'naturaleza_opuesta' => true,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);
                $totalAnticipar-= $totalCruce;
                $this->facturas[$key]->saldo-= $totalCruce;
            }
    
            foreach ($this->facturas as $key => $facturacxp) {
                if ($facturacxp->saldo <= 0) unset($this->facturas[$key]);
            }
        }
        return $totalAnticipos;
    }

    private function generarFacturaAnticipos(Facturacion $factura, $inmuebleFactura, $totalInmuebles, $totalAnticipos, $documentoReferencia)
    {
        $totalAnticipar = 0;
        $totalDescuento = 0;

        $id_comprobante_notas = Entorno::where('nombre', 'id_comprobante_notas')->first()->valor;
        $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;

        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));
        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        if ($totalAnticipos >= $inmuebleFactura->valor_total) {
            $totalAnticipar = $inmuebleFactura->valor_total;
            $totalAnticipos-= $inmuebleFactura->valor_total;
        } else {
            $totalAnticipar = $totalAnticipos;
            $totalAnticipos = 0;
        }
        
        if ($this->prontoPago && $inmuebleFactura->pronto_pago && $inmuebleFactura->porcentaje_pronto_pago) {
            if ($totalAnticipar == $inmuebleFactura->valor_total) {
                $totalDescuento = $inmuebleFactura->valor_total * ($inmuebleFactura->porcentaje_pronto_pago / 100);
                $totalAnticipar = $totalAnticipar - $totalDescuento;

                $facturaDetalle = FacturacionDetalle::create([
                    'id_factura' => $factura->id,
                    'id_nit' => $inmuebleFactura->id_nit,
                    'id_concepto_facturacion' => null,
                    'id_cuenta_por_cobrar' => $inmuebleFactura->id_cuenta_gasto,
                    'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_cobrar,
                    'id_comprobante' => $id_comprobante_notas,
                    'id_centro_costos' => $inmuebleFactura->id_centro_costos,
                    'fecha_manual' => $inicioMes.'-01',
                    'documento_referencia' => $documentoReferencia,
                    'valor' => round($totalDescuento),
                    'concepto' => 'PRONTO PAGO '.$inmuebleFactura->porcentaje_pronto_pago.'% BASE '. number_format($inmuebleFactura->valor_total).' '.$inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre,
                    'naturaleza_opuesta' => true,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);
            }
        }

        foreach ($this->facturas as $key => $facturacxp) {
            if ($totalAnticipar <= 0) continue;
            $totalCruce = $totalAnticipar >= $facturacxp->saldo ? $facturacxp->saldo : $totalAnticipar;
            
            $facturaDetalle = FacturacionDetalle::create([
                'id_factura' => $factura->id,
                'id_nit' => $inmuebleFactura->id_nit,
                'id_concepto_facturacion' => null,
                'id_cuenta_por_cobrar' => $id_cuenta_anticipos,
                'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_cobrar,
                'id_comprobante' => $id_comprobante_notas,
                'id_centro_costos' => $inmuebleFactura->id_centro_costos,
                'fecha_manual' => $inicioMes.'-01',
                'documento_referencia' => $documentoReferencia,
                'documento_referencia_anticipo' => $facturacxp->documento_referencia,
                'valor' => round($totalCruce),
                'concepto' => 'CRUCE ANTICIPOS '.$inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre,
                'naturaleza_opuesta' => true,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $totalAnticipar-= $totalCruce;
            $this->facturas[$key]->saldo-= $totalCruce;
        }

        foreach ($this->facturas as $key => $facturacxp) {
            if ($facturacxp->saldo <= 0) unset($this->facturas[$key]);
        }

        return $totalAnticipos;
    }
    
    private function generarFacturaInmuebleIntereses(Facturacion $factura, $inmuebleFactura, $id_empresa, $periodo_facturacion)
    {
        $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
        $id_cuenta_ingreso_intereses = Entorno::where('nombre', 'id_cuenta_ingreso_intereses')->first()->valor;
        
        if (!$id_cuenta_intereses) return;

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($this->extractosAgrupados)) return;

        $porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso')->first()->valor;

        $valorTotalIntereses = 0;
        $detalleIntereses = [];

        foreach ($this->extractosAgrupados as $extracto) {
            $saldo = floatval($extracto->saldo);
            $this->saldoBase+= $saldo;            
            
            $inicioMes = date('Y-m', strtotime($periodo_facturacion));
            $finMes = date('Y-m-t', strtotime($periodo_facturacion));

            $valorTotal = $saldo * ($porcentaje_intereses_mora / 100);
            $valorTotal = $this->roundNumber($valorTotal);
            $valorTotalIntereses+= $valorTotal;
            
            //DEFINIR CONCEPTO DE INTERESES
            $concepto = $extracto->concepto;
            $validateConcepto = explode('INTERESES ', $concepto );
            if (count($validateConcepto) > 1) $concepto = explode(' -', $validateConcepto[1])[0];

            $data = [
                'id_factura' => $factura->id,
                'id_nit' => $factura->id_nit,
                'id_concepto_facturacion' => null,
                'id_cuenta_por_cobrar' => $id_cuenta_intereses,
                'id_cuenta_ingreso' => $id_cuenta_ingreso_intereses,
                'id_comprobante' => $id_comprobante_ventas,
                'id_centro_costos' => $inmuebleFactura ? $inmuebleFactura->id_centro_costos : CentroCostos::first()->id,
                'fecha_manual' => $inicioMes.'-01',
                'documento_referencia' => $inicioMes,
                'valor' => round($valorTotal),
                'concepto' => 'INTERESES '.$concepto.' - '.$inicioMes.'-01'.' - %'.$porcentaje_intereses_mora.' - BASE: '.number_format($saldo),
                'naturaleza_opuesta' => false,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ];
            $facturaDetalle = FacturacionDetalle::create($data);
            array_push($detalleIntereses, $data);

            FacturacionDetalle::where('concepto', $extracto->concepto)
                ->where('id_nit', $extracto->id_nit)
                ->where('fecha_manual', $inicioMes.'-01')
                ->update([
                    'saldo' => $saldo
                ]);
        }

        return [$valorTotalIntereses, $detalleIntereses];
    }

    private function inmueblesNitsQuery($empresa, $search, $nitSsearch, $request = null)
    {
        return DB::connection('max')->table('inmueble_nits AS INMN')
            ->select(
                DB::raw("CONCAT(INM.nombre, ' - ', Z.nombre) AS nombre_inmueble"),
                "INM.area AS area_inmueble",
                "INMN.id_nit",
                "INMN.tipo",
                "INMN.porcentaje_administracion",
                "INMN.valor_total",
                "CF.nombre_concepto",
                DB::raw("0 AS tipo_factura")
            )
            ->leftJoin('inmuebles AS INM', 'INMN.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS Z', 'INM.id_zona', 'Z.id')
            ->leftJoin('concepto_facturacions AS CF', 'INM.id_concepto_facturacion', 'CF.id')
            ->when(isset($search), function ($query) use($search, $nitSsearch) {
                $query->where('INM.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('Z.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('CF.nombre_concepto', 'LIKE', '%'.$search.'%');
            })
            ->when(count($nitSsearch), function ($query) use($nitSsearch) {
                $query->orWhereIn('INMN.id_nit', $nitSsearch);
            })
            ->when($request->get('factura_fisica') ? true : false, function ($query) {
                $query->orWhereIn('INMN.enviar_notificaciones_mail', true);
            })
            ;
    }

    private function cuotasMultasQuery($empresa, $search, $nitSsearch)
    {
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));

        return DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                DB::raw("CONCAT(INM.nombre, ' - ', Z.nombre) AS nombre_inmueble"),
                "INM.area AS area_inmueble",
                "CM.id_nit",
                "inmueble_nits.tipo",
                "inmueble_nits.porcentaje_administracion",
                "CM.valor_total",
                "CF.nombre_concepto",
                DB::raw("1 AS tipo_factura")
            )
            ->leftJoin('inmuebles AS INM', 'CM.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS Z', 'INM.id_zona', 'Z.id')
            ->leftJoin('inmueble_nits',function ($join) {
                $join->on('CM.id_inmueble', '=', 'inmueble_nits.id_inmueble')
                    ->on('inmueble_nits.id_nit', '=', 'CM.id_nit');
            })
            ->leftJoin('concepto_facturacions AS CF', 'CM.id_concepto_facturacion', 'CF.id')
            ->when(isset($search), function ($query) use($search) {
                $query->where('INM.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('Z.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('CF.nombre_concepto', 'LIKE', '%'.$search.'%');
            })
            ->when(count($nitSsearch), function ($query) use($nitSsearch) {
                $query->orWhereIn('CM.id_nit', $nitSsearch);
            })
            ->whereDate("CM.fecha_inicio", '<=', $inicioMes.'-01')
            ->whereDate("CM.fecha_fin", '>=', $finMes);
    }

    private function asignarNombreNit($dataFacturas)
    {
        foreach ($dataFacturas as $dataFactura) {
            $nit = Nits::find($dataFactura->id_nit);
            $dataFactura->numero_documento = $nit->numero_documento;
            $dataFactura->id_nit = $nit->nombre_completo;
        }
        return $dataFacturas;
    }

    private function nitsSearch($search)
    {
        $data = [];
        $nits = DB::connection('sam')->table('nits')->select('id')
            ->where('razon_social', 'LIKE', '%'.$search.'%')
            ->orWhere('numero_documento', 'LIKE', '%'.$search.'%')
            ->orWhere(DB::raw("(CASE
                WHEN razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                WHEN (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                ELSE NULL
            END)"), 'LIKE', '%'.$search.'%')
            ->orWhere('email', 'LIKE', '%'.$search.'%')
            ->get()->toArray();

        if (count($nits)) {
            foreach ($nits as $nit) {
                $data[] = $nit->id;
            }
        }

        return $data;        
    }

    private function totalAnticipos($id_nit, $id_empresa)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            $id_nit,
            [4,8]
        ))->actual()->get();

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $this->facturas = [];
        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $this->facturas[] = (object)[
                'documento_referencia' => $extracto->documento_referencia,
                'saldo' => floatval($extracto->saldo)
            ];
            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function dataDetalleFactura($nit, $factura, $reprocesar)
    {
        $inmuebles = InmuebleNit::where('id_nit', $nit->id_nit)
            ->with('inmueble.concepto', 'nit')
            ->get();

        $estado = $factura ? 1 : 0; //SIN PROCESAR; //PROCESADO; //CON ERRORES
        
        return [
            'id' => $factura ? $factura->id : null,
            'id_nit' => $nit->id_nit,
            'documento_nit' => $inmuebles[0]->nit->numero_documento,
            'nombre_nit' => $inmuebles[0]->nit->nombre_completo,
            'inmueble' => $inmuebles[0]->inmueble->concepto->nombre_concepto.' '.$inmuebles[0]->inmueble->nombre,
            'valor_anticipos' => $factura ? floatval($factura->valor_anticipos) : 0,
            'numero_inmuebles' => count($inmuebles),
            'valor_inmuebles' => $factura ? floatval($factura->valor_admon) : $inmuebles->sum('valor_total'),
            'total_intereses' => $factura ? floatval($factura->valor_intereses) : 0,
            'total_cuotas_multas' => $factura ? floatval($factura->valor_cuotas_multas) : 0,
            'total_factura' => $factura ? floatval($factura->valor) : 0,
            'saldo_base' => $factura ? floatval($factura->saldo_base) : 0,
            'mensajes' => $factura ? 'REPROCESANDO FACTURA' : '',
            'estado' => $reprocesar == "true" ? 0 : $estado, 
        ];
    }

    private function inmueblesNitFacturar($id_nit)
    {
        return DB::connection('max')->table('inmueble_nits')->select(
                'inmueble_nits.id_nit',
                'inmueble_nits.id_inmueble',
                'inmueble_nits.valor_total',
                'INM.nombre',
                'INM.id_concepto_facturacion',
                'INM.coeficiente',
                'CFA.nombre_concepto',
                'CFA.id_cuenta_cobrar',
                'CFA.id_cuenta_ingreso',
                'CFA.id_cuenta_interes',
                'CFA.intereses',
                'CFA.pronto_pago',
                'CFA.id_cuenta_gasto',
                'CFA.id_cuenta_anticipo',
                'CFA.porcentaje_pronto_pago',
                'ZO.id_centro_costos',
                'ZO.nombre AS nombre_zona',
                DB::raw("CONCAT(INM.nombre, '-', ZO.nombre) as documento_referencia_group")
            )
            ->leftJoin('inmuebles AS INM', 'inmueble_nits.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS ZO', 'INM.id_zona', 'ZO.id')
            ->leftJoin('concepto_facturacions AS CFA', 'INM.id_concepto_facturacion', 'CFA.id')
            ->where('inmueble_nits.id_nit', $id_nit)
            ->get()->toArray();
    }

    private function extrasNitFacturarCxC($id_nit, $periodo_facturacion)
    {
        $fecha_facturar = date('Y-m', strtotime($periodo_facturacion));
        $dbERP = Config::get('database.connections.sam.database');
        $data = CuotasMultas::with('nit', 'concepto.cuenta_ingreso.tipos_cuenta', 'inmueble.zona')
            ->where('id_nit', $id_nit)
            ->where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar)
            ->get()->toArray();

        $dataArray = [];

        
        foreach ($data as $extraCxC) {

            $tipoCuenta = $extraCxC['concepto']['cuenta_ingreso'];
            if (array_key_exists('tipos_cuenta', $tipoCuenta) && $tipoCuenta['tipos_cuenta'] && array_key_exists('id_tipo_cuenta', $tipoCuenta['tipos_cuenta'])) {
                $tipoCuenta = $extraCxC['concepto']['cuenta_ingreso']['tipos_cuenta']['id_tipo_cuenta'];
            } else {
                $tipoCuenta = 3;
            }
            
            if ($tipoCuenta != 4 && $tipoCuenta != 8) {
                array_push($dataArray, (object)[
                    'id_nit' => $extraCxC['id_nit'],
                    'id_inmueble' => $extraCxC['id_inmueble'],
                    'valor_total' => $extraCxC['valor_total'],
                    'observacion' => $extraCxC['observacion'],
                    'id_concepto_facturacion' => $extraCxC['concepto']['id'],
                    'nombre' => $extraCxC['inmueble']['nombre'],
                    'nombre_concepto' => $extraCxC['concepto']['nombre_concepto'],
                    'id_cuenta_cobrar' => $extraCxC['concepto']['id_cuenta_cobrar'],
                    'id_cuenta_ingreso' => $extraCxC['concepto']['id_cuenta_ingreso'],
                    'id_cuenta_interes' => $extraCxC['concepto']['id_cuenta_interes'],
                    'id_cuenta_gasto' => $extraCxC['concepto']['id_cuenta_gasto'],
                    'id_cuenta_anticipo' => $extraCxC['concepto']['id_cuenta_anticipo'],
                    'porcentaje_pronto_pago' => $extraCxC['concepto']['porcentaje_pronto_pago'],
                    'pronto_pago' => $extraCxC['concepto']['pronto_pago'],
                    'intereses' => $extraCxC['concepto']['intereses'],
                    'id_centro_costos' => $extraCxC['inmueble']['zona']['id_centro_costos'],
                ]);
            }
        }
        return $dataArray;
    }

    private function extrasNitFacturarCxP($id_nit, $periodo_facturacion)
    {
        $fecha_facturar = date('Y-m', strtotime($periodo_facturacion));
        $dbERP = Config::get('database.connections.sam.database');
        $data = CuotasMultas::with('nit', 'concepto.cuenta_ingreso.tipos_cuenta', 'inmueble.zona')
            ->where('id_nit', $id_nit)
            ->where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar)
            ->get()->toArray();

        $dataArray = [];

        foreach ($data as $extraCxP) {
            $tipoCuenta = $extraCxP['concepto']['cuenta_ingreso'];
            if (array_key_exists('tipos_cuenta', $tipoCuenta) && $tipoCuenta['tipos_cuenta'] && array_key_exists('id_tipo_cuenta', $tipoCuenta['tipos_cuenta'])) {
                $tipoCuenta = $extraCxP['concepto']['cuenta_ingreso']['tipos_cuenta']['id_tipo_cuenta'];
                if ($tipoCuenta == 4 || $tipoCuenta == 8) {
                    array_push($dataArray, (object)[
                        'id_nit' => $extraCxP['id_nit'],
                        'id_inmueble' => $extraCxP['id_inmueble'],
                        'valor_total' => $extraCxP['valor_total'],
                        'observacion' => $extraCxP['observacion'],
                        'id_concepto_facturacion' => $extraCxP['concepto']['id'],
                        'nombre' => $extraCxP['inmueble']['nombre'],
                        'nombre_concepto' => $extraCxP['concepto']['nombre_concepto'],
                        'id_cuenta_cobrar' => $extraCxP['concepto']['id_cuenta_cobrar'],
                        'id_cuenta_ingreso' => $extraCxP['concepto']['id_cuenta_ingreso'],
                        'id_cuenta_interes' => $extraCxP['concepto']['id_cuenta_interes'],
                        'id_cuenta_gasto' => $extraCxC['concepto']['id_cuenta_gasto'],
                        'id_cuenta_anticipo' => $extraCxC['concepto']['id_cuenta_anticipo'],
                        'porcentaje_pronto_pago' => $extraCxC['concepto']['porcentaje_pronto_pago'],
                        'pronto_pago' => $extraCxC['concepto']['pronto_pago'],
                        'intereses' => $extraCxP['concepto']['intereses'],
                        'id_centro_costos' => $extraCxP['inmueble']['zona']['id_centro_costos'],
                    ]);
                }
            }
        }

        return $dataArray;
    }

    private function eliminarFactura($id_nit, $fecha_manual)
    {
        $facturaEliminar = Facturacion::where('id_nit', $id_nit)
            ->where('fecha_manual', $fecha_manual.'-01')
            ->first();

        if ($facturaEliminar) {

            $facturaPortafolio = FacDocumentos::where('token_factura', $facturaEliminar->token_factura)->first();
            if ($facturaPortafolio) {
                $documento = DocumentosGeneral::where('relation_id', $facturaPortafolio->id)
                    ->where('relation_type', 2)
                    ->delete();
                $facturaPortafolio->delete();
            }
            FacturacionDetalle::where('id_factura', $facturaEliminar->id)->delete();
            $facturaEliminar->delete();
        }
    }

    private function carteraDocumentosQuery($request)
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
                "N.email",
                "N.email_1",
                "N.email_2",
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
            ->when($request->get('periodo'), function ($query) use($request) {
				$query->where('DG.fecha_manual', '>=', $request->get('periodo'));
			})
            ->when($request->get('id_nit'), function ($query) use($request) {
				$query->where('DG.id_nit', '=', $request->get('id_nit'));
			})
            ->when($request->get('factura_fisica'), function ($query) {
                $nits = $this->nitFacturaFisica(true);
				$query->whereIn('DG.id_nit', $nits);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery($request)
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
                "N.email",
                "N.email_1",
                "N.email_2",
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
            ->when($request->get('periodo'), function ($query) use($request) {
				$query->where('DG.fecha_manual', '<', $request->get('periodo'));
			})
            ->when($request->get('id_nit'), function ($query) use($request) {
				$query->where('DG.id_nit', '=', $request->get('id_nit'));
			})
            ->when($request->get('factura_fisica'), function ($query) {
                $nits = $this->nitFacturaFisica(true);
				$query->whereIn('DG.id_nit', $nits);
			});

        return $anterioresQuery;
    }

    private function generarCruce ($id_cuenta)
    {
        $generar = true;
        $planCuenta = PlanCuentas::with('tipos_cuenta')
            ->where('id', $id_cuenta)
            ->first();
        
        if ($planCuenta && $planCuenta->tipos_cuenta) {
            $tipoCuenta = $planCuenta->tipos_cuenta->id_tipo_cuenta;
            if ($tipoCuenta == 4 || $tipoCuenta == 8) $generar = false;
        }
        return $generar;
    }

    private function cobrarIntereses ($id_cuenta)
    {
        $existecuenta = ConceptoFacturacion::where('id_cuenta_cobrar', $id_cuenta)
            ->where('intereses', 1)
            ->first();

        return $existecuenta ? true : false;
    }

    private function roundNumber($number)
    {
        $redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
        if ($redondeo && $redondeo->valor) {
            return round($number / $redondeo->valor) * $redondeo->valor;
        }
        return $number;
    }

    private function nitFacturaFisica($fisica = false)
    {
        $nits = [];
        $inmuebleNit = InmuebleNit::select('id_nit')
            ->when($fisica, function ($query) {
                $query->where('enviar_notificaciones_fisica', 1);
            })
            ->groupBy('id_nit')
            ->get();

        foreach ($inmuebleNit as $key => $nit) {
            array_push($nits, $nit->id_nit);
        }

        return $nits;
    }

    private function calcularTotalDeuda($inmueblesFacturar, $cuotasMultasFacturarCxC, $anticiposDisponibles, $valoresIntereses)
    {
        if ($valoresIntereses) return false;

        $deudaTotal = 0;
        $descuentoParcial = Entorno::where('nombre', 'descuento_pago_parcial')->first();
        $descuentoParcial = $descuentoParcial ? $descuentoParcial->valor : 0;

        foreach ($inmueblesFacturar as $inmueble) {
            $descuento = $inmueble->pronto_pago && $inmueble->porcentaje_pronto_pago ?
                $inmueble->valor_total * ($inmueble->porcentaje_pronto_pago / 100) :
                0;

            $deudaTotal+= ($inmueble->valor_total - $descuento);
        }
        
        foreach ($cuotasMultasFacturarCxC as $multas) {
            $descuento = $multas->pronto_pago && $multas->porcentaje_pronto_pago ?
                $multas->valor_total * ($multas->porcentaje_pronto_pago / 100) :
                0;

            $deudaTotal+= ($multas->valor_total - $descuento);
        }

        if (!$descuentoParcial && $anticiposDisponibles >= $deudaTotal) return true;
        if ($descuentoParcial) return true;
        return false;
    }

    private function generarDocumentoReferencia($inmuebleFactura, $totalInmuebles, $inicioMes)
    {
        if ($this->documento_referencia_agrupado) {
            return $inmuebleFactura->documento_referencia_group;
        }
        $countItems = $totalInmuebles ? '_'.$totalInmuebles : '';
        return $inicioMes.$countItems;
    }

}