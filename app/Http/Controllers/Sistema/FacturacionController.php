<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\PortafolioERP\Extracto;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PortafolioERP\FacturacionERP;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\FacturacionDetalle;

class FacturacionController extends Controller
{
    public function generar (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $nitsFacturacion = InmuebleNit::select('id_nit')->groupBy('id_nit')->get();

            //ELIMINAMOS LAS FACTURACIONES EN LA MISMA FECHA
            Facturacion::where('fecha_manual', $periodo_facturacion)->delete();
            FacturacionDetalle::where('fecha_manual', $periodo_facturacion)->delete();
            
            //RECORREMOS NITS CON INMUEBLES
            foreach ($nitsFacturacion as $nit) {

                $factura = Facturacion::create([//CABEZA DE FACTURA
                    'id_comprobante' => $id_comprobante_ventas,
                    'id_nit' => $nit->id_nit,
                    'fecha_manual' => $periodo_facturacion,
                    'token_factura' => $this->generateTokenDocumento(),
                    'valor' => 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);

                $valor = 0;
                $cobrarInteses = false;

                $inmueblesFacturar = InmuebleNit::with('inmueble.concepto', 'inmueble.zona')//INMUEBLES DEL NIT
                    ->where('id_nit', $nit->id_nit)
                    ->get();

                $totalAnticipos = $this->totalAnticipos($factura->id_nit);
                $totalInmuebles = 0;

                //RECORRERMOS INMUEBLES DEL NIT
                foreach ($inmueblesFacturar as $inmuebleFactura) {

                    if (count($inmueblesFacturar) > 1) $totalInmuebles++;
                    if ($inmuebleFactura->inmueble->concepto->intereses) $cobrarInteses = true;

                    $inicioMes = date('Y-m', strtotime($periodo_facturacion));
                    $valor+= $inmuebleFactura->valor_total;
                    
                    $this->generarFacturaInmueble($factura, $inmuebleFactura, $totalInmuebles);
                    if ($totalAnticipos > 0) {
                        $totalAnticipos = $this->generarFacturaAnticipos($factura, $inmuebleFactura, $totalInmuebles, $totalAnticipos);
                    }
                }

                if ($cobrarInteses) {//COBRAR INTERESES
                    $valor+= $this->generarFacturaInmuebleIntereses($factura, $inmueblesFacturar[0]);
                    $factura->valor = $valor;
                    $factura->save();
                }
            }

            Entorno::where('nombre', 'periodo_facturacion')
                ->update([
                    'valor' => date("Y-m-d", strtotime("+1 month", strtotime($periodo_facturacion)))
                ]);

            (new FacturacionERP(
                $periodo_facturacion
            ))->send();

            DB::connection('max')->commit();

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>'Facturación creada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function generarFacturaInmueble(Facturacion $factura, InmuebleNit $inmuebleFactura, $totalInmuebles)
    {
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_cuenta_por_cobrar' => $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $inmuebleFactura->inmueble->concepto->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_ventas,
            'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
            'fecha_manual' => $periodo_facturacion,
            'documento_referencia' => $inicioMes.$documentoReferenciaNumeroInmuebles,
            'valor' => $inmuebleFactura->valor_total,
            'concepto' => $inmuebleFactura->inmueble->concepto->nombre_concepto.' '.$inmuebleFactura->inmueble->nombre,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
    }

    private function generarFacturaAnticipos(Facturacion $factura, InmuebleNit $inmuebleFactura, $totalInmuebles, $totalAnticipos)
    {
        $totalAnticipar = 0;
        if ($totalAnticipos >= $totalInmuebles) {
            $totalAnticipar = $totalInmuebles;
            $totalAnticipos-= $totalInmuebles;
        } else {
            $totalAnticipar = $totalAnticipos;
            $totalAnticipos = 0;
        }

        $id_comprobante_notas = Entorno::where('nombre', 'id_comprobante_notas')->first()->valor;
        $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_cuenta_por_cobrar' => $id_cuenta_anticipos,
            'id_cuenta_ingreso' => $inmuebleFactura->inmueble->concepto->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_notas,
            'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
            'fecha_manual' => $periodo_facturacion,
            'documento_referencia' => $inicioMes.$documentoReferenciaNumeroInmuebles,
            'valor' => $totalAnticipar,
            'concepto' => 'CRUCE ANTICIPOS '.$inmuebleFactura->inmueble->concepto->nombre_concepto.' '.$inmuebleFactura->inmueble->nombre,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $totalAnticipos;
    }

    private function totalAnticipos($id_nit)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR PAGAR
            $id_nit,
            4,
        ))->send();

        if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $response['message']
            ], 422);
        }

        $extractos = $response['response']->data;

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function generarFacturaInmuebleIntereses(Facturacion $factura, InmuebleNit $inmuebleFactura)
    {
        $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
        
        if (!$id_cuenta_intereses) return;
        
        $response = (new Extracto(//TRAER CUENTAS POR COBRAR
            $factura->id_nit,
            3,
        ))->send();

        if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $response['message']
            ], 422);
        }

        $extractos = $response['response']->data;

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;

            if($extracto->id_cuenta == $id_cuenta_intereses) continue;

            $saldo = floatval($extracto->saldo);

            $porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;
            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso')->first()->valor;
            
            $inicioMes = date('Y-m', strtotime($periodo_facturacion));
            $valorTotal = $saldo * ($porcentaje_intereses_mora / 100);

            //DEFINIR CONCEPTO DE INTERESES
            $concepto = $extracto->concepto;
            $validateConcepto = explode('INTERESES ', $concepto );
            if (count($validateConcepto) > 1) $concepto = explode(' -', $validateConcepto[1])[0];

            $facturaDetalle = FacturacionDetalle::create([
                'id_factura' => $factura->id,
                'id_nit' => $factura->id_nit,
                'id_cuenta_por_cobrar' => $id_cuenta_intereses,
                'id_cuenta_ingreso' => $id_cuenta_ingreso,
                'id_comprobante' => $id_comprobante_ventas,
                'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
                'fecha_manual' => $periodo_facturacion,
                'documento_referencia' => $inicioMes,
                'valor' => $valorTotal,
                'concepto' => 'INTERESES '.$concepto.' - '.$extracto->fecha_manual.' - %'.$porcentaje_intereses_mora.' - BASE: '.$saldo,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            FacturacionDetalle::where('concepto', $extracto->concepto)
                ->where('id_nit', $extracto->id_nit)
                ->where('fecha_manual', $extracto->fecha_manual)
                ->update([
                    'saldo' => $saldo
                ]);
        }

        return $valorTotal;
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
}