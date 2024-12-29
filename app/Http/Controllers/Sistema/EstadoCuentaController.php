<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Jobs\ProcessValidarPago;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PlacetoPay\PaymentRequest;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Portafolio\ConReciboDetalles;

use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Facturacion;
use App\Models\Empresa\UsuarioEmpresa;

class EstadoCuentaController extends Controller
{

    public function __construct(Request $request)
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}
    
    public function index(Request $request)
    {
        $nit = Nits::where('email', request()->user()->email)->first();

        $entorno = Entorno::whereIn('nombre', ['placetopay_login', 'placetopay_trankey', 'placetopay_url', 'placetopay_forma_pago'])->get();

		$placetopayUrl = '';
		$placetopayLogin = '';
		$placetopayTrankey = '';
		$placetopayFormaPago = '';

		if (count($entorno)) {
			$placetopayUrl = $entorno->firstWhere('nombre', 'placetopay_url');
			$placetopayUrl = $placetopayUrl ? $placetopayUrl->valor : '';

            $placetopayLogin = $entorno->firstWhere('nombre', 'placetopay_login');
			$placetopayLogin = $placetopayLogin ? $placetopayLogin->valor : '';

			$placetopayTrankey = $entorno->firstWhere('nombre', 'placetopay_trankey');
			$placetopayTrankey = $placetopayTrankey && $placetopayTrankey->valor ? $placetopayTrankey->valor : '';

            $placetopayFormaPago = $entorno->firstWhere('nombre', 'placetopay_forma_pago');
			$placetopayFormaPago = $placetopayFormaPago && $placetopayFormaPago->valor ? $placetopayFormaPago->valor : '';
		}

        $pasarelaPagos = false;

        if ($placetopayUrl && $placetopayLogin && $placetopayTrankey && $placetopayFormaPago) {
            $pasarelaPagos = true;
        }
        
        $data = [
            'id_nit' => $nit ? $nit->id : '',
            'numero_documento' => $nit ? $nit->numero_documento : '',
            'pasarela_pagos' => $pasarelaPagos,
            'id_comprobante' => Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor,
            'id_cuenta_ingreso' => Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first()->valor,
            'usuario_empresa' => UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first()
        ];

        return view('pages.administrativo.estado_cuenta.estado_cuenta-view', $data);
    }

    public function generate(Request $request)
    {
        try {

            $nit = null;

            $usuario_empresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if ($usuario_empresa && $usuario_empresa->id_nit) {
                $nit = Nits::where('id', $usuario_empresa->id_nit)->first();
            }

            if (!$nit) {
                $nit = Nits::where('email', request()->user()->email)->first();
            }

            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit no existente'
                ], 422);
            }

            $response = (new Extracto(//TRAER CUENTAS POR COBRAR
                $nit->id,
                [3,7]
            ))->actual()->get();

            $responseCXP = (new Extracto(//TRAER CUENTAS POR PAGAR
                $nit->id,
                [4,8]
            ))->anticipos()->get();

            $cuentasXPData = null;
            if (count($responseCXP)) {
                $totalValor = 0;
                foreach ($responseCXP as $data) {
                    $data = (object)$data;
                    $totalValor+= ($data->debito - $data->credito);
                }
                $cuentasXPData = (object)[
                    'concepto' => 'SALDO A FAVOR',
                    'fecha_manual' => '',
                    'documento_referencia' => '',
                    'tipo_cuenta' => 'cxp',
                    'total_facturas' => '',
                    'total_abono' => '',
                    'saldo' => $totalValor * -1,
                ];
            }

            if (!count($response)) {
                $dataNone = [(object)[
                    'concepto' => 'SIN CUENTAS POR PAGAR',
                    'fecha_manual' => '',
                    'documento_referencia' => '',
                    'tipo_cuenta' => '',
                    'total_facturas' => '',
                    'total_abono' => '',
                    'saldo' => '0',
                ]];

                if ($cuentasXPData) array_push($dataNone, $cuentasXPData);

                return response()->json([
                    'success'=>	true,
                    'data' => $dataNone,
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
                $data->tipo_cuenta = 'cxc';
                $saldo+= $data->saldo;
                array_push($extractos, $data);
            }

            if ($cuentasXPData) array_push($extractos, $cuentasXPData);

            array_push($extractos, (object)[
                'concepto' => 'TOTALES',
                'fecha_manual' => '',
                'documento_referencia' => $count_facturas,
                'total_facturas' => $total_facturas,
                'tipo_cuenta' => '',
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
                'total_cuentas_cobrar' => 0,
                'total_cuentas_cobro' => 0,
                'total_pagos' => 0
            ];
    
            $nit = null;

            $usuario_empresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if ($usuario_empresa && $usuario_empresa->id_nit) {
                $nit = Nits::where('id', $usuario_empresa->id_nit)->first();
            }

            if (!$nit) {
                $nit = Nits::where('email', request()->user()->email)->first();
            }
            
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'Nit asociado no se encuentra en nuestra base de datos'
                ], 422);
            }
    
            $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
                $nit->id,
                [3,7]
            ))->actual()->get();

            $cuentasXP = (new Extracto(//TRAER CUENTAS POR PAGAR
                $nit->id,
                [4,8]
            ))->actual()->get();

            foreach ($extractos as $extracto) {
                $extracto = (object)$extracto;
                $data['total_cuentas_pagar']+= $extracto->saldo;
            }

            foreach ($cuentasXP as $cxp) {
                $cxp = (object)$cxp;
                $data['total_cuentas_cobrar']+= ($cxp->debito - $cxp->credito);
            }

            $data['total_pagos'] = ConRecibos::where('id_nit', $nit->id)->count();
            $data['total_cuentas_cobro'] = Facturacion::where('id_nit', $nit->id)->count();
            $data['total_cuentas_cobrar'] = $data['total_cuentas_cobrar'] * -1;
    
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
                ->orderBy('id', 'DESC')
                ->get()
                ->toArray();

            $facturasData = [];
            
            foreach ($facturas as $factura) {
                $factura = (object)$factura;
                $facturasData[] = (object)[
                    'id' => $factura->id,
                    'id_nit' => $factura->id_nit,
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
                        'id_nit' => $factura->id_nit,
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

    public function pasarela(Request $request)
    {
        $comprobanteRecibo = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_pago', null) : Carbon::now();

        if(!$comprobanteRecibo) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante recibo' => ['El Comprobante del recibo es incorrecto!']]
            ], 422);
        }

        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'numero_documento' => 'required|exists:sam.nits,numero_documento',
            'fecha_pago' => 'nullable',
            'valor_comprobante' => 'nullable',
            'valor_pago' => 'nullable',
            'comprobante' => 'nullable',
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }
        
        try {
            DB::connection('sam')->beginTransaction();
            
            $placetopay_forma_pago = Entorno::where('nombre', 'placetopay_forma_pago')->first();
            $placetopay_forma_pago = $placetopay_forma_pago ? $placetopay_forma_pago->valor : 2;
            $formaPago = $this->findFormaPago($placetopay_forma_pago);
            
            //AGREGAR MOVIMIENTO PAGO
            if (!$formaPago) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'La forma de pago con el id: '.$placetopay_forma_pago.' No existe!'
                ], 422);
            }
            
            //CREAR FACTURA RECIBO
            $nit = $this->findNit($request->get('id_nit'));

            $recibo = ConRecibos::create([
                'id_nit' => $request->get('id_nit'),
                'id_comprobante' => $request->get('id_comprobante'),
                'fecha_manual' => $request->get('fecha_pago'),
                'consecutivo' => 0,
                'total_abono' => $request->get('valor_pago'),
                'total_anticipo' => 0,
                'observacion' => 'PASARELA DE PAGOS',
                'estado' => 2,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            //GUARDAR FORMA DE PAGO
            $pagoRecibo = ConReciboPagos::create([
                'id_recibo' => $recibo->id,
                'id_forma_pago' => $formaPago->id,
                'valor' => $request->get('valor_pago'),
                'saldo' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $response = (new PaymentRequest(
                $recibo->id
            ))->send(request()->user()->id_empresa);

            if ($response->status < 300) {
                
                $recibo->request_id = $response->response->requestId;
                $recibo->save();

                $empresa = Empresa::where('id', $request->user()['id_empresa'])->first();
                DB::connection('sam')->commit();

                ProcessValidarPago::dispatch($recibo->id, $empresa, $request->user()->id)->delay(now()->addMinute(3));

                return response()->json([
                    "success"=>true,
                    'data' => [],
                    'link' => $response->response->processUrl,
                    "message"=>'Link portal de pago'
                ], 200);
            }
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=> false,
                'data' => [],
                "message"=> $response->response->status['message']
            ], 422);
            
        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }


    private function findFormaPagoCuenta ($idCuenta)
    {
        return FacFormasPago::where('id_cuenta', $idCuenta)
            ->with(
                'cuenta.tipos_cuenta'
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
}