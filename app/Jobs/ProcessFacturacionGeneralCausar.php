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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
use App\Helpers\PortafolioERP\FacturacionERP;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Portafolio\DocumentosGeneral;

class ProcessFacturacionGeneralCausar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BegConsecutiveTrait;

    public $empresa = null;
    public $inicioMes = null;
	public $id_usuario = null;
    public $id_empresa = null;
    public $periodo_facturacion = null;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($id_usuario, $id_empresa)
    {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->empresa = Empresa::find($id_empresa);
        $this->periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        try {            
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $facturas = Facturacion::with('detalle')
                ->where('fecha_manual', $this->inicioMes.'-01')
                ->get();

            foreach ($facturas as $key => $factura) {

                //ARMAMOS LOS DATOS PARA LUEGO USARLOS
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
                
                //AGRUPAMOS LOS ITEMS QUE TENGAN EL MISMO TOKEN DE FACTURA
                $documentosGroup = [];
                foreach($documento as $document) {
                    $document = (object)$document;
                    $documentosGroup[$document->token_factura][] = $document;
                }

                $cuentasContables = [
                    'id_cuenta_por_cobrar',
                    'id_cuenta_ingreso'
                ];

                //ARMAMOS EL MOVIMIENTO CONTABLE
                foreach($documentosGroup as $docGroup) {

                    $consecutivo = $this->getNextConsecutive($docGroup[0]->id_comprobante, $docGroup[0]->fecha_manual);

                    $facDocumento = FacDocumentos::create([
                        'id_nit' => $docGroup[0]->id_nit,
                        'id_comprobante' => $docGroup[0]->id_comprobante,
                        'fecha_manual' => $docGroup[0]->fecha_manual,
                        'consecutivo' => $consecutivo,
                        'token_factura' => $docGroup[0]->token_factura,
                        'debito' => 0,
                        'credito' => 0,
                        'saldo_final' => 0,
                        'created_by' => $this->id_usuario,
                        'updated_by' => $this->id_usuario,
                    ]);
    
                    $documentoGeneral = new Documento(
                        $docGroup[0]->id_comprobante,
                        $facDocumento,
                        $docGroup[0]->fecha_manual,
                        $consecutivo
                    );
    
                    foreach ($docGroup as $doc) {
                        
                        foreach ($cuentasContables as $cuentaContableI) {
                            $naturaleza = null;
                            $docGeneral = $this->newDocGeneral();
                            $cuentaContable = PlanCuentas::where('id', $doc->{$cuentaContableI})
                                ->with('tipos_cuenta')
                                ->first();
    
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
                            $docGeneral['documento_referencia'] = $documentoReferencia;
                            $docGeneral['concepto'] = $doc->concepto;
                            $docGeneral['consecutivo'] = $consecutivo;
                            $docGeneral['created_by'] = $this->id_usuario;
                            $docGeneral['updated_by'] = $this->id_usuario;
            
                            $docGeneral = new DocumentosGeneral($docGeneral);
                            $documentoGeneral->addRow($docGeneral, $naturaleza);
                        }
                    }

                    if (!$documentoGeneral->save()) {
        
                        DB::connection('sam')->rollback();
                        return response()->json([
                            'success'=>	false,
                            'data' => [],
                            'message'=> $documentoGeneral->getErrors()
                        ], 401);
                    }
                    
                    $this->updateConsecutivo($docGroup[0]->id_comprobante, $consecutivo);
                }
            }

            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('facturacion-rapida-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'success' =>  true,
                'action' => 4
            ]));

		} catch (Exception $exception) {
			Log::error('ProcessFacturacionGeneralCausar al enviar facturación a PortafolioERP', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);
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
                return $doc->documento_referencia_anticipo;
            }
        }
		return $doc->documento_referencia;
	}

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

	public function failed($exception)
	{
		Log::error('ProcessFacturacionGeneralCausar al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
