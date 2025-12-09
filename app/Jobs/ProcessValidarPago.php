<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\PlacetoPay\PaymentStatus;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS

use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;

class ProcessValidarPago implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;
    use BegConsecutiveTrait;

	protected $id;
    protected $empresa;
    protected $id_usuario;

    public function __construct($id, $empresa, $id_usuario)
    {
        $this->id = $id;
        $this->empresa = $empresa;
        $this->id_usuario = $id_usuario;
    }

    public function handle()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);

        info('validando pago: '.$this->id. '; Empresa: '.$this->empresa->token_db_maximo);

        $recibo = ConRecibos::where('id', $this->id)->first();

        if ($recibo && $recibo->estado == 1) return;

        $response = (new PaymentStatus(
            $recibo->request_id
        ))->send();

        if ($response->status < 300) {
            $status = (object)$response->response->status;
            $tipo_mesaje = 'info';

            switch ($status->status) {
                case 'APPROVED':
                    $tipo_mesaje = 'exito';
                    $this->aprobarRecibo($recibo, $status->message);
                    break;
                case 'PENDING':
                    $recibo->observacion = $status->message;
                    $recibo->save();
                    break;
                case 'REJECTED':
                    $tipo_mesaje = 'warning';
                    $recibo->estado = 0;
                    $recibo->observacion = $status->message;
                    $recibo->save();
                    break;
                case 'PARTIAL_EXPIRED':
                    $recibo->observacion = $status->message;
                    $recibo->save();
                    break;
                case 'APPROVED_PARTIAL':
                    $recibo->observacion = $status->message;
                    $recibo->save();
                    break;
                default:
                    break;
            }

            event(new PrivateMessageEvent('estado-cuenta-'.$this->empresa->token_db_maximo.'_'.$this->id_usuario, [
                'success'=>	true,
                'accion' => 2,
                'tipo' => $tipo_mesaje,
                'mensaje' => $status->message,
                'titulo' => 'Actualización de pago',
                'autoclose' => false
            ]));
        }
    }

    private function aprobarRecibo($recibo, $message)
    {
        
        $consecutivo = $this->getNextConsecutive($recibo->id_comprobante, $recibo->fecha_manual);
        $placetopay_forma_pago = Entorno::where('nombre', 'placetopay_forma_pago')->first();
        $placetopay_forma_pago = $placetopay_forma_pago ? $placetopay_forma_pago->valor : 2;
        
        $nit = $this->findNit($recibo->id_nit);
        $formaPago = $this->findFormaPago($placetopay_forma_pago);

        $recibo->consecutivo = $consecutivo;
        $recibo->estado = 1;
        $recibo->observacion = $message;
        $recibo->save();
        
        $extractos = (new Extracto(
            $recibo->id_nit,
            3,
            null,
            $recibo->fecha_manual
        ))->actual()->get();

        //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
        $documentoGeneral = new Documento(
            $recibo->id_comprobante,
            $recibo,
            $recibo->fecha_manual,
            $consecutivo,
            false
        );

        $valorPagado = $recibo->total_abono;
        $centro_costos = CentroCostos::first();

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
                'created_by' => $this->id_usuario,
                'updated_by' => $this->id_usuario
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
                "created_by" => $this->id_usuario,
                "updated_by" => $this->id_usuario
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
            'created_by' => $this->id_usuario,
            'updated_by' => $this->id_usuario
        ]);

        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);

        $this->updateConsecutivo($recibo->id_comprobante, $consecutivo);

        $documentoGeneral->save();
    }

    private function findNit ($id_nit)
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

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta.tipos_cuenta'
            )
            ->first();
    }
}
