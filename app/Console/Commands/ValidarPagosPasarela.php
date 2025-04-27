<?php

namespace App\Console\Commands;

use DB;
use Exception;
use App\Helpers\helpers;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Console\Command;
use App\Events\PrivateMessageEvent;
use App\Helpers\PlacetoPay\PaymentStatus;
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

class ValidarPagosPasarela extends Command
{
    use BegConsecutiveTrait;

    protected $signature = 'app:validar-pagos-pasarela';
    protected $description = 'Validar pagos de place to pay';

    protected $id_usuario;
    protected $timeout = 10;
    protected $chunkSize = 34;
    protected $empresa = null;
    protected $totalValidaciones = 0;

    public function handle()
    {
        $startTime = microtime(true);

        try {
            
            $empresas = DB::connection('clientes')
                ->table('empresas')
                ->select('id', 'razon_social', 'token_db_maximo', 'token_db_portafolio')
                ->cursor(); // Usar cursor para mejor manejo de memoria

            foreach ($empresas as $empresa) {

                $this->empresa = $empresa;
                // Configurar conexiones
                $this->setUpDatabaseConnections($empresa);

                $recibos = ConRecibos::where('estado', 2)
                    ->whereNotNull('request_id')
                    ->get();

                if (!count($recibos)) {
                    continue;
                }

                info("Validando pagos de {$empresa->razon_social}");

                foreach ($recibos as $recibo) {
                    $this->validarPlaceToPay($recibo);
                }

                // Liberar memoria
                gc_collect_cycles();
            }

            if ($this->totalValidaciones) {
                $executionTime = round((microtime(true) - $startTime) / 60, 2);
                info("\nProceso completado. Total pagos validados: {$this->totalValidaciones}. Tiempo total: {$executionTime} minutos");
            }
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function setUpDatabaseConnections($empresa): void
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $empresa->token_db_portafolio);
            
        } catch (\Exception $e) {
            $this->error("Error configurando conexiones para {$empresa->razon_social}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function validarPlaceToPay($recibo): void
    {
        try {
            $this->totalValidaciones++;

            info("Validando pago: {$recibo->id}");
            
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
    
                event(new PrivateMessageEvent('estado-cuenta-'.$this->empresa->token_db_maximo.'_'.$recibo->created_by, [
                    'success'=>	true,
                    'accion' => 2,
                    'tipo' => $tipo_mesaje,
                    'mensaje' => $status->message,
                    'titulo' => 'Actualización de pago',
                    'autoclose' => false
                ]));
            }


        } catch (\Exception $e) {
            $this->error("Error validando recibo {$recibo->id}: " . $e->getMessage());
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
            $consecutivo
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
