<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\PortafolioERP\Extracto;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\ConRecibos;

class EstadoCuentaController extends Controller
{
    public function index(Request $request)
    {
        $nit = Nits::where('email', request()->user()->email)->first();
        
        $data = [
            'id_nit' => $nit ? $nit->id : '',
            'numero_documento' => $nit ? $nit->numero_documento : '',
            'id_comprobante' => Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor,
            'id_cuenta_ingreso' => Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first()->valor,
        ];

        return view('pages.administrativo.estado_cuenta.estado_cuenta-view', $data);
    }

    public function generate(Request $request)
    {
        try {
            $nit = Nits::where('email', request()->user()->email)->first();

            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit no existente'
                ], 422);
            }

            $response = (new Extracto(//TRAER CUENTAS POR COBRAR
                $nit->id,
                [3,7],
            ))->send(request()->user()->id_empresa);

            if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> $response['message']
                ], 422);
            }

            $response = $response['response']->data;

            if (!count($response)) {
                return response()->json([
                    'success'=>	true,
                    'data' => [
                        (object)[
                            'concepto' => 'SIN CUENTAS POR PAGAR',
                            'fecha_manual' => '',
                            'documento_referencia' => '',
                            'total_facturas' => '',
                            'total_abono' => '',
                            'saldo' => '0',
                        ]
                    ],
                    'message'=> 'Estado de cuenta generado con exito!'
                ]);
            }

            $extractos = [];
            $count_facturas = 0;
            $total_facturas = 0;
            $total_abono = 0;
            $saldo = 0;

            foreach ($response as $data) {
                $data = (object)$data;
                $count_facturas++;
                $total_facturas+= $data->total_facturas;
                $total_abono+= $data->total_abono;
                $saldo+= $data->saldo;
                array_push($extractos, $data);
            }

            array_push($extractos, (object)[
                'concepto' => 'TOTALES',
                'fecha_manual' => '',
                'documento_referencia' => $count_facturas,
                'total_facturas' => $total_facturas,
                'total_abono' => $total_abono,
                'saldo' => $saldo,
            ]);

            return response()->json([
                'success'=>	true,
                'data' => $extractos,
                'message'=> 'Estado de cuenta generado con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function totales(Request $request)
    {
        try {
            $data = [
                'total_cuentas_pagar' => 0,
                'total_cuentas_cobro' => 0,
                'total_pagos' => 0
            ];
    
            $nit = Nits::where('email', request()->user()->email)->first();
    
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit no existente'
                ], 422);
            }
    
            $response = (new Extracto(//TRAER CUENTAS POR COBRAR
                $nit->id,
                [3,7],
            ))->send(request()->user()->id_empresa);

            $extractos = $response['response']->data;

            if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> $response['message']
                ], 422);
            }

            foreach ($extractos as $extracto) {
                $extracto = (object)$extracto;
                $data['total_cuentas_pagar']+= $extracto->saldo;
            }

            $data['total_pagos'] = ConRecibos::where('id_nit', $nit->id)->count();
            $data['total_cuentas_cobro'] = Facturacion::where('id_nit', $nit->id)->count();
    
            return response()->json([
                'success'=>	true,
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function pagos(Request $request)
    {
        try {

            $nit = Nits::where('email', request()->user()->email)->first();
    
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit no existente'
                ], 422);
            }

            $recibos = ConRecibos::where('id_nit', $nit->id)
                ->when($request->get('estado') == 0 || $request->get('estado'), function ($query) use($request) {
                    if ($request->get('estado') != '') {
                        $query->where('estado', $request->get('estado'));
                    }
                })
                ->when($request->get('fecha_desde') && $request->get('fecha_hasta'), function ($query) use($request) {
                    $query->whereBetween('fecha_manual', [$request->get('fecha_desde'), $request->get('fecha_hasta')]);
                })
                ->with('pagos.forma_pago')
                ->get()
                ->toArray();

            $pagosData = [];
            $totalAbonado = 0;

            foreach ($recibos as $recibo) {
                $recibo = (object)$recibo;
                $totalAbonado+= $recibo->total_abono;
                $recibo->total = false;
                $pagosData[] = $recibo;
            }

            $pagosData[] = (object)[
                'id' => '',
                'fecha_manual' => 'TOTALES',
                'total_abono' => $totalAbonado,
                'observacion' => '',
                'estado' => 4,
                'pagos' => [],
                'total' => true
            ];

            return response()->json([
                'success'=>	true,
                'data' => $pagosData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function facturas(Request $request)
    {
        try {

            $nit = Nits::where('email', request()->user()->email)->first();
    
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit no existente'
                ], 422);
            }

            $facturas = Facturacion::where('id_nit', $nit->id)
                ->with('detalle')
                ->when($request->get('fecha_desde') && $request->get('fecha_hasta'), function ($query) use($request) {
                    $query->whereBetween('fecha_manual', [$request->get('fecha_desde'), $request->get('fecha_hasta')]);
                })
                ->get()
                ->toArray();

            $facturasData = [];
            
            foreach ($facturas as $factura) {
                $factura = (object)$factura;
                $facturasData[] = (object)[
                    'id' => $factura->id,
                    'documento_referencia' => 'TOTAL FACTURA '.$factura->fecha_manual,
                    'valor' => $factura->valor,
                    'fecha_manual' => $factura->fecha_manual,
                    'concepto' => '',
                    'total' => true
                ];
                foreach ($factura->detalle as $detalle) {
                    $detalle = (object)$detalle;
                    $facturasData[] = (object)[
                        'id' => $detalle->id,
                        'documento_referencia' => $detalle->documento_referencia,
                        'valor' => $detalle->valor,
                        'fecha_manual' => $detalle->fecha_manual,
                        'concepto' => $detalle->concepto,
                        'total' => false
                    ];
                }
            }

            return response()->json([
                'success'=>	true,
                'data' => $facturasData
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}