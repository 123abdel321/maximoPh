<?php

namespace App\Jobs;

use DB;
use Error;
use Exception;
use Carbon\Carbon;
use App\Helpers\helpers;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\Comprobantes;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;

class ProcessImportarRecibos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BegConsecutiveTrait;

    public $id_comprobante = null;
    public $descuentoParcial = null;
    public $redondeo = null;
    public $empresa = null;
    public $user_id = null;

    public function __construct($empresa, $user_id)
    {
        $this->empresa = $empresa;
        $this->user_id = $user_id;
    }

    public function handle()
    {
        
        try {            
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $this->id_comprobante = Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor;
            $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first()->valor;
            $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
            $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
            $this->descuentoParcial = Entorno::where('nombre', 'descuento_pago_parcial')->first();
            $this->descuentoParcial = $this->descuentoParcial ? $this->descuentoParcial->valor : 0;
            $this->redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
            $this->redondeo = $this->redondeo ? $this->redondeo->valor : 0;
            $comprobante = Comprobantes::where('id', $this->id_comprobante)->first();

            if (!$id_cuenta_ingreso) {
                throw new Error("La cuenta de ingreso id: $id_cuenta_ingreso no existe.");
            }

            //GREGAR PAGO
            $formaPago = FacFormasPago::where('id_cuenta', $id_cuenta_ingreso)
                ->with('cuenta.tipos_cuenta')
                ->first();

            if (!$formaPago) {
                $cuenta = PlanCuentas::where('id', $id_cuenta_ingreso)->first();
                throw new Error("La cuenta de ingreso $cuenta->cuenta - $cuenta->nombre, no tiene forma de pago asociada.");
            }

            $ordenFacturacion = ConceptoFacturacion::select('id_cuenta_cobrar')
                ->orderBy('orden', 'ASC')
                ->pluck('id_cuenta_cobrar')
                ->toArray();

            array_unshift($ordenFacturacion, $id_cuenta_intereses);
            $ordenFacturacion = array_flip($ordenFacturacion);

            $recibosImport = ConRecibosImport::where('estado', 0)
                ->get();
            
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
                        $extractos = (new Extracto(
                            $reciboImport->id_nit,
                            [3,7]
                        ))->actual()->get();

                        $countTotal = count($extractos) ? '-'.count($extractos) : '';
                        $documentoReferencia = date('Ymd', strtotime($reciboImport->fecha_manual)).$countTotal;

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
                            "created_by" => $this->user_id,
                            "updated_by" => $this->user_id
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
                            "created_by" => $this->user_id,
                            "updated_by" => $this->user_id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaIngreso->naturaleza_ingresos);

                    } else {//AGREGAR PAGOS EN CXP

                        $inicioMes =  Carbon::parse($this->fechaManual)->format('Y-m');
                        $inicioMes = $inicioMes.'-01';
                        $inicioMesMenosDia = Carbon::parse($inicioMes)->subDay()->format('Y-m-d');

                        $sandoPendiente = (new Extracto(
                            $reciboImport->id_nit,
                            [3,7],
                            null,
                            $inicioMesMenosDia
                        ))->completo()->first();
                        
                        $extractos = (new Extracto(
                            $reciboImport->id_nit,
                            [3,7],
                            null,
                            $finMes
                        ))->actual()->get();

                        $extractos = $extractos->sortBy(function ($item) use ($ordenFacturacion) {
                            return $ordenFacturacion[$item->id_cuenta] ?? 9999;
                        })->values();
                        
                        $realizarDescuento = false;
                        
                        $totalDescuentosArray = [];
                        $deudaTotal = $this->sumarDeudaTotal($extractos);
                        $totalDescuento = $facturaDescuento ? $facturaDescuento->descuento : 0;
                        $anticiposDisponibles = $anticiposNit = $this->totalAnticipos($reciboImport->id_nit);
                        
                        if ($facturaDescuento && !$sandoPendiente && ($totalDescuento + $anticiposNit + $valorDisponible) >= $deudaTotal) {
                            $realizarDescuento = true;
                            Facturacion::where('id', $facturaDescuento->id_factura)
                                ->update(['pronto_pago' => 1]);
                        }

                        //AGREGAR DESCUENTOS
                        if ($realizarDescuento) {
                            $cuentaAnticipo = PlanCuentas::find($id_cuenta_anticipos);
                            
                            foreach ($extractos as $extracto) {
                                if (array_key_exists($extracto->id_cuenta, $facturaDescuento->detalle)) {

                                    $conceptoDescuento = $facturaDescuento->detalle[$extracto->id_cuenta];
                                    $valorDescuento = $conceptoDescuento->descuento;
                                    $cuentaGasto = PlanCuentas::find($conceptoDescuento->id_cuenta_gasto);

                                    //AGREGAR MOVIMIENTO GASTO
                                    $doc = new DocumentosGeneral([
                                        "id_cuenta" => $cuentaGasto->id,
                                        "id_nit" => $cuentaGasto->exige_nit ? $recibo->id_nit : null,
                                        "id_centro_costos" => $cuentaGasto->exige_centro_costos ?  $cecos->id : null,
                                        "concepto" => 'PRONTO PAGO '.$conceptoDescuento->porcentaje_pronto_pago.'% BASE '.number_format($conceptoDescuento->subtotal).' '.$conceptoDescuento->nombre_concepto,
                                        "documento_referencia" => $cuentaGasto->exige_documento_referencia ? $extracto->documento_referencia : null,
                                        "debito" => $valorDescuento,
                                        "credito" => $valorDescuento,
                                        "created_by" => $this->user_id,
                                        "updated_by" => $this->user_id
                                    ]);
                                    
                                    $documentoGeneral->addRow($doc, $cuentaGasto->naturaleza_egresos);
                                }
                            }
                        }

                        //AGREGAR DEUDA
                        foreach ($extractos as $extracto) {
                            if ($valorDisponible <= 0) continue;
                            
                            $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
                            $valorPendiente = $extracto->saldo;
                            $valorDescuento = 0;
                            $totalAnticipar = 0;
                            
                            if ($realizarDescuento && array_key_exists($extracto->id_cuenta, $facturaDescuento->detalle)) {

                                $conceptoDescuento = $facturaDescuento->detalle[$extracto->id_cuenta];
                                $valorPendiente-= $conceptoDescuento->descuento;

                                //AGREGAR MOVIMIENTO GASTO
                                $doc = new DocumentosGeneral([
                                    "id_cuenta" => $cuentaPago->id,
                                    "id_nit" => $cuentaPago->exige_nit ? $recibo->id_nit : null,
                                    "id_centro_costos" => $cuentaPago->exige_centro_costos ?  $cecos->id : null,
                                    "concepto" => 'PRONTO PAGO '.$conceptoDescuento->porcentaje_pronto_pago.'% BASE '.number_format($conceptoDescuento->subtotal).' '.$conceptoDescuento->nombre_concepto,
                                    "documento_referencia" => $cuentaPago->exige_documento_referencia ? $extracto->documento_referencia : null,
                                    "debito" => $conceptoDescuento->descuento,
                                    "credito" => $conceptoDescuento->descuento,
                                    "created_by" => $this->user_id,
                                    "updated_by" => $this->user_id
                                ]);

                                $documentoGeneral->addRow($doc, $cuentaPago->naturaleza_ingresos);
                            }

                            if ($anticiposDisponibles > 0) {
                                [$anticiposDisponibles, $valorPendiente, $totalAnticipar] = $this->cruzarAnticipos($extracto, $anticiposDisponibles, $documentoGeneral, $cecos, $valorPendiente);
                            }

                            $validarPago = $valorDisponible - $valorPendiente - $valorDescuento;
                            $valorPago = $valorDisponible - $valorPendiente - $valorDescuento > 0 ? $valorPendiente : $valorDisponible;
                            $documentoReferencia = $extracto->documento_referencia ? $extracto->documento_referencia : $this->consecutivo;

                            if ($valorPago) {
                                ConReciboDetalles::create([
                                    'id_recibo' => $recibo->id,
                                    'id_cuenta' => $cuentaPago->id,
                                    'id_nit' => $recibo->id_nit,
                                    'fecha_manual' => $recibo->fecha_manual,
                                    'documento_referencia' => $extracto->documento_referencia,
                                    'consecutivo' => $recibo->consecutivo,
                                    'concepto' => 'VALOR IMPORTADO DESDE RECIBOS '.number_format($valorPago),
                                    'total_factura' => 0,
                                    'total_abono' => $valorPago,
                                    'total_saldo' => $extracto->saldo,
                                    'nuevo_saldo' => $extracto->saldo - ($valorPago + $valorDescuento + $totalAnticipar),
                                    'total_anticipo' => 0,
                                    'created_by' => $this->user_id,
                                    'updated_by' => $this->user_id
                                ]);
                                
                                //AGREGAR MOVIMIENTO CONTABLE
                                $doc = new DocumentosGeneral([
                                    "id_cuenta" => $cuentaPago->id,
                                    "id_nit" => $cuentaPago->exige_nit ? $recibo->id_nit : null,
                                    "id_centro_costos" => $cuentaPago->exige_centro_costos ?  $cecos->id : null,
                                    "concepto" => $cuentaPago->exige_concepto ? 'VALOR IMPORTADO DESDE RECIBOS '.number_format($valorPago) : null,
                                    "documento_referencia" => $cuentaPago->exige_documento_referencia ? $documentoReferencia : null,
                                    "debito" => $valorPago,
                                    "credito" => $valorPago,
                                    "created_by" => $this->user_id,
                                    "updated_by" => $this->user_id
                                ]);
                                $documentoGeneral->addRow($doc, $cuentaPago->naturaleza_ingresos);
                            }
    
                            $valorDisponible-= ($valorPago - ($totalAnticipar));
                        }

                        //AGREGAR ANTICIPO
                        if ($valorDisponible > 0) {
                            $documentoReferencia = date('Ymd', strtotime($reciboImport->fecha_manual));
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
                                'created_by' => $this->user_id,
                                'updated_by' => $this->user_id
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
                                "created_by" => $this->user_id,
                                "updated_by" => $this->user_id
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
                            'created_by' => $this->user_id,
                            'updated_by' => $this->user_id
                        ]);
                        
                        $doc = new DocumentosGeneral([
                            'id_cuenta' => $formaPago->cuenta->id,
                            'id_nit' => $formaPago->cuenta->exige_nit ? $recibo->id_nit : null,
                            'id_centro_costos' => null,
                            'concepto' => $formaPago->cuenta->exige_concepto ? 'PAGO IMPORTADO DESDE RECIBOS' : null,
                            'documento_referencia' => $documentoReferencia,
                            'debito' => $valorRecibido,
                            'credito' => $valorRecibido,
                            'created_by' => $this->user_id,
                            'updated_by' => $this->user_id
                        ]);
            
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                    }
                    $this->updateConsecutivo($this->id_comprobante, $this->consecutivo);
                    
                    if (!$documentoGeneral->save()) {
                        throw new Error($documentoGeneral->getErrors());
                    }
                }
            }
            ConRecibosImport::whereIn('estado', [0])->delete();

		} catch (Exception $exception) {
            $message = $exception->getMessage();
            $line = $exception->getLine();
            throw new Error("Mensaje: $message; Line: $line");
		}
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
                CF.nombre_concepto,
                CF.porcentaje_pronto_pago,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
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
                AND FA.id IS NOT NULL
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

        $data->descuento = $this->roundNumber($data->descuento);

        return $data;
    }

    private function totalAnticipos($id_nit)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            $id_nit,
            [4,8],
            null,
            $this->fechaManual
        ))->actual()->get();

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $this->facturasAnticipos = [];
        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $this->facturasAnticipos[] = (object)[
                'documento_referencia' => $extracto->documento_referencia,
                'id_cuenta' => $extracto->id_cuenta,
                'naturaleza_ingresos' => $extracto->naturaleza_ingresos,
                'naturaleza_egresos' => $extracto->naturaleza_egresos,
                'naturaleza_compras' => $extracto->naturaleza_compras,
                'naturaleza_ventas' => $extracto->naturaleza_ventas,
                'naturaleza_cuenta' => $extracto->naturaleza_cuenta,
                'exige_nit' => $extracto->exige_nit,
                'exige_documento_referencia' => $extracto->exige_documento_referencia,
                'exige_concepto' => $extracto->exige_concepto,
                'exige_centro_costos' => $extracto->exige_centro_costos,
                'saldo' => floatval($extracto->saldo)
            ];

            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function cruzarAnticipos($extracto, $anticiposDisponibles, $documentoGeneral, $cecos, $valorPendiente)
    {
        foreach ($this->facturasAnticipos as $key => $anticipo) {
            if ($anticiposDisponibles <= 0) continue;

            $totalAnticipar = 0;
            if ($anticiposDisponibles >= $valorPendiente) {
                $totalAnticipar = $valorPendiente;
                $anticiposDisponibles-= $valorPendiente;
            } else {
                $totalAnticipar = $anticiposDisponibles;
                $anticiposDisponibles = 0;
            }

            $doc = new DocumentosGeneral([
                "id_cuenta" => $anticipo->id_cuenta,
                "id_nit" => $anticipo->exige_nit ? $extracto->id_nit : null,
                "id_centro_costos" => $anticipo->exige_centro_costos ? $cecos->id : null,
                "concepto" => 'CRUCE ANTICIPOS '.$extracto->concepto,
                "documento_referencia" => $anticipo->exige_documento_referencia ? $anticipo->documento_referencia : null,
                "debito" => $totalAnticipar,
                "credito" => $totalAnticipar,
                "created_by" => $this->user_id,
                "updated_by" => $this->user_id
            ]);
            $documentoGeneral->addRow($doc, PlanCuentas::DEBITO);

            if ($anticipo->saldo <= 0) unset($this->facturasAnticipos[$key]);
        }
        return [$anticiposDisponibles, $valorPendiente, $totalAnticipar]; 
    }

    private function sumarDeudaTotal($extractos)
    {
        $totalDeuda = 0;
        foreach ($extractos as $extracto) {
            $totalDeuda+= $extracto->saldo;
        }
        return $totalDeuda;
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
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);
        return $recibo;
    }


    private function isAnticiposDocumentoRefe($idNit)
    {
        $anticipoCuenta = (new Extracto(
            $idNit,
            [4,8]
        ))->anticiposDiscriminados()->get();

        return $anticipoCuenta;
    }

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }

	public function failed($exception)
	{
		Log::error('ProcessImportarRecibos', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
