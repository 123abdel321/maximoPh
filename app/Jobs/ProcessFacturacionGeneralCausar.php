<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
use App\Helpers\PortafolioERP\FacturacionERP;
use Carbon\Carbon; // Usar Carbon para manejo de fechas

//MODELS
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

    public $empresa = null; // Se cargará en handle
    public $inicioMes = null; // Se cargará en handle
    public $id_usuario = null;
    public $id_empresa = null;
    public $periodo_facturacion = null; // Se cargará en handle

    /**
     * Create a new job instance.
     * * @return void
     */
    public function __construct($id_usuario, $id_empresa)
    {
        // 1. Mantenemos el constructor simple: Solo asignación de IDs serializables.
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        // La carga de modelos y DB se hace en handle().
    }

    /**
     * Execute the job.
     * * @return string
     */
    public function handle()
    {
        try { 
            // 2. CARGA DE DEPENDENCIAS AL INICIO (MÁS SEGURO EN EL WORKER)
            $this->empresa = Empresa::find($this->id_empresa);

            if (!$this->empresa) {
                throw new Exception("Empresa con ID {$this->id_empresa} no encontrada.");
            }

            $entorno = Entorno::where('nombre', 'periodo_facturacion')->first();
            if (!$entorno) {
                throw new Exception("Variable de entorno 'periodo_facturacion' no configurada.");
            }
            $this->periodo_facturacion = $entorno->valor;
            $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));

            // 3. CONFIGURACIÓN DE CONEXIONES
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            DB::connection('sam')->beginTransaction();

            $facturas = Facturacion::with('detalle')
                ->where('fecha_manual', $this->inicioMes.'-01')
                ->get();
            
            // 4. LÓGICA PRINCIPAL (SE ELIMINÓ $lastConsecutivo, LO HACE EL HELPER)

            foreach ($facturas as $key => $factura) {

                // ARMAMOS LOS DATOS PARA LUEGO USARLOS
                $documento = [];
                foreach ($factura->detalle as $detalle) {
                    $documento[] = (object)[
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
                
                // AGRUPAMOS LOS ITEMS QUE TENGAN EL MISMO TOKEN DE FACTURA
                $documentosGroup = [];
                foreach($documento as $document) {
                    $document = (object)$document;
                    $documentosGroup[$document->token_factura][] = $document;
                }

                $cuentasContables = [
                    'id_cuenta_por_cobrar',
                    'id_cuenta_ingreso'
                ];
                
                // ARMAMOS EL MOVIMIENTO CONTABLE
                foreach($documentosGroup as $docGroup) {
                    
                    $comprobante = Comprobantes::find($docGroup[0]->id_comprobante);

                    if (!$comprobante) {
                        throw new Exception("El comprobante con ID {$docGroup[0]->id_comprobante} no existe.");
                    }
                    
                    // 5. OBTENER CONSECUTIVO (SE ELIMINÓ LA LÓGICA MANUAL DE CÁLCULO)
                    // Ya no se calcula ni se valida aquí. El Helper Documento::save() lo hará.
                    
                    $facDocumento = FacDocumentos::create([
                        'id_nit' => $docGroup[0]->id_nit,
                        'id_comprobante' => $docGroup[0]->id_comprobante,
                        'fecha_manual' => $docGroup[0]->fecha_manual,
                        'consecutivo' => 0, // Se inicializa en 0 y se actualiza después de guardar
                        'token_factura' => $docGroup[0]->token_factura,
                        'debito' => 0,
                        'credito' => 0,
                        'saldo_final' => 0,
                        'created_by' => $this->id_usuario,
                        'updated_by' => $this->id_usuario,
                    ]);

                    // INICIALIZAMOS EL HELPER SIN PASAR CONSECUTIVO (Para que lo genere)
                    $documentoGeneral = new Documento(
                        $facDocumento->id_comprobante,
                        $facDocumento,
                        $facDocumento->fecha_manual
                        // NO SE PASA EL CONSECUTIVO AQUÍ
                    );
                    
                    foreach ($docGroup as $doc) {
                        
                        foreach ($cuentasContables as $cuentaContableI) {
                            $naturaleza = null;
                            $docGeneral = $this->newDocGeneral();
                            $cuentaContable = PlanCuentas::where('id', $doc->{$cuentaContableI})
                                ->with('tipos_cuenta')
                                ->first();

                            if (!$cuentaContable) {
                                continue;
                            }
                            
                            $tipoNumeroCuenta = mb_substr($cuentaContable->cuenta, 0, 1);
                            
                            $naturaleza = null;
                            $documentoReferencia = $doc->documento_referencia;
                            
                            if ($tipoNumeroCuenta == '5') {
                                $naturaleza = PlanCuentas::DEBITO;
                                $docGeneral['debito'] = $doc->valor;
                            } else if ($doc->naturaleza_opuesta) {

                                $documentoReferencia = $this->generarDocumentoReferenciaAnticipos($cuentaContable, $doc);
                                
                                if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
                                    $naturaleza = PlanCuentas::CREDITO;
                                    $docGeneral['credito'] = $doc->valor;
                                } else {
                                    $naturaleza = PlanCuentas::DEBITO;
                                    $docGeneral['debito'] = $doc->valor;
                                }
                            } else {
                                if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
                                    $naturaleza = PlanCuentas::DEBITO;
                                    $docGeneral['debito'] = $doc->valor;
                                } else {
                                    $naturaleza = PlanCuentas::CREDITO;
                                    $docGeneral['credito'] = $doc->valor;
                                }
                            }
                            
                            $docGeneral['id_nit'] = $doc->id_nit;
                            $docGeneral['id_cuenta'] = $cuentaContable->id;
                            $docGeneral['id_centro_costos'] = $doc->id_centro_costos;
                            $docGeneral['documento_referencia'] = $cuentaContable->exige_documento_referencia ? $documentoReferencia : null;
                            $docGeneral['concepto'] = $doc->concepto;
                            // El consecutivo se llenará en Documento::save()
                            $docGeneral['consecutivo'] = 0; 
                            $docGeneral['created_by'] = $this->id_usuario;
                            $docGeneral['updated_by'] = $this->id_usuario;
                            
                            $docGeneral = new DocumentosGeneral($docGeneral);
                            $documentoGeneral->addRow($docGeneral, $naturaleza);
                        }
                        
                    }
                    
                    if (!$documentoGeneral->save()) {
                        DB::connection('sam')->rollback();

                        // 6. ¡CRÍTICO! LANZAR EXCEPCIÓN EN LUGAR DE response()->json
                        $errors = json_encode($documentoGeneral->getErrors());
                        throw new Exception("Fallo al guardar el movimiento contable. Errores: {$errors}");
                    }
                    
                    // 7. ACTUALIZAR FacDocumentos con el consecutivo asignado por el Helper
                    $finalConsecutivo = $documentoGeneral->getHead()['consecutivo'];
                    $facDocumento->consecutivo = $finalConsecutivo;
                    $facDocumento->save();
                    
                    // ELIMINADO: La actualización de $comprobante->consecutivo_siguiente ya la hace Documento::save()
                }
            }

            DB::connection('sam')->commit();

            event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
                'tipo' => 'exito',
                'success' => true,
                'action' => 4,
                'message' => 'La causación de la facturación general ha finalizado con éxito.'
            ]));

        } catch (Exception $exception) {

            // El rollback se hace aquí y en el método failed() por si acaso.
            DB::connection('sam')->rollback();
            
            // 8. Aseguramos que el método failed() se llama para la notificación
            $this->failed($exception);
            
            // Relanzar la excepción para que el Job la marque como fallida/reintente
            throw $exception; 
        }
    }

    private function getInmueblesNitsQuery()
    {
        return DB::connection('max')->table('inmueble_nits AS IN')
            ->select(
                'IN.id_nit'
            );
    }

    private function getCuotasMultasNitsQuery($fecha_facturar)
    {
        return DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                'CM.id_nit'
            )
            ->where("CM.fecha_inicio", '<=', $fecha_facturar)
            ->where("CM.fecha_fin", '>=', $fecha_facturar);
    }

    private function generarDocumentoReferenciaAnticipos($cuenta = null, $doc)
    {
        $tiposCuenta = $cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 4 || $tipoCuenta->id_tipo_cuenta == 8) {
                if ($doc->documento_referencia_anticipo) {
                    return $doc->documento_referencia_anticipo;
                }
                return $doc->documento_referencia;
            }
        }
        return $doc->documento_referencia;
    }

    private function newDocGeneral()
    {
        return [
            'id_nit' => '',
            'id_cuenta' => '',
            'id_comprobante' => '', // Añadido para consistencia
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
    
    // 9. MÉTODO getLastConsecutive ELIMINADO - Ahora Documento::save() lo calcula

    public function failed($exception)
    {
        // Se mantiene la lógica de rollback y notificación en caso de fallo.
        DB::connection('sam')->rollback();

        Log::error('ProcessFacturacionGeneralCausar al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);

        event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
            'tipo' => 'error',
            'success' => false,
            'message' => "ERROR JOB: " . $exception->getMessage(),
            'line' => $exception->getLine(),
            'action' => 5
        ]));
        // No se relanza la excepción aquí, ya que el handle() se encarga de eso.
    }
}