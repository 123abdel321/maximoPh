<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Imports\RecibosCajaImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;


class ImportadorRecibosController extends Controller
{
    use BegConsecutiveTrait;

    protected $id_recibo = 0;
    protected $messages = null;
    protected $fechaManual = null;
    protected $consecutivo = null;
    protected $prontoPago = false;
    protected $id_comprobante = null;
    protected $extractosAgrupados = [];
    protected $descuentoParcial = false;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
	}

	public function index ()
    {
        return view('pages.importador.recibos_caja.recibos_caja-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_recibos' => 'required|mimes:xlsx'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file_import_recibos');

            ConRecibosImport::truncate();

            $import = new RecibosCajaImport();
            $import->import($file);

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibos creados con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al actualizar precio de productos'
            ]);
        }
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $recibos = ConRecibosImport::orderBy($columnName,$columnSortOrder);

        $recibosTotals = $recibos->get();

        $recibosPaginate = $recibos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $recibosTotals->count(),
            'iTotalDisplayRecords' => $recibosTotals->count(),
            'data' => $recibosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Recibos generado con exito!'
        ]);
    }
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_recibos.xlsx',
            'message'=> 'Url generada con exito'
        ]);
    }

    public function cargar (Request $request)
    {
        $recibosImport = ConRecibosImport::where('estado', 0)
            ->get();
        
        try {
            DB::connection('max')->beginTransaction();

            $this->id_comprobante = Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor;
            $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first()->valor;
            $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
            $this->descuentoParcial = Entorno::where('nombre', 'descuento_pago_parcial')->first();
            $this->descuentoParcial = $this->descuentoParcial ? $this->descuentoParcial->valor : 0;
            $comprobante = Comprobantes::where('id', $this->id_comprobante)->first();

            if ($recibosImport->count()) {
                foreach ($recibosImport as $reciboImport) {
                    
                    $inicioMes = date('Y-m', strtotime($reciboImport->fecha_manual));
                    $finMes = date('Y-m-t', strtotime($reciboImport->fecha_manual));
                    $facturaDescuento = $this->getFacturaMes($reciboImport->id_nit, $inicioMes.'-01', $reciboImport->fecha_manual);
                    $valorDisponible = $reciboImport->pago;
                    $valorRecibido = $reciboImport->pago;
                    $valorPendiente = 0;
                    $this->fechaManual = $reciboImport->fecha_manual;
                    $this->consecutivo = $this->getNextConsecutive($comprobante->id, $this->fechaManual);

                    $recibo = $this->createFacturaRecibo($reciboImport);
                    $cecos = CentroCostos::first();

                    $documentoGeneral = new Documento(
                        $comprobante->id,
                        $recibo,
                        $this->fechaManual,
                        $this->consecutivo
                    );

                    //AGREGAR PAGOS EN CONCEPTOS
                    if ($reciboImport->id_concepto_facturacion) {
                        $conceptoFacturacion = ConceptoFacturacion::find($reciboImport->id_concepto_facturacion);

                        $cuentaIngreso = PlanCuentas::find($conceptoFacturacion->id_cuenta_ingreso);
                        $cuentaCobro = PlanCuentas::find($conceptoFacturacion->id_cuenta_cobrar);

                        //AGREGAR MOVIMIENTO COBRO
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaCobro->id,
                            "id_nit" => $cuentaCobro->exige_nit ? $recibo->id_nit : null,
                            "id_centro_costos" => $cuentaCobro->exige_centro_costos ?  $cecos->id : null,
                            "concepto" => $cuentaCobro->exige_concepto ? 'COBRO '.$conceptoFacturacion->nombre_concepto : null,
                            "documento_referencia" => $cuentaCobro->exige_documento_referencia ? $documentoReferencia : null,
                            "debito" => $reciboImport->pago,
                            "credito" => $reciboImport->pago,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaCobro->naturaleza_ingresos);

                        //AGREGAR MOVIMIENTO INGRESO
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaIngreso->id,
                            "id_nit" => $cuentaIngreso->exige_nit ? $recibo->id_nit : null,
                            "id_centro_costos" => $cuentaIngreso->exige_centro_costos ?  $cecos->id : null,
                            "concepto" => $cuentaIngreso->exige_concepto ? 'PAGO '.$conceptoFacturacion->nombre_concepto : null,
                            "documento_referencia" => $cuentaIngreso->exige_documento_referencia ? $documentoReferencia : null,
                            "debito" => $reciboImport->pago,
                            "credito" => $reciboImport->pago,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaIngreso->naturaleza_ingresos);

                    } else {//AGREGAR PAGOS EN CXP
                        $extractos = (new Extracto(
                            $reciboImport->id_nit,
                            [3,7]
                        ))->actual()->get();
    
                        $facturasPendientes = count($extractos);
    
                        //AGREGAR DEUDA
                        foreach ($extractos as $extracto) {
                            if ($valorDisponible <= 0) continue;
                            $facturasPendientes--;
                            $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
                            $valorPendiente = $extracto->saldo;
                            $saldoNuevo = $extracto->saldo - $valorDisponible;
                            $totalPago = $saldoNuevo < 0 ? $extracto->saldo : $valorDisponible;
                            $totalDescuento = $this->calcularTotalDescuento($facturaDescuento, $extracto, $totalPago, $facturasPendientes);
    
                            $documentoReferencia = $extracto->documento_referencia ? $extracto->documento_referencia : $this->consecutivo;
    
                            if ($totalDescuento) {
                                if ($totalDescuento >= $valorPendiente) {
                                    $totalPago = 0;
                                    $diferencia = $totalDescuento - $valorPendiente;
                                    $totalDescuento-= $diferencia;
                                    $valorDisponible+= $diferencia;
                                } else if ($totalDescuento + $totalPago > $valorPendiente) {
                                    $diferencia = ($valorPendiente - ($totalDescuento + $totalPago)) * -1;
                                    $totalPago-= $diferencia;
                                }
                                $idCuentaGasto = array_values($facturaDescuento->detalle)[0]->id_cuenta_gasto;
                                if ($facturaDescuento->detalle[$extracto->id_cuenta]) {
                                    $idCuentaGasto = $facturaDescuento->detalle[$extracto->id_cuenta]->id_cuenta_gasto;
                                }
                                //AGREGAR MOVIMIENTO CONTABLE
                                $cuentaGasto = PlanCuentas::find($idCuentaGasto);
    
                                $doc = new DocumentosGeneral([
                                    "id_cuenta" => $cuentaGasto->id,
                                    "id_nit" => $cuentaGasto->exige_nit ? $recibo->id_nit : null,
                                    "id_centro_costos" => $cuentaGasto->exige_centro_costos ?  $cecos->id : null,
                                    "concepto" => 'PRONTO PAGO DESCUENTO',
                                    "documento_referencia" => $cuentaGasto->exige_documento_referencia ? $documentoReferencia : null,
                                    "debito" => $totalDescuento,
                                    "credito" => $totalDescuento,
                                    "created_by" => request()->user()->id,
                                    "updated_by" => request()->user()->id
                                ]);
                                $documentoGeneral->addRow($doc, PlanCuentas::DEBITO);
    
                                //AGREGAR MOVIMIENTO CONTABLE
                                $doc = new DocumentosGeneral([
                                    "id_cuenta" => $cuentaPago->id,
                                    "id_nit" => $cuentaPago->exige_nit ? $recibo->id_nit : null,
                                    "id_centro_costos" => $cuentaPago->exige_centro_costos ?  $cecos->id : null,
                                    "concepto" => $cuentaPago->exige_concepto ? 'PRONTO PAGO IMPORTADO DESDE RECIBOS' : null,
                                    "documento_referencia" => $cuentaPago->exige_documento_referencia ? $documentoReferencia : null,
                                    "debito" => $totalDescuento,
                                    "credito" => $totalDescuento,
                                    "created_by" => request()->user()->id,
                                    "updated_by" => request()->user()->id
                                ]);
                                $documentoGeneral->addRow($doc, $cuentaPago->naturaleza_ingresos);
    
                                Facturacion::where($facturaDescuento->id_factura)
                                    ->update(['pronto_pago' => 1]);
                            }
                            
                            if ($totalPago) {
                                ConReciboDetalles::create([
                                    'id_recibo' => $recibo->id,
                                    'id_cuenta' => $cuentaPago->id,
                                    'id_nit' => $recibo->id_nit,
                                    'fecha_manual' => $recibo->fecha_manual,
                                    'documento_referencia' => $extracto->documento_referencia,
                                    'consecutivo' => $recibo->consecutivo,
                                    'concepto' => 'VALOR IMPORTADO DESDE RECIBOS',
                                    'total_factura' => 0,
                                    'total_abono' => $saldoNuevo < 0 ? $extracto->saldo : $valorDisponible,
                                    'total_saldo' => $extracto->saldo,
                                    'nuevo_saldo' => $saldoNuevo < 0 ? 0 : $saldoNuevo,
                                    'total_anticipo' => 0,
                                    'created_by' => request()->user()->id,
                                    'updated_by' => request()->user()->id
                                ]);
        
                                //AGREGAR MOVIMIENTO CONTABLE
                                $doc = new DocumentosGeneral([
                                    "id_cuenta" => $cuentaPago->id,
                                    "id_nit" => $cuentaPago->exige_nit ? $recibo->id_nit : null,
                                    "id_centro_costos" => $cuentaPago->exige_centro_costos ?  $cecos->id : null,
                                    "concepto" => $cuentaPago->exige_concepto ? 'IMPORTADO DESDE RECIBOS' : null,
                                    "documento_referencia" => $cuentaPago->exige_documento_referencia ? $documentoReferencia : null,
                                    "debito" => $totalPago,
                                    "credito" => $totalPago,
                                    "created_by" => request()->user()->id,
                                    "updated_by" => request()->user()->id
                                ]);
                                $documentoGeneral->addRow($doc, $cuentaPago->naturaleza_ingresos);
                            }
    
                            $valorDisponible-= $totalPago;
                        }
                        //AGREGAR ANTICIPO
                        if ($valorDisponible > 0) {
                            $cuentaAnticipo = PlanCuentas::find($id_cuenta_anticipos);
    
                            ConReciboDetalles::create([
                                'id_recibo' => $recibo->id,
                                'id_cuenta' => $cuentaAnticipo->id,
                                'id_nit' => $recibo->id_nit,
                                'fecha_manual' => $recibo->fecha_manual,
                                'documento_referencia' => $recibo->consecutivo,
                                'consecutivo' => $recibo->consecutivo,
                                'concepto' => 'ANTICIPO IMPORTADO DESDE RECIBOS',
                                'total_factura' => 0,
                                'total_abono' => 0,
                                'total_saldo' => 0,
                                'nuevo_saldo' => 0,
                                'total_anticipo' => $valorDisponible,
                                'created_by' => request()->user()->id,
                                'updated_by' => request()->user()->id
                            ]);
    
                            //AGREGAR MOVIMIENTO CONTABLE
                            $doc = new DocumentosGeneral([
                                "id_cuenta" => $cuentaAnticipo->id,
                                "id_nit" => $cuentaAnticipo->exige_nit ? $recibo->id_nit : null,
                                "id_centro_costos" => $cuentaAnticipo->exige_centro_costos ? $cecos->id : null,
                                "concepto" => $cuentaAnticipo->exige_concepto ? 'ANTICIPO IMPORTADO DESDE RECIBOS' : null,
                                "documento_referencia" => $cuentaAnticipo->exige_documento_referencia ? $documentoReferencia : null,
                                "debito" => $valorDisponible,
                                "credito" => $valorDisponible,
                                "created_by" => request()->user()->id,
                                "updated_by" => request()->user()->id
                            ]);
                            $documentoGeneral->addRow($doc, $cuentaAnticipo->naturaleza_ingresos);
                        }
                        //GREGAR PAGO
                        $formaPago = FacFormasPago::where('id_cuenta', $id_cuenta_ingreso)
                            ->with('cuenta.tipos_cuenta')
                            ->first();
    
                        $pagoRecibo = ConReciboPagos::create([
                            'id_recibo' => $recibo->id,
                            'id_forma_pago' => $formaPago->id,
                            'valor' => $reciboImport->pago,
                            'saldo' => 0,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
    
                        $doc = new DocumentosGeneral([
                            'id_cuenta' => $formaPago->cuenta->id,
                            'id_nit' => $formaPago->cuenta->exige_nit ? $recibo->id_nit : null,
                            'id_centro_costos' => null,
                            'concepto' => $formaPago->cuenta->exige_concepto ? 'PAGO IMPORTADO DESDE RECIBOS' : null,
                            'documento_referencia' => $documentoReferencia,
                            'debito' => $valorRecibido,
                            'credito' => $valorRecibido,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
            
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                    }


                    $this->updateConsecutivo($this->id_comprobante, $this->consecutivo);

                    if (!$documentoGeneral->save()) {

                        DB::connection('max')->rollback();
                        return response()->json([
                            'success'=>	false,
                            'data' => [],
                            'message'=> $documentoGeneral->getErrors()
                        ], 422);
                    }
                }
            }
            
            ConRecibosImport::whereIn('estado', [0])->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibos creados con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function totales (Request $request)
    {
        $recibosErrores = ConRecibosImport::where('estado', 1)->count();
        $recibosBuenos = ConRecibosImport::where('estado', 0)->count();
        $recibosPagos = ConRecibosImport::where('estado', 0)->sum('pago');
        $recibosAnticipos = ConRecibosImport::where('estado', 0)->sum('anticipos');

        $data = [
            'errores' => $recibosErrores,
            'buenos' => $recibosBuenos,
            'pagos' => $recibosPagos,
            'anticipos' => $recibosAnticipos
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }

    private function calcularTotalDescuento($facturaDescuento, $extracto, $totalPago, $facturasPendientes)
    {
        if ($facturaDescuento && !$facturaDescuento->has_pronto_pago && $facturasPendientes == 0) {
            if ($totalPago + $facturaDescuento->descuento >= $extracto->saldo) {
                return $facturaDescuento->descuento;
            }
        }
        return 0;
    }

    private function getFacturaMes($id_nit, $inicioMes, $fechaManual)
    {
        $fechaActual = Carbon::now()->format("Y-m-d");
        $fechaManual = Carbon::parse($fechaManual)->format("Y-m-d");

        $facturas = DB::connection('max')->select("SELECT
                FA.id AS id_factura,
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100)
                        ELSE 0
                END AS descuento,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) - (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                        ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FD.fecha_manual = '{$inicioMes}'
                AND CF.porcentaje_pronto_pago > 0
                AND FA.pronto_pago IS NULL
                AND CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                
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

        return $data;
    }

    private function createFacturaRecibo($reciboImport)
    {
        $recibo = ConRecibos::create([
            'id_nit' => $reciboImport->id_nit,
            'id_comprobante' => $this->id_comprobante,
            'fecha_manual' => $this->fechaManual,
            'consecutivo' => $this->consecutivo,
            'total_abono' => $reciboImport->pago,
            'total_anticipo' => $reciboImport->total_anticipo ? $reciboImport->total_anticipo : 0,
            'observacion' => 'CARGADO DESDE IMPORTADOR',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);
        return $recibo;
    }

}