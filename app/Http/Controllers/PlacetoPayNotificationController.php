<?php

namespace App\Http\Controllers;

use DB;
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
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;

class PlacetoPayNotificationController extends Controller
{
    use BegConsecutiveTrait;

    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('Notificación PlacetoPay recibida', $data);

        // Validación básica de campos necesarios
        if (!isset($data['status'], $data['status']['status'], $data['status']['date'], $data['requestId'], $data['signature'])) {
            return response('Bad Request', 400);
        }

        $requestId = $data['requestId'];
        $reference = $data['reference'];
        $status = $data['status']['status'];
        $message = $data['status']['message'];
        $signature = $data['signature'] ?? null;
        
        list($recibo_id, $empresa_id) = explode("-", $reference);

        if (!$empresa_id) {
            Log::error('Empresa no encontrada', ['empresa_id' => $empresa_id]);
            return response('Not Found', 404);
        }

        // Buscar Empresa
        $empresa = Empresa::find($empresa_id);

        copyDBConnection('max', 'max');
        setDBInConnection('max', $empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db_portafolio);

        // Buscar el recibo
        $recibo = ConRecibos::where('id', $recibo_id)->first();

        if (!$recibo) {
            Log::error('Recibo no encontrado', ['recibo_id' => $recibo_id]);
            return response('Not Found', 404);
        }

        // Mapear estados y actualizar recibo
        $estado = $this->mapStatus($status);

        // Registrar movimiento contable si está aprobado
        if ($estado == 1) { // 1 = Aprobado/Pagado
            $response = (new PaymentStatus(
                $recibo->request_id
            ))->send();

            if ($response->status < 300) {

                $statusNew = (object)$response->response->status;
                switch ($statusNew->status) {
                    case 'APPROVED':
                        $tipo_mesaje = 'exito';
                        $recibo->update([
                            'estado' => 1,
                            'observacion' => $statusNew->message
                        ]);
                        $this->registrarMovimientoContable($recibo);
                        break;
                    case 'PENDING':
                        $recibo->observacion = $statusNew->message;
                        $recibo->save();
                        break;
                    case 'REJECTED':
                        $tipo_mesaje = 'warning';
                        $recibo->estado = 0;
                        $recibo->observacion = $statusNew->message;
                        $recibo->save();
                        break;
                    case 'PARTIAL_EXPIRED':
                        $recibo->observacion = $statusNew->message;
                        $recibo->save();
                        break;
                    case 'APPROVED_PARTIAL':
                        $recibo->observacion = $statusNew->message;
                        $recibo->save();
                        break;
                    default:
                        break;
                }
            }
        }

        event(new PrivateMessageEvent('estado-cuenta-'.$empresa->token_db_maximo.'_'.$recibo->created_by, [
            'success'=>	true,
            'accion' => 2,
            'tipo' => 'info',
            'mensaje' => 'Pago actualizado',
            'titulo' => 'Actualización de pago',
            'autoclose' => false
        ]));

        Log::info('Notificación procesada correctamente', [
            'recibo_id' => $recibo->id,
            'nuevo_estado' => $estado
        ]);

        return response('OK', 200);
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

            $nit = $this->findNit($recibo->id_nit);
            $formaPago = $this->findFormaPago($placetopay_forma_pago);

            $recibo->consecutivo = $consecutivo;
            $recibo->estado = 1;
            $recibo->save();

            $extractos = (new Extracto(
                $recibo->id_nit,
                3,
                null
            ))->actual()->get();

            $valorPagado = $recibo->total_abono;
            $centro_costos = CentroCostos::first();

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $recibo->id_comprobante,
                $recibo,
                $recibo->fecha_manual,
                $recibo->consecutivo,
                false
            );

            foreach ($extractos as $extracto) {
                if (!$valorPagado) continue;
    
                $cuentaRecord = PlanCuentas::find($extracto->id_cuenta);
                $totalAbonado = 0;
                if ($extracto->saldo >= $valorPagado) {
                    $totalAbonado = $valorPagado;
                    $valorPagado = 0;
                } else {
                    $totalAbonado = $extracto->saldo;
                    $valorPagado-= $extracto->saldo;
                }
                //CREAR RECIBO DETALLE
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $extracto->documento_referencia,
                    'consecutivo' => $consecutivo,
                    'concepto' => 'PAGO PASARELA',
                    'total_factura' => 0,
                    'total_abono' => $totalAbonado,
                    'total_saldo' => $extracto->saldo,
                    'nuevo_saldo' => $extracto->saldo - $totalAbonado,
                    'total_anticipo' => 0,
                    'created_by' => $recibo->created_by,
                    'updated_by' => $recibo->created_by
                ]);
                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $centro_costos->id : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $extracto->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $extracto->documento_referencia : null,
                    "debito" => $totalAbonado,
                    "credito" => $totalAbonado,
                    "created_by" => $recibo->created_by,
                    "updated_by" => $recibo->created_by
                ]);
                
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ingresos);
            }
    
            //AGREGAR MOVIMIENTO CONTABLE PAGO
            $doc = new DocumentosGeneral([
                'id_cuenta' => $formaPago->cuenta->id,
                'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                'id_centro_costos' => null,
                'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$recibo->consecutivo : null,
                'documento_referencia' => null,
                'debito' => $recibo->total_abono,
                'credito' => $recibo->total_abono,
                'created_by' => $recibo->created_by,
                'updated_by' => $recibo->created_by
            ]);
    
            $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
    
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
}
