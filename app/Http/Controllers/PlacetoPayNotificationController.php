<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Log;
use App\Helpers\PlacetoPay\PaymentStatus;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;

class PlacetoPayNotificationController extends Controller
{
    use BegConsecutiveTrait;

    protected $cecos;
    protected $user_id;
    protected $fechaManual;
    protected $id_cuenta_anticipos;
    protected $id_cuenta_intereses;

    public function handle(Request $request)
    {
        try {
            $data = $request->all();

            Log::info('Notificación PlacetoPay recibida', ['payload' => $data]);
            // Validar estructura mínima
            if (!isset($data['status'], $data['status']['status'], $data['requestId'], $data['reference'])) {
                Log::warning('Webhook con estructura inválida', ['payload' => $data]);
                return response()->json([
                    'success' => false,
                    'error' => 'Estructura inválida',
                    'required_fields' => ['status.status', 'requestId', 'reference']
                ], 400);
            }

            $date = $data['status']['date'] ?? now()->toDateTimeString();
            $requestId = $data['requestId'];
            $reference = $data['reference'];
            $status = $data['status']['status'];
            $message = $data['status']['message'] ?? null;
            $signature = $data['signature'] ?? null;

            $parts = explode('-', $reference);
            if (count($parts) < 2) {
                Log::warning('Referencia inválida', ['reference' => $reference]);
                return response()->json([
                    'success' => false,
                    'error' => 'Referencia inválida',
                    'message' => 'La referencia no es valida para procesar el pago',
                    'received_reference' => $reference
                ], 400);
            }

            list($recibo_id, $empresa_id) = $parts;

            if (empty($empresa_id)) {
                return response()->json(['success' => false, 'error' => 'Empresa no especificada'], 400);
            }

            $empresa = Empresa::find($empresa_id);
            if (!$empresa) {
                Log::error('Empresa no encontrada', ['empresa_id' => $empresa_id]);
                return response()->json(['success' => false, 'error' => 'Empresa no encontrada'], 404);
            }

            // if (!$signature) {
            //     Log::warning('Webhook sin firma');
            //     return response()->json(['success' => false, 'error' => 'Firma requerida'], 401);
            // }

            // Validación de firma (ajusta el campo secret_key según tu DB)
            // $secretKey = $empresa->placetopay_secret_key ?? '';
            // $receivedSignature = str_replace('sha256:', '', $signature);
            // $generatedSignature = hash('sha256', $requestId . $status . $date . $secretKey);

            // if (!hash_equals($generatedSignature, $receivedSignature)) {
            //     Log::warning('Firma inválida', ['received' => $receivedSignature, 'generated' => $generatedSignature]);
            //     return response()->json(['success' => false, 'error' => 'Firma inválida'], 401);
            // }

            // Cambiar conexiones de BD (ajusta a tu lógica)
            copyDBConnection('max', 'max');
            setDBInConnection('max', $empresa->token_db_maximo);
            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $empresa->token_db_portafolio);

            $recibo = ConRecibos::where('id', $recibo_id)->first();
            if (!$recibo) {
                Log::error('Recibo no encontrado', ['recibo_id' => $recibo_id]);
                return response()->json(['success' => false, 'error' => 'Recibo no encontrado'], 404);
            }

            if ($recibo->estado != 2) {
                Log::error('Recibo aprobado previamente', ['recibo_id' => $recibo_id]);
                return response()->json(['success' => false, 'error' => 'Recibo ya fue aprobado'], 404);
            }

            $this->user_id = $recibo->created_by;
            $this->cecos = CentroCostos::first();
            $this->fechaManual = $recibo->fecha_manual;

            $estado = $this->mapStatus($status);
            if ($estado == 1) {

                $response = (new PaymentStatus($recibo->request_id))->send();
                if ($response->status < 300) {
                    $statusNew = (object) $response->response->status;
                    switch ($statusNew->status) {
                        case 'APPROVED':
                            // $recibo->update(['estado' => 1, 'observacion' => $statusNew->message]);
                            $this->registrarMovimientoContable($recibo);
                            break;
                        case 'PENDING':
                            $recibo->observacion = $statusNew->message;
                            $recibo->save();
                            break;
                        case 'REJECTED':
                            $recibo->estado = 0;
                            $recibo->observacion = $statusNew->message;
                            $recibo->save();
                            break;
                        default:
                            $recibo->observacion = $statusNew->message;
                            $recibo->save();
                            break;
                    }
                }
            }

            event(new PrivateMessageEvent(
                'estado-cuenta-' . $empresa->token_db_maximo . '_' . $recibo->created_by,
                ['success' => true, 'accion' => 2, 'tipo' => 'info', 'mensaje' => 'Pago actualizado', 'titulo' => 'Actualización de pago', 'autoclose' => false]
            ));

            Log::info('Notificación procesada correctamente', ['recibo_id' => $recibo->id, 'nuevo_estado' => $estado]);
            return response()->json(['success' => true, 'message' => 'OK'], 200);

        } catch (\Throwable $e) {
            Log::error('Error procesando webhook PlacetoPay', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'payload' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Internal Server Error',
                'message' => $e->getMessage() // opcional, en desarrollo; quítalo en producción
            ], 500);
        }
    }

    protected function mapStatus($placetopayStatus)
    {
        switch ($placetopayStatus) {
            case 'APPROVED':
                return 1; // Pagado
            case 'REJECTED':
            case 'FAILED':
                return 2; // Rechazado
            case 'PENDING':
                return 3; // Pendiente
            default:
                return 2; // Por defecto rechazado
        }
    }

    protected function registrarMovimientoContable($recibo)
    {
        try {
            $consecutivo = $this->getNextConsecutive($recibo->id_comprobante, $recibo->fecha_manual);
            $placetopay_forma_pago = Entorno::where('nombre', 'placetopay_forma_pago')->first();
            $placetopay_forma_pago = $placetopay_forma_pago ? $placetopay_forma_pago->valor : 2;

            $redondeoPronto = Entorno::where('nombre', 'redondeo_pronto_pago')->first();
            $redondeoPronto = $redondeoPronto ? (float) $redondeoPronto->valor : 0;

            $this->id_cuenta_ingreso = (int) optional(Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first())->valor;
            $this->id_cuenta_anticipos = (int) optional(Entorno::where('nombre', 'id_cuenta_anticipos')->first())->valor;
            $this->id_cuenta_intereses = (int) optional(Entorno::where('nombre', 'id_cuenta_intereses')->first())->valor;

            $this->formaPago = FacFormasPago::where('id', $placetopay_forma_pago)
                ->with('cuenta.tipos_cuenta')
                ->first();

            $documentoGeneral = new Documento(
                $recibo->id_comprobante,
                $recibo,
                $recibo->fecha_manual,
                $consecutivo,
                false
            );

            $inicioMes = Carbon::parse($recibo->fecha_manual)->format('Y-m-01');
            $inicioMesMenosDia = Carbon::parse($inicioMes)->subDay()->format('Y-m-d');
            $finMes = Carbon::parse($recibo->fecha_manual)->format('Y-m-t');

            $facturaDescuento = $this->getFacturaMes($recibo->id_nit, $inicioMes, $recibo->fecha_manual, $redondeoPronto);

            $totalDescuentoDisponible = $facturaDescuento ? $facturaDescuento->descuento : 0;
            $anticiposDisponibles = $this->totalAnticipos($recibo->id_nit);
            $valorDisponible = (float) $recibo->total_abono;
            $valorRecibido = (float) $recibo->total_abono;
            $deudaTotal = $facturaDescuento ? $facturaDescuento->saldo_pendiente : 0;

            $extractos = (new Extracto($recibo->id_nit, [3,7], null, $finMes))->actual()->get();
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
                        $conceptoAux = $recibo->observacion ? $recibo->observacion : 'PAGO DESDE PASARELA';
                        if ($cuentaGasto) {
                            $this->agregarMovimiento($documentoGeneral, $cuentaGasto, $recibo->id_nit,
                                'PRONTO PAGO ' . $conceptoDescuento->porcentaje_pronto_pago . '% BASE ' . number_format($facturaDescuento->subtotal) . ' ' . $conceptoAux,
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
                    $this->agregarMovimiento($documentoGeneral, $cuentaPago, $recibo->id_nit,
                        'CRUCE PRONTO PAGO ' . ($extracto->concepto ?? ''),
                        $extracto->documento_referencia, $valorDescuento, $cuentaPago->naturaleza_ingresos);
                }
                $extracto->saldo -= $valorDescuento;
            }

            $valorDisponible += $totalDescuentoDisponible;  // sumamos lo que sobró del descuento (si no se usó todo)

            // Ahora aplicar pagos y anticipos
            $valorRestante = $this->aplicarPagosYAnticiposOriginal($recibo, $recibo, $documentoGeneral, $extractosMapeados, $valorDisponible, $anticiposDisponibles);

            if ($valorRestante > 0) {
                $this->crearAnticipo($recibo, $documentoGeneral, $valorRestante);
            }

            ConReciboPagos::create([
                'id_recibo' => $recibo->id,
                'id_forma_pago' => $this->formaPago->id,
                'valor' => $recibo->total_abono,
                'saldo' => 0,
                'created_by' => $this->user_id,
                'updated_by' => $this->user_id
            ]);

            $concepto = 'PAGO DESDE PASARELA';
            $this->agregarMovimiento($documentoGeneral, $this->formaPago->cuenta, $recibo->id_nit,
                $concepto,
                date('Ymd', strtotime($recibo->fecha_manual)), $recibo->total_abono, $this->formaPago->cuenta->naturaleza_ventas);

            $this->updateConsecutivo($recibo->id_comprobante, $consecutivo);
    
            $documentoGeneral->save();
            
            Log::info('Movimiento contable registrado para recibo', [
                'recibo_id' => $recibo->id
            ]);
            
        } catch (\Exception $e) {

            Log::error('Error al registrar movimiento contable', [
                'recibo_id' => $recibo->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function findNit ($id_nit)
    {
        return Nits::whereId($id_nit)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->first();
    }

    protected function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta.tipos_cuenta'
            )
            ->first();
    }

    private function getFacturaMes($id_nit, $inicioMes, $fechaManual, $redondeoPronto)
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
        
        if ($redondeoPronto) {
            $descuentoRedondeado = $this->roundNumber($data->descuento, $redondeoPronto);
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

    private function crearAnticipo($recibo, Documento $documentoGeneral, $monto)
    {
        $cuentaAnticipo = PlanCuentas::find($this->id_cuenta_anticipos);
        if (!$cuentaAnticipo) return;

        $concepto = $recibo->observacion ? 'ANTICIPO - ' . $recibo->observacion : 'ANTICIPO CARGADO DESDE PASARELA';

        ConReciboDetalles::create([
            'id_recibo' => $recibo->id,
            'id_cuenta' => $cuentaAnticipo->id,
            'id_nit' => $recibo->id_nit,
            'fecha_manual' => $recibo->fecha_manual,
            'documento_referencia' => $recibo->consecutivo,
            'consecutivo' => $recibo->consecutivo,
            'concepto' => $concepto,
            'total_factura' => 0,
            'total_abono' => 0,
            'total_saldo' => 0,
            'nuevo_saldo' => 0,
            'total_anticipo' => $monto,
            'created_by' => $this->user_id,
            'updated_by' => $this->user_id
        ]);

        
        $this->agregarMovimiento($documentoGeneral, $cuentaAnticipo, $recibo->id_nit,
            $concepto,
            date('Ymd', strtotime($recibo->fecha_manual)), $monto, $cuentaAnticipo->naturaleza_ingresos);
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
            $concepto = $reciboImport->concepto ? $reciboImport->concepto . ' - ' . number_format($valorPago) : 'PAGADO DESDE IMPORTADOR DE RECIBOS ' . number_format($valorPago);

            if ($valorPago) {
                $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $extracto->id_cuenta,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $extracto->documento_referencia,
                    'consecutivo' => $recibo->consecutivo,
                    'concepto' => $concepto,
                    'total_factura' => 0,
                    'total_abono' => $valorPago,
                    'total_saldo' => $extracto->saldo,
                    'nuevo_saldo' => $extracto->saldo - ($valorPago + $totalAnticipar),
                    'total_anticipo' => 0,
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id
                ]);
                
                $this->agregarMovimiento($documentoGeneral, $cuentaPago, $recibo->id_nit,
                    $concepto,
                    $documentoReferencia, $valorPago, $cuentaPago->naturaleza_ingresos);
            }

            $valorRestante -= ($valorPago - $totalAnticipar);
        }

        return $valorRestante;
    }


}
