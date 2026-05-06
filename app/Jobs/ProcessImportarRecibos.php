<?php

namespace App\Jobs;

use DB;
use Error;
use Exception;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
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

    protected $empresa;
    protected $user_id;
    protected $id_comprobante;
    protected $id_cuenta_ingreso;
    protected $id_cuenta_anticipos;
    protected $id_cuenta_intereses;
    protected $descuentoParcial;
    protected $redondeoProntoPago;
    protected $redondeoIntereses;
    protected $comprobante;
    protected $cecos;
    protected $formaPago;
    protected $fechaManual;
    protected $facturasAnticipos = [];

    public function __construct($empresa, $user_id)
    {
        $this->empresa = $empresa;
        $this->user_id = $user_id;
    }

    public function handle()
    {
        try {
            $this->setupDatabaseConnections();
            $this->initializeConfiguration();
            $this->validateConfiguration();

            DB::connection('max')->beginTransaction();
            DB::connection('sam')->beginTransaction();

            $recibosImport = ConRecibosImport::where('estado', 0)->cursor();
            foreach ($recibosImport as $reciboImport) {
                $this->procesarReciboImport($reciboImport);
            }

            ConRecibosImport::whereIn('estado', [0])->delete();

            DB::connection('max')->commit();
            DB::connection('sam')->commit();

            $this->sendSuccessEvent('Recibos importados con exito!');
        } catch (\Throwable $exception) {
            $this->rollbackAndSendError($exception);
        }
    }

    /* -------------------- CONFIGURACIÓN Y VALIDACIÓN -------------------- */

    private function setupDatabaseConnections()
    {
        DB::connection('max')->disableQueryLog();
        DB::connection('sam')->disableQueryLog();

        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);
    }

    private function initializeConfiguration()
    {
        $this->id_comprobante = (int) optional(Entorno::where('nombre', 'id_comprobante_recibos_caja')->first())->valor;
        $this->id_cuenta_ingreso = (int) optional(Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first())->valor;
        $this->id_cuenta_anticipos = (int) optional(Entorno::where('nombre', 'id_cuenta_anticipos')->first())->valor;
        $this->id_cuenta_intereses = (int) optional(Entorno::where('nombre', 'id_cuenta_intereses')->first())->valor;

        $descuentoParcial = Entorno::where('nombre', 'descuento_pago_parcial')->first();
        $this->descuentoParcial = $descuentoParcial ? (float) $descuentoParcial->valor : 0;

        $redondeoPronto = Entorno::where('nombre', 'redondeo_pronto_pago')->first();
        $this->redondeoProntoPago = $redondeoPronto ? (float) $redondeoPronto->valor : 0;

        $redondeoInteres = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeoIntereses = $redondeoInteres ? (float) $redondeoInteres->valor : 0;

        $this->comprobante = Comprobantes::where('id', $this->id_comprobante)->first();
        $this->cecos = CentroCostos::first();

        $this->formaPago = FacFormasPago::where('id_cuenta', $this->id_cuenta_ingreso)
            ->with('cuenta.tipos_cuenta')
            ->first();
    }

    private function validateConfiguration()
    {
        if (!$this->id_cuenta_ingreso) {
            throw new Exception("La cuenta de ingreso no está configurada.");
        }
        if (!$this->formaPago) {
            $cuenta = PlanCuentas::where('id', $this->id_cuenta_ingreso)->first();
            throw new Exception("La cuenta de ingreso {$cuenta->cuenta} - {$cuenta->nombre} no tiene forma de pago asociada.");
        }
        if (!$this->comprobante) {
            throw new Exception("El comprobante de recibos de caja no existe.");
        }
    }

    /* -------------------- PROCESAMIENTO DE CADA RECIBO -------------------- */

    private function procesarReciboImport($reciboImport)
    {
        if (!$reciboImport->id_nit) {
            return;
        }

        $this->fechaManual = $reciboImport->fecha_manual;
        $consecutivo = $this->getNextConsecutive($this->comprobante->id, $this->fechaManual);

        // Crear cabecera del recibo
        $recibo = ConRecibos::create([
            'id_nit' => $reciboImport->id_nit,
            'id_comprobante' => $this->id_comprobante,
            'fecha_manual' => $this->fechaManual,
            'consecutivo' => $consecutivo,
            'total_abono' => $reciboImport->pago,
            'total_anticipo' => $reciboImport->anticipos ?? 0,
            'observacion' => 'CARGADO DESDE IMPORTADOR',
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);

        $documentoGeneral = new Documento(
            $this->comprobante->id,
            $recibo,
            $this->fechaManual,
            $consecutivo,
            false
        );

        if ($reciboImport->id_concepto_facturacion) {
            $this->procesarConceptoFacturacion($reciboImport, $recibo, $documentoGeneral);
        } else {
            $this->procesarPagosCxp($reciboImport, $recibo, $documentoGeneral);
        }


        $this->agregarPagoFormaPago($reciboImport, $recibo, $documentoGeneral);
        $this->updateConsecutivo($this->id_comprobante, $consecutivo);

        if (!$documentoGeneral->save()) {
            throw new Exception(json_encode($documentoGeneral->getErrors()));
        }
    }

    /* -------------------- PROCESAR CONCEPTO FACTURACIÓN -------------------- */

    private function procesarConceptoFacturacion($reciboImport, $recibo, Documento $documentoGeneral)
    {
        $conceptoFacturacion = ConceptoFacturacion::with('cuenta_cobrar', 'cuenta_ingreso')
            ->where('id', $reciboImport->id_concepto_facturacion)
            ->first();

        if (!$conceptoFacturacion) {
            throw new Exception("Concepto de facturación no encontrado.");
        }

        $extractos = (new Extracto($reciboImport->id_nit, [3,7]))->actual()->get();
        $countTotal = count($extractos) ? '-' . count($extractos) : '';
        $documentoReferencia = date('Ymd', strtotime($reciboImport->fecha_manual)) . $countTotal;

        $cuentaCobro = $conceptoFacturacion->cuenta_cobrar;
        $cuentaIngreso = $conceptoFacturacion->cuenta_ingreso;

        // Movimiento cobro (cartera)
        $this->agregarMovimiento($documentoGeneral, $cuentaCobro, $recibo->id_nit,
            'COBRO ' . $conceptoFacturacion->nombre_concepto, $documentoReferencia, $reciboImport->pago,
            $cuentaCobro->naturaleza_ingresos);

        // Movimiento ingreso (caja/banco)
        $this->agregarMovimiento($documentoGeneral, $cuentaIngreso, $recibo->id_nit,
            'PAGO ' . $conceptoFacturacion->nombre_concepto, $documentoReferencia, $reciboImport->pago,
            $cuentaIngreso->naturaleza_ingresos);
    }

    /* -------------------- PROCESAR PAGOS CXP (SIN CONCEPTO) -------------------- */

    private function procesarPagosCxp($reciboImport, $recibo, Documento $documentoGeneral)
    {
        $inicioMes = Carbon::parse($this->fechaManual)->format('Y-m-01');
        $inicioMesMenosDia = Carbon::parse($inicioMes)->subDay()->format('Y-m-d');
        $finMes = Carbon::parse($this->fechaManual)->format('Y-m-t');

        $facturaDescuento = $this->getFacturaMes($reciboImport->id_nit, $inicioMes, $this->fechaManual);
        $totalDescuentoDisponible = $facturaDescuento ? $facturaDescuento->descuento : 0;
        $anticiposDisponibles = $this->totalAnticipos($reciboImport->id_nit);
        $valorDisponible = (float) $reciboImport->pago;
        $valorRecibido = (float) $reciboImport->pago;  // guardamos el original
        $deudaTotal = $facturaDescuento ? $facturaDescuento->saldo_pendiente : 0; // si hay factura con pronto pago, la deuda total es lo que queda pendiente de esa factura, no de todo el extracto

        $extractos = (new Extracto($reciboImport->id_nit, [3,7], null, $finMes))->actual()->get();
        $extractosMapeados = [];
        foreach ($extractos as $extracto) {
            $extractosMapeados[] = $extracto;
        }

        $realizarDescuento = $facturaDescuento && ($totalDescuentoDisponible + $anticiposDisponibles + $valorDisponible) >= $deudaTotal;
        
        if ($realizarDescuento) {
            // Aplicar descuento al primer concepto que coincida (igual que original)
            $cuentaAnticipo = PlanCuentas::find($this->id_cuenta_anticipos);
            foreach ($extractos as $extracto) {
                if (array_key_exists($extracto->documento_referencia, $facturaDescuento->detalle) && !$facturaDescuento->usado) {
                    $facturaDescuento->usado = true;
                    $conceptoDescuento = $facturaDescuento->detalle[$extracto->documento_referencia];
                    $facturaDescuento->detalle[$extracto->documento_referencia]->usado = true;

                    $valorDescuento = $facturaDescuento->descuento ?: 0;
                    $cuentaGasto = PlanCuentas::find($conceptoDescuento->id_cuenta_gasto);
                    if ($cuentaGasto) {
                        $this->agregarMovimiento($documentoGeneral, $cuentaGasto, $reciboImport->id_nit,
                            'PRONTO PAGO ' . $conceptoDescuento->porcentaje_pronto_pago . '% BASE ' . number_format($facturaDescuento->subtotal) . ' IMPORTADOS DESDE RECIBOS',
                            $extracto->documento_referencia, $valorDescuento, $cuentaGasto->naturaleza_egresos);
                    }
                    // No se resta aún del totalDescuentoDisponible porque eso se hace después
                }
            }
        } else {
            $totalDescuentoDisponible = 0;
        }

        // Aplicar el descuento restante a todos los extractos (cruce)
        foreach ($extractosMapeados as $extracto) {
            if ($totalDescuentoDisponible <= 0) continue;
            $extractoSaldo = $extracto->saldo;
            $valorDescuento = $totalDescuentoDisponible > $extractoSaldo ? $extractoSaldo : $totalDescuentoDisponible;
            $totalDescuentoDisponible -= $valorDescuento;

            $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
            if ($cuentaPago) {
                $this->agregarMovimiento($documentoGeneral, $cuentaPago, $reciboImport->id_nit,
                    'CRUCE PRONTO PAGO ' . ($extracto->concepto ?? ''),
                    $extracto->documento_referencia, $valorDescuento, $cuentaPago->naturaleza_ingresos);
            }
            $extracto->saldo -= $valorDescuento;
        }

        $valorDisponible += $totalDescuentoDisponible;  // sumamos lo que sobró del descuento (si no se usó todo)

        // Ahora aplicar pagos y anticipos
        $valorRestante = $this->aplicarPagosYAnticiposOriginal($reciboImport, $recibo, $documentoGeneral, $extractosMapeados, $valorDisponible, $anticiposDisponibles);

        if ($valorRestante > 0) {
            $this->crearAnticipo($reciboImport, $recibo, $documentoGeneral, $valorRestante);
        }
    }

    private function aplicarPagosYAnticiposOriginal($reciboImport, $recibo, Documento $documentoGeneral, &$extractos, $valorDisponible, $anticiposDisponibles)
    {
        $valorRestante = $valorDisponible;
        $anticipoRestante = $anticiposDisponibles;
        $totalAnticipar = 0;

        foreach ($extractos as $extracto) {
            if ($valorRestante <= 0) break;

            $valorPendiente = $extracto->saldo;

            // Cruzar anticipos
            if ($anticipoRestante > 0) {
                foreach ($this->facturasAnticipos as $key => $anticipo) {
                    if ($anticipoRestante <= 0) break;
                    $totalAnticipar = 0;
                    if ($anticipoRestante >= $valorPendiente) {
                        $totalAnticipar = $valorPendiente;
                        $anticipoRestante -= $valorPendiente;
                    } else {
                        $totalAnticipar = $anticipoRestante;
                        $anticipoRestante = 0;
                    }

                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $anticipo->id_cuenta,
                        "id_nit" => $anticipo->exige_nit ? $recibo->id_nit : null,
                        "id_centro_costos" => $anticipo->exige_centro_costos ? ($this->cecos->id ?? null) : null,
                        "concepto" => 'CRUCE ANTICIPOS ' . ($extracto->concepto ?? ''),
                        "documento_referencia" => $anticipo->exige_documento_referencia ? $anticipo->documento_referencia : null,
                        "debito" => $totalAnticipar,
                        "credito" => $totalAnticipar,
                        "created_by" => $this->user_id,
                        "updated_by" => $this->user_id
                    ]);
                    $documentoGeneral->addRow($doc, PlanCuentas::DEBITO);

                    if ($anticipo->saldo <= 0) unset($this->facturasAnticipos[$key]);
                }
            }

            $valorPago = $valorRestante > $valorPendiente ? $valorPendiente : $valorRestante;
            $documentoReferencia = $extracto->documento_referencia ?: $recibo->consecutivo;

            if ($valorPago) {
                $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $extracto->id_cuenta,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $extracto->documento_referencia,
                    'consecutivo' => $recibo->consecutivo,
                    'concepto' => 'PAGADO DESDE IMPORTADOR DE RECIBOS ' . number_format($valorPago),
                    'total_factura' => 0,
                    'total_abono' => $valorPago,
                    'total_saldo' => $extracto->saldo,
                    'nuevo_saldo' => $extracto->saldo - ($valorPago + $totalAnticipar),
                    'total_anticipo' => 0,
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id
                ]);

                $this->agregarMovimiento($documentoGeneral, $cuentaPago, $recibo->id_nit,
                    'PAGADO DESDE IMPORTADOR DE RECIBOS ' . number_format($valorPago),
                    $documentoReferencia, $valorPago, $cuentaPago->naturaleza_ingresos);
            }

            $valorRestante -= ($valorPago - $totalAnticipar);
        }

        return $valorRestante;
    }

    private function cruzarAnticipo($extracto, Documento $documentoGeneral, $monto, $id_nit)
    {
        // Buscar el primer anticipo disponible
        foreach ($this->facturasAnticipos as $key => $anticipo) {
            if ($anticipo->saldo <= 0) {
                unset($this->facturasAnticipos[$key]);
                continue;
            }

            $usar = min($monto, $anticipo->saldo);
            $cuentaAnticipo = PlanCuentas::find($anticipo->id_cuenta);
            if ($cuentaAnticipo) {
                $this->agregarMovimiento($documentoGeneral, $cuentaAnticipo, $id_nit,
                    'CRUCE ANTICIPOS ' . ($extracto->concepto ?? ''),
                    $anticipo->documento_referencia, $usar, PlanCuentas::DEBITO);
                $anticipo->saldo -= $usar;
                $monto -= $usar;
            }
            if ($monto <= 0) break;
        }
    }

    private function crearDetallePago($recibo, $extracto, $monto)
    {
        ConReciboDetalles::create([
            'id_recibo' => $recibo->id,
            'id_cuenta' => $extracto->id_cuenta,
            'id_nit' => $recibo->id_nit,
            'fecha_manual' => $recibo->fecha_manual,
            'documento_referencia' => $extracto->documento_referencia,
            'consecutivo' => $recibo->consecutivo,
            'concepto' => 'PAGADO DESDE IMPORTADOR DE RECIBOS ' . number_format($monto),
            'total_factura' => 0,
            'total_abono' => $monto,
            'total_saldo' => $extracto->saldo,
            'nuevo_saldo' => $extracto->saldo - $monto,
            'total_anticipo' => 0,
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);
    }

    private function crearAnticipo($reciboImport, $recibo, Documento $documentoGeneral, $monto)
    {
        $cuentaAnticipo = PlanCuentas::find($this->id_cuenta_anticipos);
        if (!$cuentaAnticipo) return;

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
            'total_anticipo' => $monto,
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);

        $this->agregarMovimiento($documentoGeneral, $cuentaAnticipo, $reciboImport->id_nit,
            'ANTICIPO IMPORTADO DESDE RECIBOS',
            date('Ymd', strtotime($reciboImport->fecha_manual)), $monto, $cuentaAnticipo->naturaleza_ingresos);
    }

    private function agregarPagoFormaPago($reciboImport, $recibo, Documento $documentoGeneral)
    {
        ConReciboPagos::create([
            'id_recibo' => $recibo->id,
            'id_forma_pago' => $this->formaPago->id,
            'valor' => $reciboImport->pago,
            'saldo' => 0,
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);

        $this->agregarMovimiento($documentoGeneral, $this->formaPago->cuenta, $reciboImport->id_nit,
            'PAGO IMPORTADO DESDE RECIBOS',
            date('Ymd', strtotime($reciboImport->fecha_manual)), $reciboImport->pago, $this->formaPago->cuenta->naturaleza_ventas);
    }

    private function agregarMovimiento(Documento $documentoGeneral, $cuenta, $id_nit, $concepto, $documentoReferencia, $monto, $naturaleza)
    {
        $doc = new DocumentosGeneral([
            "id_cuenta" => $cuenta->id,
            "id_nit" => $cuenta->exige_nit ? $id_nit : null,
            "id_centro_costos" => $cuenta->exige_centro_costos ? ($this->cecos->id ?? null) : null,
            "concepto" => $cuenta->exige_concepto ? $concepto : null,
            "documento_referencia" => $cuenta->exige_documento_referencia ? $documentoReferencia : null,
            "debito" => $monto,
            "credito" => $monto,
            "created_by" => $this->user_id,
            "updated_by" => $this->user_id
        ]);
        $documentoGeneral->addRow($doc, $naturaleza);
    }

    /* -------------------- MÉTODOS AUXILIARES EXISTENTES (SIN CAMBIOS) -------------------- */

    private function totalAnticipos($id_nit)
    {
        $extractos = (new Extracto($id_nit, [4,8], null, $this->fechaManual))->anticiposDiscriminados()->get();
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
            $totalAnticipos += floatval($extracto->saldo);
        }
        return $totalAnticipos;
    }

    private function sumarDeudaTotal($extractos)
    {
        $totalDeuda = 0;
        foreach ($extractos as $extracto) {
            $totalDeuda += $extracto->saldo;
        }
        return $totalDeuda;
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
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') <= CF.dias_pronto_pago THEN 
                        CASE 
                            WHEN CF.valor_fijo_pronto_pago IS NOT NULL AND CF.valor_fijo_pronto_pago > 0 
                                THEN CF.valor_fijo_pronto_pago
                            ELSE ROUND(SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100), 0)
                        END
                    ELSE 0
                END AS descuento,

                CASE
                    WHEN DATEDIFF('{$fechaManual}', '{$inicioMes}') <= CF.dias_pronto_pago THEN 
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

    /* -------------------- EVENTOS Y MANEJO DE ERRORES -------------------- */

    private function sendSuccessEvent($message)
    {
        event(new PrivateMessageEvent('importador-recibos-' . $this->empresa->token_db_maximo . '_' . $this->user_id, [
            'success' => true,
            'accion' => 2,
            'tipo' => 'exito',
            'mensaje' => $message,
            'titulo' => 'Recibos importados',
            'autoclose' => false
        ]));
    }

    private function rollbackAndSendError($exception)
    {
        DB::connection('max')->rollBack();
        DB::connection('sam')->rollBack();
        Log::error('ProcessImportarRecibos', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
        event(new PrivateMessageEvent('importador-recibos-' . $this->empresa->token_db_maximo . '_' . $this->user_id, [
            'success' => false,
            'accion' => 0,
            'tipo' => 'error',
            'mensaje' => $exception->getMessage(),
            'titulo' => 'Fallo en la importación',
            'autoclose' => false
        ]));
    }

    public function failed($exception)
    {
        $this->rollbackAndSendError($exception);
    }
}