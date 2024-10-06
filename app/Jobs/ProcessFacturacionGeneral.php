<?php

namespace App\Jobs;

use DB;
use Config;
use Exception;
use App\Helpers\helpers;
use App\Helpers\Extracto;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\PortafolioERP\FacturacionERP;
use App\Helpers\PortafolioERP\EliminarFactura;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\CuotasMultas;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Sistema\FacturacionDetalle;
use App\Models\Sistema\ConceptoFacturacion;

class ProcessFacturacionGeneral implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $id_usuario = null;
    public $empresa = null;
	public $id_empresa = null;
    public $id_cuenta_intereses = null;
    public $id_cuenta_anticipos = null;
    public $id_cuenta_ingreso_intereses = null;
    public $porcentaje_intereses_mora = null;
    public $id_comprobante_ventas = null;
    public $id_comprobante_notas = null;
    public $periodo_facturacion = null;
    public $id_cuenta_ingreso = null;
    public $id_centro_costos = null;
    public $documento_referencia_agrupado = 0;
    public $inicioMes = null;
    public $finMes = null;
    public $total_facturados = null;
    public $dataGeneral = null;
    public $redondeo = null;
    public $countIntereses = 0;
    public $saldoBase = 0;
    public $facturas = [];
    public $notificacionesGeneradas = 0;
    public $totalNotificaciones = 0;
    public $prontoPago = false;
    public $descuentoParcial = false;
    public $extractosAgrupados = [];

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
        $this->id_centro_costos = CentroCostos::first()->id;
        $this->id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso')->first()->valor;
        $this->periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $this->id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
        $this->id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
        $this->id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $this->id_comprobante_notas = Entorno::where('nombre', 'id_comprobante_notas')->first()->valor;
        $this->id_cuenta_ingreso_intereses = Entorno::where('nombre', 'id_cuenta_ingreso_intereses')->first()->valor;
        $this->porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;
        $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));
        $this->finMes = date('Y-m-t', strtotime($this->periodo_facturacion));
        $this->redondeo = Entorno::where('nombre', 'redondeo_intereses')->first();
        $this->redondeo = $this->redondeo ? $this->redondeo->valor : 0;
        $this->descuentoParcial = Entorno::where('nombre', 'descuento_pago_parcial')->first();
        $this->descuentoParcial = $this->descuentoParcial ? $this->descuentoParcial->valor : 0;
        $this->documento_referencia_agrupado = Entorno::where('nombre', 'documento_referencia_agrupado')->first();
        $this->documento_referencia_agrupado = $this->documento_referencia_agrupado ? $this->documento_referencia_agrupado->valor : 0;
        $this->total_facturados = 0;
        $this->dataGeneral = [
            'valor' => 0,
            'valor_anticipos' => 0,
            'count_intereses' => 0,
            'inmuebles' => [],
            'extras' => [
                'intereses' => (object)[
                    'items' => 0,
                    'id_concepto_facturacion' => 'intereses',
                    'valor_causado' => 0
                ]]
        ];
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);
        
        try {

            $query = $this->getInmueblesNitsQuery();
            $query->unionAll($this->getCuotasMultasNitsQuery(date('Y-m', strtotime($this->periodo_facturacion))));
            
            DB::connection('max')
                ->table(DB::raw("({$query->toSql()}) AS nits"))
                ->mergeBindings($query)
                ->select(
                    'id_nit'
                )
                ->groupByRaw('id_nit')
                ->orderByRaw('id_nit')
                ->chunk(233, function ($nits) {
                    $nits->each(function ($nit) {
                        
                        $this->countIntereses = 0;

                        $inmueblesFacturar = $this->inmueblesNitFacturar($nit->id_nit);
                        $cuotasMultasFacturarCxC = $this->extrasNitFacturarCxC($nit->id_nit, $this->periodo_facturacion);
                        $cuotasMultasFacturarCxP = $this->extrasNitFacturarCxP($nit->id_nit, $this->periodo_facturacion);
                        
                        $factura = Facturacion::create([//CABEZA DE FACTURA
                            'id_comprobante' => $this->id_comprobante_ventas,
                            'id_nit' => $nit->id_nit,
                            'fecha_manual' => $this->inicioMes.'-01',
                            'token_factura' => $this->generateTokenDocumento(),
                            'valor' => 0,
                            'created_by' => $this->id_usuario,
                            'updated_by' => $this->id_usuario,
                        ]);
                        
                        $valoresExtra = 0;
                        $valoresAdmon = 0;
                        $totalInmuebles = 0;
                        $valoresIntereses = 0;

                        //COBRAR INTERESES
                        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
                            $factura->id_nit,
                            [3,7],
                            null,
                            $this->periodo_facturacion
                        ))->actual()->get();

                        //AGRUPAMOS 
                        $this->extractosAgrupados = [];
                        foreach ($extractos as $extracto) {
                            $extracto = (object)$extracto;
                            if ($extracto->saldo > 0) {
                                if (!$this->cobrarIntereses($extracto->id_cuenta)) continue;
                                
                                $this->countIntereses++;
                                if (array_key_exists($extracto->id_cuenta, $this->extractosAgrupados)) {
                                    $this->extractosAgrupados[$extracto->id_cuenta]->total_abono+= $extracto->total_abono;
                                    $this->extractosAgrupados[$extracto->id_cuenta]->total_facturas+= $extracto->total_facturas;
                                    $this->extractosAgrupados[$extracto->id_cuenta]->saldo+= $extracto->saldo;
                                } else {
                                    $this->extractosAgrupados[$extracto->id_cuenta] = (object)[
                                        'id_nit' => $extracto->id_nit,
                                        'concepto' => $extracto->concepto,
                                        'total_abono' => $extracto->total_abono,
                                        'total_facturas' => $extracto->total_facturas,
                                        'documento_referencia' => $extracto->documento_referencia,
                                        'saldo' => $extracto->saldo,
                                    ];
                                }
                            }
                        }

                        $primerInmueble = count($inmueblesFacturar) ? $inmueblesFacturar[0] : false;
                        [$valores, $detalleFacturasInteres] = $this->generarFacturaInmuebleIntereses($factura, $primerInmueble);
                        
                        $valoresIntereses+= $valores;
                        
                        if ($valoresIntereses) {
                            $this->dataGeneral['extras']['intereses']->items+= 1;
                            $this->dataGeneral['extras']['intereses']->valor_causado+= $valoresIntereses;
                        };
                        
                        //TRAER ANTICIPOS
                        $anticiposNit = $this->totalAnticipos($factura->id_nit, $this->id_empresa);
                        $anticiposDisponibles = $anticiposNit;
                        
                        //RECORREMOS CUOTAS Y MULTAS CXP
                        foreach ($cuotasMultasFacturarCxP as $cuotaMultaFactura) {
                            if (array_key_exists($cuotaMultaFactura->id_concepto_facturacion, $this->dataGeneral['extras'])) {
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->items+= 1;
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->valor_causado+= $cuotaMultaFactura->valor_total;
                            } else {
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion] = (object)[
                                    'items' => 1,
                                    'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
                                    'valor_causado' => $cuotaMultaFactura->valor_total
                                ];
                            }
                            $valoresExtra+= $cuotaMultaFactura->valor_total;
                            $anticiposDisponibles+= $cuotaMultaFactura->valor_total;
                            
                            $documentoReferencia = $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                            $this->facturas[] = (object)[
                                'documento_referencia' => $documentoReferencia,
                                'saldo' => floatval($cuotaMultaFactura->valor_total)
                            ];
                        }

                        $this->prontoPago = $this->calcularTotalDeuda($inmueblesFacturar, $cuotasMultasFacturarCxC, $anticiposDisponibles, $valoresIntereses);
                        if ($anticiposDisponibles > 0 && $valoresIntereses) {
                            $anticiposDisponibles = $this->generarCruceIntereses($factura, $detalleFacturasInteres, $anticiposDisponibles);
                        }
                        
                        //RECORREMOS CUOTAS Y MULTAS CXC
                        foreach ($cuotasMultasFacturarCxC as $cuotaMultaFactura) {
                            if (array_key_exists($cuotaMultaFactura->id_concepto_facturacion, $this->dataGeneral['extras'])) {
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->items+= 1;
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion]->valor_causado+= $cuotaMultaFactura->valor_total;
                            } else {
                                $this->dataGeneral['extras'][$cuotaMultaFactura->id_concepto_facturacion] = (object)[
                                    'items' => 1,
                                    'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
                                    'valor_causado' => $cuotaMultaFactura->valor_total
                                ];
                            }

                            $valoresExtra+= $cuotaMultaFactura->valor_total;
                            $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                            $documentoReferencia = date('Y-m', strtotime($this->periodo_facturacion));
                            if ($anticiposDisponibles > 0) {
                                $anticiposDisponibles = $this->generarFacturaAnticipos($factura, $cuotaMultaFactura, 0, $anticiposDisponibles, $documentoReferencia);
                            }
                        }
                        
                        //RECORREMOS INMUEBLES DEL NIT
                        foreach ($inmueblesFacturar as $inmuebleFactura) {
                            
                            if (count($inmueblesFacturar) > 1) $totalInmuebles++;
                            if (array_key_exists($inmuebleFactura->id_concepto_facturacion, $this->dataGeneral['inmuebles'])) {
                                $this->dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion]->items+= 1;
                                $this->dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion]->valor_causado+= $inmuebleFactura->valor_total;
                                
                            } else {
                                $this->dataGeneral['inmuebles'][$inmuebleFactura->id_concepto_facturacion] = (object)[
                                    'items' => 1,
                                    'id_concepto_facturacion' => $inmuebleFactura->id_concepto_facturacion,
                                    'valor_causado' => $inmuebleFactura->valor_total
                                ];
                            }
                            
                            $valoresAdmon+= $inmuebleFactura->valor_total;
                            $documentoReferencia = $this->generarFacturaInmueble($factura, $inmuebleFactura, $totalInmuebles);

                            if ($anticiposDisponibles > 0) {
                                $anticiposDisponibles = $this->generarFacturaAnticipos($factura, $inmuebleFactura, $totalInmuebles, $anticiposDisponibles, $documentoReferencia);
                            }
                        }
                        
                        $factura->valor = ($valoresExtra + $valoresAdmon + $valoresIntereses);
                        $factura->valor_admon = $valoresAdmon;
                        $factura->valor_intereses = $valoresIntereses;
                        $factura->count_intereses = $this->countIntereses;
                        $factura->saldo_base = $this->saldoBase;
                        $factura->valor_anticipos = $anticiposNit - $anticiposDisponibles;
                        $factura->valor_cuotas_multas = $valoresExtra;
                        $factura->count_cuotas_multas = count($cuotasMultasFacturarCxC);
                        $factura->save();

                        $this->saldoBase = 0;
                    });
            });
            // DB::connection('max')->commit();
            // dd('hola afuera');
            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('facturacion-rapida-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'dataGeneral' => $this->dataGeneral,
                'success' =>  true,
                'action' => 3
            ]));

		} catch (Exception $exception) {
			Log::error('ProcessFacturacionGeneral al enviar facturación a PortafolioERP', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);
		}
    }

    private function generarFacturaInmuebleIntereses(Facturacion $factura, $inmuebleFactura)
    {   
        if (!$this->id_cuenta_intereses) return;
        
        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($this->extractosAgrupados)) return;

        $valorTotalIntereses = 0;
        $detalleIntereses = [];

        foreach ($this->extractosAgrupados as $extracto) {
            $saldo = floatval($extracto->saldo);
            $this->saldoBase+= $saldo;   
                 
            $valorTotal = $saldo * ($this->porcentaje_intereses_mora / 100);
            $valorTotal = $this->roundNumber($valorTotal);
            $valorTotalIntereses+= $valorTotal;
               
            //DEFINIR CONCEPTO DE INTERESES
            $concepto = $extracto->concepto;
            $validateConcepto = explode('INTERESES ', $concepto );
            if (count($validateConcepto) > 1) $concepto = explode(' -', $validateConcepto[1])[0];

            $data = [
                'id_factura' => $factura->id,
                'id_nit' => $factura->id_nit,
                'id_concepto_facturacion' => null,
                'id_cuenta_por_cobrar' => $this->id_cuenta_intereses,
                'id_cuenta_ingreso' => $this->id_cuenta_ingreso_intereses,
                'id_comprobante' => $this->id_comprobante_ventas,
                'id_centro_costos' => $inmuebleFactura ? $inmuebleFactura->id_centro_costos : CentroCostos::first()->id,
                'fecha_manual' => $this->inicioMes.'-01',
                'documento_referencia' => $extracto->documento_referencia,
                'valor' => round($valorTotal),
                'concepto' => 'INTERESES '.$concepto.' - '.$this->inicioMes.'-01'.' - %'.$this->porcentaje_intereses_mora.' - BASE: '.number_format($saldo),
                'naturaleza_opuesta' => false,
                'created_by' => $this->id_usuario,
                'updated_by' => $this->id_usuario,
            ];
            $facturaDetalle = FacturacionDetalle::create($data);
            array_push($detalleIntereses, $data);

            FacturacionDetalle::where('concepto', $extracto->concepto)
                ->where('id_nit', $extracto->id_nit)
                ->where('fecha_manual', $this->inicioMes.'-01')
                ->update([
                    'saldo' => $saldo
                ]);
        }

        return [$valorTotalIntereses, $detalleIntereses];
    }

    private function generarFacturaInmueble(Facturacion $factura, $inmuebleFactura, $totalInmuebles)
    {
        $documentoReferenciaNumeroInmuebles = $this->generarDocumentoReferencia($inmuebleFactura, $totalInmuebles);

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_concepto_facturacion' => $inmuebleFactura->id_concepto_facturacion,
            'id_cuenta_por_cobrar' => $inmuebleFactura->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_ingreso,
            'id_comprobante' => $this->id_comprobante_ventas,
            'id_centro_costos' => $inmuebleFactura->id_centro_costos,
            'fecha_manual' => $this->inicioMes.'-01',
            'documento_referencia' => $documentoReferenciaNumeroInmuebles,
            'valor' => round($inmuebleFactura->valor_total),
            'concepto' => $inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre_zona.' '.$inmuebleFactura->nombre.' Coef:'.$inmuebleFactura->coeficiente,
            'naturaleza_opuesta' => false,
            'created_by' => $this->id_usuario,
            'updated_by' => $this->id_usuario,
        ]);
        return $documentoReferenciaNumeroInmuebles;
    }

    private function generarFacturaAnticipos(Facturacion $factura, $inmuebleFactura, $totalInmuebles, $totalAnticipos, $documentoReferencia)
    {
        $totalAnticipar = 0;
        $totalDescuento = 0;

        if ($totalAnticipos >= $inmuebleFactura->valor_total) {
            $totalAnticipar = $inmuebleFactura->valor_total;
            $totalAnticipos-= $inmuebleFactura->valor_total;
        } else {
            $totalAnticipar = $totalAnticipos;
            $totalAnticipos = 0;
        }
        
        if ($this->prontoPago && $inmuebleFactura->pronto_pago && $inmuebleFactura->porcentaje_pronto_pago) {
            if ($totalAnticipar == $inmuebleFactura->valor_total) {
                $totalDescuento = $inmuebleFactura->valor_total * ($inmuebleFactura->porcentaje_pronto_pago / 100);
                $totalAnticipar = $totalAnticipar - round($totalDescuento);
                $totalAnticipos+= round($totalDescuento);
                $facturaDetalle = FacturacionDetalle::create([
                    'id_factura' => $factura->id,
                    'id_nit' => $inmuebleFactura->id_nit,
                    'id_concepto_facturacion' => null,
                    'id_cuenta_por_cobrar' => $inmuebleFactura->id_cuenta_gasto,
                    'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_cobrar,
                    'id_comprobante' => $this->id_comprobante_notas,
                    'id_centro_costos' => $inmuebleFactura->id_centro_costos,
                    'fecha_manual' => $this->inicioMes.'-01',
                    'documento_referencia' => $documentoReferencia,
                    'valor' => round($totalDescuento),
                    'concepto' => 'PRONTO PAGO '.$inmuebleFactura->porcentaje_pronto_pago.'% BASE '. number_format($inmuebleFactura->valor_total).' '.$inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre,
                    'naturaleza_opuesta' => true,
                    'created_by' => $this->id_usuario,
                    'updated_by' => $this->id_usuario,
                ]);

                $factura->pronto_pago = 2;
                $factura->save();
            }
        }

        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        foreach ($this->facturas as $key => $facturacxp) {
            if ($totalAnticipar <= 0) continue;
            $totalCruce = $totalAnticipar >= $facturacxp->saldo ? $facturacxp->saldo : $totalAnticipar;
            
            $facturaDetalle = FacturacionDetalle::create([
                'id_factura' => $factura->id,
                'id_nit' => $inmuebleFactura->id_nit,
                'id_concepto_facturacion' => null,
                'id_cuenta_por_cobrar' => $this->id_cuenta_anticipos,
                'id_cuenta_ingreso' => $inmuebleFactura->id_cuenta_cobrar,
                'id_comprobante' => $this->id_comprobante_notas,
                'id_centro_costos' => $inmuebleFactura->id_centro_costos,
                'fecha_manual' => $this->inicioMes.'-01',
                'documento_referencia' => $documentoReferencia,
                'documento_referencia_anticipo' => $facturacxp->documento_referencia,
                'valor' => round($totalCruce),
                'concepto' => 'CRUCE ANTICIPOS '.$inmuebleFactura->nombre_concepto.' '.$inmuebleFactura->nombre,
                'naturaleza_opuesta' => true,
                'created_by' => $this->id_usuario,
                'updated_by' => $this->id_usuario,
            ]);
            $totalAnticipar-= $totalCruce;
            $this->facturas[$key]->saldo-= $totalCruce;
        }

        foreach ($this->facturas as $key => $facturacxp) {
            if ($facturacxp->saldo <= 0) unset($this->facturas[$key]);
        }

        return $totalAnticipos;
    }

    private function generarCruceIntereses (Facturacion $factura, $detalleFacturas, $totalAnticipos)
    {
        foreach ($detalleFacturas as $detalleFactura) {
            if ($totalAnticipos <= 0) continue;
            $detalleFactura = (object)$detalleFactura;
            $totalAnticipar = 0;
            if ($totalAnticipos >= $detalleFactura->valor) {
                $totalAnticipar = $detalleFactura->valor;
                $totalAnticipos-= $detalleFactura->valor;
            } else {
                $totalAnticipar = $totalAnticipos;
                $totalAnticipos = 0;
            }
            
            foreach ($this->facturas as $key => $facturacxp) {
                if ($totalAnticipar <= 0) continue;

                $totalCruce = $totalAnticipar >= $facturacxp->saldo ? $facturacxp->saldo : $totalAnticipar;
                
                $facturaDetalle = FacturacionDetalle::create([
                    'id_factura' => $factura->id,
                    'id_nit' => $detalleFactura->id_nit,
                    'id_concepto_facturacion' => null,
                    'id_cuenta_por_cobrar' => $this->id_cuenta_anticipos,
                    'id_cuenta_ingreso' => $detalleFactura->id_cuenta_por_cobrar,
                    'id_comprobante' => $this->id_comprobante_notas,
                    'id_centro_costos' => $detalleFactura->id_centro_costos,
                    'fecha_manual' => $this->inicioMes.'-01',
                    'documento_referencia' => $detalleFactura->documento_referencia,
                    'documento_referencia_anticipo' => $facturacxp->documento_referencia,
                    'valor' => round($totalCruce),
                    'concepto' => 'CRUCE ANTICIPOS '.$detalleFactura->concepto,
                    'naturaleza_opuesta' => true,
                    'created_by' => $this->id_usuario,
                    'updated_by' => $this->id_usuario,
                ]);
                $totalAnticipar-= $totalCruce;
                $this->facturas[$key]->saldo-= $totalCruce;
            }
    
            foreach ($this->facturas as $key => $facturacxp) {
                if ($facturacxp->saldo <= 0) unset($this->facturas[$key]);
            }
        }
        return $totalAnticipos;
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

    private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function generarFacturaCuotaMulta(Facturacion $factura, $cuotaMultaFactura)
    {
        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $cuotaMultaFactura->id_nit,
            'id_concepto_facturacion' => $cuotaMultaFactura->id_concepto_facturacion,
            'id_cuenta_por_cobrar' => $cuotaMultaFactura->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $cuotaMultaFactura->id_cuenta_ingreso,
            'id_comprobante' => $this->id_comprobante_ventas,
            'id_centro_costos' => $cuotaMultaFactura->id_centro_costos,
            'fecha_manual' => $this->inicioMes.'-01',
            'documento_referencia' => $this->inicioMes,
            'valor' => round($cuotaMultaFactura->valor_total),
            'concepto' => $cuotaMultaFactura->nombre_concepto.' '.$cuotaMultaFactura->observacion,
            'naturaleza_opuesta' => false,
            'created_by' => $this->id_usuario,
            'updated_by' => $this->id_usuario,
        ]);
        return $this->inicioMes;
    }

    private function totalAnticipos($id_nit, $id_empresa)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            $id_nit,
            [4,8]
        ))->actual()->get();
        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $this->facturas = [];
        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $this->facturas[] = (object)[
                'documento_referencia' => $extracto->documento_referencia,
                'saldo' => floatval($extracto->saldo)
            ];
            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function notificarTotalFacturado()
    {
        $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
        $this->notificacionesGeneradas++;
        event(new PrivateMessageEvent('facturacion-rapida-'.$urlEventoNotificacion, [
            'tipo' => 'exito',
            'dataGeneral' => $this->dataGeneral,
            'porcentaje' => ($this->notificacionesGeneradas / $this->totalNotificaciones) * 100,
            'autoclose' => false
        ]));
        $this->total_facturados = 0;
        $this->dataGeneral = [
            'valor' => 0,
            'valor_anticipos' => 0,
            'count_intereses' => 0,
            'inmuebles' => [],
            'extras' => []
        ];
    }

    private function cobrarIntereses ($id_cuenta)
    {
        $existecuenta = ConceptoFacturacion::where('id_cuenta_cobrar', $id_cuenta)
            ->where('intereses', 1);

        return $existecuenta->count() ? true : false;
    }

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }

    private function inmueblesNitFacturar($id_nit)
    {
        return DB::connection('max')->table('inmueble_nits')->select(
                'inmueble_nits.id_nit',
                'inmueble_nits.id_inmueble',
                'inmueble_nits.valor_total',
                'INM.nombre',
                'INM.id_concepto_facturacion',
                'INM.coeficiente',
                'CFA.nombre_concepto',
                'CFA.id_cuenta_cobrar',
                'CFA.id_cuenta_ingreso',
                'CFA.id_cuenta_interes',
                'CFA.intereses',
                'CFA.pronto_pago',
                'CFA.id_cuenta_gasto',
                'CFA.id_cuenta_anticipo',
                'CFA.porcentaje_pronto_pago',
                'ZO.id_centro_costos',
                'ZO.nombre AS nombre_zona',
                'ZO.nombre AS nombre_zona',
                DB::raw("CONCAT(INM.nombre, '-', ZO.nombre) as documento_referencia_group")
            )
            ->leftJoin('inmuebles AS INM', 'inmueble_nits.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS ZO', 'INM.id_zona', 'ZO.id')
            ->leftJoin('concepto_facturacions AS CFA', 'INM.id_concepto_facturacion', 'CFA.id')
            ->where('inmueble_nits.id_nit', $id_nit)
            ->get()->toArray();
    }

    private function extrasNitFacturarCxC($id_nit, $periodo_facturacion)
    {
        $fecha_facturar = date('Y-m', strtotime($periodo_facturacion));
        $dbERP = Config::get('database.connections.sam.database');
        $data = CuotasMultas::with('nit', 'concepto.cuenta_ingreso.tipos_cuenta', 'inmueble.zona')
            ->where('id_nit', $id_nit)
            ->where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar)
            ->get()->toArray();

        $dataArray = [];

        
        foreach ($data as $extraCxC) {
            
            $tipoCuenta = $extraCxC['concepto']['cuenta_ingreso'];
            if (array_key_exists('tipos_cuenta', $tipoCuenta) && $tipoCuenta['tipos_cuenta'] && array_key_exists('id_tipo_cuenta', $tipoCuenta['tipos_cuenta'])) {
                $tipoCuenta = $extraCxC['concepto']['cuenta_ingreso']['tipos_cuenta']['id_tipo_cuenta'];
            } else {
                $tipoCuenta = 3;
            }
            
            if ($tipoCuenta != 4 && $tipoCuenta != 8) {
                array_push($dataArray, (object)[
                    'id_nit' => $extraCxC['id_nit'],
                    'id_inmueble' => $extraCxC['id_inmueble'],
                    'valor_total' => $extraCxC['valor_total'],
                    'observacion' => $extraCxC['observacion'],
                    'id_concepto_facturacion' => $extraCxC['concepto']['id'],
                    'nombre' => $extraCxC['inmueble']['nombre'],
                    'nombre_concepto' => $extraCxC['concepto']['nombre_concepto'],
                    'id_cuenta_cobrar' => $extraCxC['concepto']['id_cuenta_cobrar'],
                    'id_cuenta_ingreso' => $extraCxC['concepto']['id_cuenta_ingreso'],
                    'id_cuenta_interes' => $extraCxC['concepto']['id_cuenta_interes'],
                    'id_cuenta_gasto' => $extraCxC['concepto']['id_cuenta_gasto'],
                    'id_cuenta_anticipo' => $extraCxC['concepto']['id_cuenta_anticipo'],
                    'porcentaje_pronto_pago' => $extraCxC['concepto']['porcentaje_pronto_pago'],
                    'pronto_pago' => $extraCxC['concepto']['pronto_pago'],
                    'intereses' => $extraCxC['concepto']['intereses'],
                    'id_centro_costos' => $extraCxC['inmueble']['zona']['id_centro_costos'],
                ]);
            }
        }
        return $dataArray;
    }

    private function extrasNitFacturarCxP($id_nit, $periodo_facturacion)
    {
        $fecha_facturar = date('Y-m', strtotime($periodo_facturacion));
        $dbERP = Config::get('database.connections.sam.database');
        $data = CuotasMultas::with('nit', 'concepto.cuenta_ingreso.tipos_cuenta', 'inmueble.zona')
            ->where('id_nit', $id_nit)
            ->where("fecha_inicio", '<=', $fecha_facturar)
            ->where("fecha_fin", '>=', $fecha_facturar)
            ->get()->toArray();

        $dataArray = [];

        foreach ($data as $extraCxP) {
            $tipoCuenta = $extraCxP['concepto']['cuenta_ingreso'];
            if (array_key_exists('tipos_cuenta', $tipoCuenta) && $tipoCuenta['tipos_cuenta'] && array_key_exists('id_tipo_cuenta', $tipoCuenta['tipos_cuenta'])) {
                $tipoCuenta = $extraCxP['concepto']['cuenta_ingreso']['tipos_cuenta']['id_tipo_cuenta'];
                if ($tipoCuenta == 4 || $tipoCuenta == 8) {
                    array_push($dataArray, (object)[
                        'id_nit' => $extraCxP['id_nit'],
                        'id_inmueble' => $extraCxP['id_inmueble'],
                        'valor_total' => $extraCxP['valor_total'],
                        'observacion' => $extraCxP['observacion'],
                        'id_concepto_facturacion' => $extraCxP['concepto']['id'],
                        'nombre' => $extraCxP['inmueble']['nombre'],
                        'nombre_concepto' => $extraCxP['concepto']['nombre_concepto'],
                        'id_cuenta_cobrar' => $extraCxP['concepto']['id_cuenta_cobrar'],
                        'id_cuenta_ingreso' => $extraCxP['concepto']['id_cuenta_ingreso'],
                        'id_cuenta_interes' => $extraCxP['concepto']['id_cuenta_interes'],
                        'id_cuenta_gasto' => $extraCxP['concepto']['id_cuenta_gasto'],
                        'id_cuenta_anticipo' => $extraCxP['concepto']['id_cuenta_anticipo'],
                        'porcentaje_pronto_pago' => $extraCxP['concepto']['porcentaje_pronto_pago'],
                        'pronto_pago' => $extraCxP['concepto']['pronto_pago'],
                        'intereses' => $extraCxP['concepto']['intereses'],
                        'id_centro_costos' => $extraCxP['inmueble']['zona']['id_centro_costos'],
                    ]);
                }
            }
        }

        return $dataArray;
    }

    private function calcularTotalDeuda($inmueblesFacturar, $cuotasMultasFacturarCxC, $anticiposDisponibles, $valoresIntereses)
    {
        if ($valoresIntereses) return false;

        $deudaTotal = 0;

        foreach ($inmueblesFacturar as $inmueble) {
            $descuento = $inmueble->pronto_pago && $inmueble->porcentaje_pronto_pago ?
                $inmueble->valor_total * ($inmueble->porcentaje_pronto_pago / 100) :
                0;

            $deudaTotal+= ($inmueble->valor_total - $descuento);
        }
        
        foreach ($cuotasMultasFacturarCxC as $multas) {
            $descuento = $multas->pronto_pago && $multas->porcentaje_pronto_pago ?
                $multas->valor_total * ($multas->porcentaje_pronto_pago / 100) :
                0;

            $deudaTotal+= ($multas->valor_total - $descuento);
        }

        if (!$this->descuentoParcial && $anticiposDisponibles >= $deudaTotal) return true;
        if ($this->descuentoParcial) return true;
        return false;
    }

    private function generarDocumentoReferencia($inmuebleFactura, $totalInmuebles)
    {
        if ($this->documento_referencia_agrupado) {
            return $inmuebleFactura->documento_referencia_group;
        }
        $countItems = $totalInmuebles ? '_'.$totalInmuebles : '';
        return $this->inicioMes.$countItems;
    }

	public function failed($exception)
	{
		Log::error('ProcessFacturacionGeneral al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
