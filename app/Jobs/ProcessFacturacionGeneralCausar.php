<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
// MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Portafolio\DocumentosGeneral;

class ProcessFacturacionGeneralCausar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;        // 10 minutos máximo de ejecución
    public $tries = 1;            // Solo un intento (evita reintentos infinitos y error de uuid duplicado)
    public $empresa = null;
    public $inicioMes = null;
    public $id_usuario = null;
    public $id_empresa = null;
    public $periodo_facturacion = null;

    /**
     * Create a new job instance.
     */
    public function __construct($id_usuario, $id_empresa)
    {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->empresa = Empresa::find($id_empresa);
        $entorno = Entorno::where('nombre', 'periodo_facturacion')->first();
        $this->periodo_facturacion = $entorno ? $entorno->valor : null;
        $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Iniciar transacción en la conexión 'sam' (PortafolioERP)
        DB::connection('sam')->beginTransaction();

        try {
            // Configurar conexiones
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            // Obtener facturas del período (usar chunk para no sobrecargar memoria)
            $facturaQuery = Facturacion::with('detalle')
                ->where('fecha_manual', $this->inicioMes . '-01');

            $lastConsecutivoGlobal = 0; // Solo para control de duplicados dentro del mismo job

            // Procesar en lotes de 50 facturas
            $facturaQuery->chunk(50, function ($facturas) use (&$lastConsecutivoGlobal) {
                foreach ($facturas as $factura) {
                    $this->procesarFactura($factura, $lastConsecutivoGlobal);
                }
            });

            // Confirmar transacción
            DB::connection('sam')->commit();

            // Notificar éxito (solo después del commit)
            event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
                'tipo' => 'exito',
                'success' => true,
                'action' => 4
            ]));

        } catch (Exception $exception) {
            DB::connection('sam')->rollBack();

            Log::error('ProcessFacturacionGeneralCausar falló', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);

            event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
                'tipo' => 'error',
                'success' => false,
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'action' => 5
            ]));

            // Relanzar excepción para que Laravel marque el job como fallido
            throw $exception;
        }
    }

    /**
     * Procesa una sola factura y sus detalles.
     *
     * @param \App\Models\Sistema\Facturacion $factura
     * @param int &$lastConsecutivoGlobal
     * @throws Exception
     */
    private function procesarFactura($factura, &$lastConsecutivoGlobal)
    {
        // Armar array de detalles como objetos
        $documentos = [];
        foreach ($factura->detalle as $detalle) {
            $documentos[] = (object) [
                'id_nit' => $detalle->id_nit,
                'id_cuenta_por_cobrar' => $detalle->id_cuenta_por_cobrar,
                'id_cuenta_ingreso' => $detalle->id_cuenta_ingreso,
                'id_comprobante' => $detalle->id_comprobante,
                'id_centro_costos' => $detalle->id_centro_costos,
                'fecha_manual' => $detalle->fecha_manual,
                'documento_referencia' => $detalle->documento_referencia,
                'documento_referencia_anticipo' => $detalle->documento_referencia_anticipo,
                'valor' => $detalle->valor,
                'concepto' => $detalle->concepto,
                'naturaleza_opuesta' => $detalle->naturaleza_opuesta,
                'token_factura' => $factura->token_factura,
            ];
        }

        // Agrupar por token_factura (normalmente uno solo, pero se respeta)
        $gruposPorToken = [];
        foreach ($documentos as $doc) {
            $gruposPorToken[$doc->token_factura][] = $doc;
        }

        $cuentasContables = ['id_cuenta_por_cobrar', 'id_cuenta_ingreso'];

        foreach ($gruposPorToken as $grupo) {
            $primerItem = $grupo[0];
            $comprobante = Comprobantes::find($primerItem->id_comprobante);
            if (!$comprobante) {
                throw new Exception("Comprobante no encontrado ID: {$primerItem->id_comprobante}");
            }

            // Calcular consecutivo
            $consecutivo = $comprobante->consecutivo_siguiente;
            if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
                $last = $this->getLastConsecutive($comprobante->id, $primerItem->fecha_manual);
                $consecutivo = $last + 1;
            }

            // Validar duplicado dentro del mismo lote
            if ($lastConsecutivoGlobal == $consecutivo) {
                throw new Exception("El consecutivo {$consecutivo} ya está en uso en este proceso.");
            }
            $lastConsecutivoGlobal = $consecutivo;

            // Crear registro FacDocumentos
            $facDocumento = FacDocumentos::create([
                'id_nit' => $primerItem->id_nit,
                'id_comprobante' => $primerItem->id_comprobante,
                'fecha_manual' => $primerItem->fecha_manual,
                'consecutivo' => $consecutivo,
                'token_factura' => $primerItem->token_factura,
                'debito' => 0,
                'credito' => 0,
                'saldo_final' => 0,
                'created_by' => $this->id_usuario,
                'updated_by' => $this->id_usuario,
            ]);

            // Instancia del helper Documento
            $documentoGeneral = new Documento(
                $primerItem->id_comprobante,
                $facDocumento,
                $primerItem->fecha_manual,
                $consecutivo
            );

            // Procesar cada línea contable del grupo
            foreach ($grupo as $item) {
                foreach ($cuentasContables as $cuentaCampo) {
                    $cuentaId = $item->{$cuentaCampo};
                    if (!$cuentaId) continue;

                    $cuentaContable = PlanCuentas::where('id', $cuentaId)
                        ->with('tipos_cuenta')
                        ->first();
                    if (!$cuentaContable) continue;

                    $tipoNumeroCuenta = mb_substr($cuentaContable->cuenta, 0, 1);
                    $docGeneralData = $this->newDocGeneral();
                    $docGeneralData['id_nit'] = $item->id_nit;
                    $docGeneralData['id_cuenta'] = $cuentaContable->id;
                    $docGeneralData['id_centro_costos'] = $item->id_centro_costos;
                    $docGeneralData['concepto'] = $item->concepto;
                    $docGeneralData['consecutivo'] = $consecutivo;
                    $docGeneralData['created_by'] = $this->id_usuario;
                    $docGeneralData['updated_by'] = $this->id_usuario;

                    $naturaleza = null;
                    $documentoReferencia = $item->documento_referencia;

                    // Lógica de naturaleza y débito/crédito
                    if ($tipoNumeroCuenta == '5') {
                        $naturaleza = PlanCuentas::DEBITO;
                        $docGeneralData['debito'] = $item->valor;
                    } elseif ($item->naturaleza_opuesta) {
                        $documentoReferencia = $this->generarDocumentoReferenciaAnticipos($cuentaContable, $item);
                        if ($cuentaContable->naturaleza_ventas == PlanCuentas::DEBITO) {
                            $naturaleza = PlanCuentas::CREDITO;
                            $docGeneralData['credito'] = $item->valor;
                        } else {
                            $naturaleza = PlanCuentas::DEBITO;
                            $docGeneralData['debito'] = $item->valor;
                        }
                    } else {
                        if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
                            $naturaleza = PlanCuentas::DEBITO;
                            $docGeneralData['debito'] = $item->valor;
                        } else {
                            $naturaleza = PlanCuentas::CREDITO;
                            $docGeneralData['credito'] = $item->valor;
                        }
                    }

                    $docGeneralData['documento_referencia'] = $documentoReferencia;

                    $docGeneral = new DocumentosGeneral($docGeneralData);
                    $documentoGeneral->addRow($docGeneral, $naturaleza);
                }
            }

            // Guardar el documento completo
            if (!$documentoGeneral->save()) {
                throw new Exception("Error guardando DocumentosGeneral: " . $documentoGeneral->getErrors());
            }

            // Actualizar consecutivo siguiente del comprobante
            $comprobante->consecutivo_siguiente++;
            $comprobante->save();
        }
    }

    /**
     * Obtiene el último consecutivo usado para un comprobante en un mes específico.
     *
     * @param int $idComprobante
     * @param string $fecha (formato Y-m-d)
     * @return int
     */
    private function getLastConsecutive($idComprobante, $fecha)
    {
        $last = DocumentosGeneral::where('id_comprobante', $idComprobante)
            ->whereYear('fecha_manual', '=', date('Y', strtotime($fecha)))
            ->whereMonth('fecha_manual', '=', date('m', strtotime($fecha)))
            ->orderBy('consecutivo', 'desc')
            ->value('consecutivo');

        return $last ? (int)$last : 0;
    }

    /**
     * Genera documento de referencia para anticipos.
     *
     * @param \App\Models\Portafolio\PlanCuentas $cuenta
     * @param \stdClass $doc
     * @return string
     */
    private function generarDocumentoReferenciaAnticipos($cuenta, $doc)
    {
        $tiposCuenta = $cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 4 || $tipoCuenta->id_tipo_cuenta == 8) {
                return $doc->documento_referencia_anticipo ?: $doc->documento_referencia;
            }
        }
        return $doc->documento_referencia;
    }

    /**
     * Retorna un array base para un nuevo registro de DocumentosGeneral.
     *
     * @return array
     */
    private function newDocGeneral()
    {
        return [
            'id_nit' => '',
            'id_cuenta' => '',
            'id_centro_costos' => '',
            'created_by' => '',
            'updated_by' => '',
            'consecutivo' => '',
            'concepto' => '',
            'credito' => 0,
            'debito' => 0,
            'saldo' => 0,
            'documento_referencia' => ''
        ];
    }

    /**
     * Maneja el fallo definitivo del job (cuando se agotan los reintentos).
     *
     * @param \Exception $exception
     */
    public function failed($exception)
    {
        Log::error('ProcessFacturacionGeneralCausar falló definitivamente', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);

        event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
            'tipo' => 'error',
            'success' => false,
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'action' => 5
        ]));
    }
}